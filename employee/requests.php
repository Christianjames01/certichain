<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/RequestRepository.php';

require_role(['employee']);

// This is the ONLY college_id this employee is allowed to query.
// It comes from the DB (employees.assigned_college_id), never from the URL
// or a form field, so it cannot be tampered with by the client.
$collegeId = current_employee_college_id($pdo);

$repo = new RequestRepository($pdo);

$statusFilter = $_GET['status'] ?? null;
$allowedStatuses = [
    'pending_review', 'requirements_incomplete', 'under_verification',
    'approved', 'awaiting_payment', 'payment_verified',
    'scheduled', 'completed', 'rejected', 'cancelled',
];
if ($statusFilter !== null && !in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = null;
}

$requests = $repo->getRequestsForEmployeeCollege($collegeId, $statusFilter);

// Fetch the college name just for display in the header
$stmt = $pdo->prepare('SELECT college_name, college_code FROM colleges WHERE college_id = :id');
$stmt->execute([':id' => $collegeId]);
$college = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Assigned Requests - CERTICHAIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h3>Requests Assigned to <?= htmlspecialchars($college['college_name'] ?? '') ?>
        (<?= htmlspecialchars($college['college_code'] ?? '') ?>)</h3>
    <p class="text-muted">You only see requests submitted by students/alumni whose program belongs to your college.</p>

    <table class="table table-bordered table-striped bg-white" id="requestsTable">
        <thead>
            <tr>
                <th>Request Code</th>
                <th>Requester</th>
                <th>Program</th>
                <th>Document</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($requests as $r): ?>
            <tr>
                <td><?= htmlspecialchars($r['request_code']) ?></td>
                <td><?= htmlspecialchars($r['first_name'] . ' ' . $r['last_name']) ?></td>
                <td><?= htmlspecialchars($r['degree_code']) ?></td>
                <td><?= htmlspecialchars($r['document_name']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($r['status']) ?></span></td>
                <td><?= htmlspecialchars($r['created_at']) ?></td>
                <td>
                    <a href="view_request.php?request_id=<?= (int)$r['request_id'] ?>" class="btn btn-sm btn-primary">Open</a>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($requests)): ?>
            <tr><td colspan="7" class="text-center text-muted">No requests found.</td></tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script>$(document).ready(function () { $('#requestsTable').DataTable(); });</script>
</body>
</html>
