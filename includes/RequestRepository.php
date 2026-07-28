<?php
declare(strict_types=1);

/**
 * CERTICHAIN - Request Repository
 *
 * Central place for every query that touches `requests`.
 * Any employee-facing query in this file is scoped by college_id so an
 * employee physically cannot retrieve requests outside their assigned college.
 */

class RequestRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Look up the college_id that a given program routes to.
     * Used at submission time so the caller/UI can show "Your request will
     * be handled by: <College Name>" before the row is even inserted
     * (the DB trigger also enforces this independently on INSERT).
     */
    public function getCollegeForProgram(int $programId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.college_id, c.college_code, c.college_name
             FROM programs p
             JOIN colleges c ON c.college_id = p.college_id
             WHERE p.program_id = :pid AND p.is_active = 1'
        );
        $stmt->execute([':pid' => $programId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Create a new academic document request.
     * college_id is intentionally NOT passed in from the caller -- it is
     * derived server-side (and re-verified by the DB trigger) from the
     * requester's program_id, so a student/alumni can never spoof routing.
     */
    public function createRequest(
        int $requesterUserId,
        string $requesterRole,
        int $programId,
        int $documentTypeId,
        ?string $remarks = null
    ): int {
        $requestCode = $this->generateRequestCode();

        $stmt = $this->pdo->prepare(
            'INSERT INTO requests
                (request_code, requester_user_id, requester_role, program_id, college_id,
                 document_type_id, status, remarks)
             VALUES
                (:code, :uid, :role, :pid, 0, :doc, :status, :remarks)'
            // college_id is set to 0 here as a placeholder; the BEFORE INSERT
            // trigger `trg_requests_set_college` overwrites it from program_id.
        );
        $stmt->execute([
            ':code'    => $requestCode,
            ':uid'     => $requesterUserId,
            ':role'    => $requesterRole,
            ':pid'     => $programId,
            ':doc'     => $documentTypeId,
            ':status'  => 'pending_review',
            ':remarks' => $remarks,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * THE core scoping query: all requests visible to an employee.
     * college_id filter is mandatory and non-optional -- there is no code
     * path in this class that lets an employee pass in a different college.
     */
    public function getRequestsForEmployeeCollege(int $collegeId, ?string $status = null): array
    {
        $sql = 'SELECT r.request_id, r.request_code, r.status, r.created_at,
                       u.first_name, u.last_name, u.email,
                       p.degree_code, dt.document_name,
                       col.college_code
                FROM requests r
                JOIN users u        ON u.user_id = r.requester_user_id
                JOIN programs p     ON p.program_id = r.program_id
                JOIN document_types dt ON dt.document_type_id = r.document_type_id
                JOIN colleges col   ON col.college_id = r.college_id
                WHERE r.college_id = :college_id';

        $params = [':college_id' => $collegeId];

        if ($status !== null) {
            $sql .= ' AND r.status = :status';
            $params[':status'] = $status;
        }

        $sql .= ' ORDER BY r.created_at ASC';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch a single request -- but only if it belongs to the given college.
     * Use this instead of a bare "WHERE request_id = ?" anywhere an employee
     * is opening a specific request, so a guessed/URL-edited request_id
     * belonging to another college returns nothing.
     */
    public function getRequestForEmployeeCollege(int $requestId, int $collegeId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT r.*, u.first_name, u.last_name, u.email, p.degree_code, dt.document_name
             FROM requests r
             JOIN users u ON u.user_id = r.requester_user_id
             JOIN programs p ON p.program_id = r.program_id
             JOIN document_types dt ON dt.document_type_id = r.document_type_id
             WHERE r.request_id = :rid AND r.college_id = :college_id
             LIMIT 1'
        );
        $stmt->execute([':rid' => $requestId, ':college_id' => $collegeId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Assign an employee to a request. Enforces that the employee's own
     * assigned college matches the request's college_id -- prevents an
     * employee from being assigned (or assigning themselves) to a request
     * outside their scope even via a direct API/form call.
     */
    public function assignEmployeeToRequest(int $requestId, int $employeeId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.assigned_college_id, r.college_id AS request_college_id
             FROM employees e
             JOIN requests r ON r.request_id = :rid
             WHERE e.employee_id = :eid'
        );
        $stmt->execute([':rid' => $requestId, ':eid' => $employeeId]);
        $row = $stmt->fetch();

        if (!$row || (int) $row['assigned_college_id'] !== (int) $row['request_college_id']) {
            // Mismatch: employee's college != request's college -> reject
            return false;
        }

        $update = $this->pdo->prepare(
            'UPDATE requests SET assigned_employee_id = :eid WHERE request_id = :rid'
        );
        return $update->execute([':eid' => $employeeId, ':rid' => $requestId]);
    }

    private function generateRequestCode(): string
    {
        $year = date('Y');
        $stmt = $this->pdo->query(
            "SELECT COUNT(*) AS cnt FROM requests WHERE request_code LIKE 'REQ-{$year}-%'"
        );
        $count = (int) $stmt->fetch()['cnt'] + 1;
        return sprintf('REQ-%s-%05d', $year, $count);
    }
}
