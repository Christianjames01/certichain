<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';        // $pdo
require_once __DIR__ . '/../includes/auth.php';

// Must be logged in, and must be an employee.
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: ../index.php');
    exit;
}

$userId = (int) $_SESSION['user_id'];
$requestId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($requestId <= 0) {
    die("Invalid request ID.");
}

$dbError = false;
$requestData = null;

try {
    // 1. Get the employee's assigned program and college parameters
    $stmt = $pdo->prepare("
        SELECT assigned_college_id, program_id
        FROM employees
        WHERE user_id = :uid
        LIMIT 1
    ");
    $stmt->execute([':uid' => $userId]);
    $employeeRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employeeRow || empty($employeeRow['program_id'])) {
        die("Account Error: You have not been assigned to an active program queue yet.");
    }

    $assignedProgramId = (int) $employeeRow['program_id'];

    // 2. Fetch the request details ONLY if it matches the employee's assigned program_id
    $stmt = $pdo->prepare("
        SELECT
            r.request_id,
            r.status,
            r.created_at,
            r.program_id,
            dt.document_name AS cert_name,
            u.first_name,
            u.last_name,
            s.student_number
        FROM requests r
        JOIN students s
            ON s.user_id = r.requester_user_id
        JOIN users u
            ON u.user_id = s.user_id
        LEFT JOIN document_types dt
            ON dt.document_type_id = r.document_type_id
        WHERE r.request_id = :request_id AND r.program_id = :program_id
        LIMIT 1
    ");
    
    $stmt->execute([
        ':request_id' => $requestId,
        ':program_id' => $assignedProgramId
    ]);

    $requestData = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no row matches, it means the request doesn't exist or belongs to another program queue
    if (!$requestData) {
        die("Access Denied: Request not found, or it is not assigned to your active program queue.");
    }

} catch (PDOException $e) {
    $dbError = true;
    die("Database Error: Unable to safely process request verification data.");
}

// Handle form submissions for status changes (e.g., updating to Ready or Released)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = trim($_POST['status'] ?? '');
    
    // Simple validation array matching your dashboard tracker keys
    $validStatuses = ['pending_review', 'approved', 'ready', 'released', 'completed', 'rejected'];
    
    if (in_array(strtolower($newStatus), $validStatuses, true)) {
        try {
            $updateStmt = $pdo->prepare("
                UPDATE requests 
                SET status = :status 
                WHERE request_id = :request_id AND program_id = :program_id
            ");
            $updateStmt->execute([
                ':status' => $newStatus,
                ':request_id' => $requestId,
                ':program_id' => $assignedProgramId
            ]);
            
            // Refresh page to show updated badge parameters
            header("Location: view_request.php?id=" . $requestId);
            exit;
        } catch (PDOException $e) {
            $dbError = true;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Request #<?= $requestId ?> | CertiChain</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/employee-dashboard.css">
    <style>
        .view-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px; max-width: 650px; margin: 40px auto; }
        .meta-group { margin-bottom: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px; }
        .meta-label { font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 600; }
        .meta-val { font-size: 16px; color: #1e293b; font-weight: 500; margin-top: 4px; }
        .actions-bar { margin-top: 24px; display: flex; gap: 12px; align-items: center; }
        select { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-family: inherit; }
    </style>
</head>
<body>

    <div class="view-card">
        <div style="margin-bottom: 20px;">
            <a href="dashboard.php" class="link-muted" style="font-size: 14px;">&larr; Back to Dashboard</a>
        </div>
        
        <h2>Review Document Request #<?= (int)$requestData['request_id'] ?></h2>
        <p style="color: #64748b; font-size: 14px; margin-bottom: 24px;">Manage validation actions for this program document tracker.</p>

        <div class="meta-group">
            <div class="meta-label">Student Requester</div>
            <div class="meta-val"><?= htmlspecialchars($requestData['first_name'] . ' ' . $requestData['last_name']) ?> (<?= htmlspecialchars($requestData['student_number']) ?>)</div>
        </div>

        <div class="meta-group">
            <div class="meta-label">Requested Document</div>
            <div class="meta-val"><?= htmlspecialchars($requestData['cert_name'] ?? 'Official Certificate') ?></div>
        </div>

        <div class="meta-group">
            <div class="meta-label">Date Submitted</div>
            <div class="meta-val"><?= htmlspecialchars(date('F j, Y, g:i a', strtotime((string)$requestData['created_at']))) ?></div>
        </div>

        <div class="meta-group">
            <div class="meta-label">Current Processing Status</div>
            <div class="meta-val">
                <span style="text-transform: capitalize; font-weight:600; color:#2563eb;"><?= htmlspecialchars(str_replace('_', ' ', (string)$requestData['status'])) ?></span>
            </div>
        </div>

        <!-- Status Modification Form -->
        <form method="POST" action="" class="actions-bar">
            <label for="status" style="font-weight: 500;">Update Status:</label>
            <select name="status" id="status">
                <option value="pending_review" <?= $requestData['status'] === 'pending_review' ? 'selected' : '' ?>>Pending Review</option>
                <option value="ready" <?= $requestData['status'] === 'ready' ? 'selected' : '' ?>>Ready for Pickup</option>
                <option value="released" <?= $requestData['status'] === 'released' ? 'selected' : '' ?>>Released / Completed</option>
                <option value="rejected" <?= $requestData['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
            <button type="submit" name="update_status" class="btn" style="padding: 8px 16px; background: #1e293b; color: #fff; border: none; border-radius: 6px; cursor: pointer;">
                Save Changes
            </button>
        </form>
    </div>

</body>
</html>
