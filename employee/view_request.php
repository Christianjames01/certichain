<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/RequestRepository.php';

require_role(['employee']);

$collegeId = current_employee_college_id($pdo);
$repo = new RequestRepository($pdo);

$requestId = (int) ($_GET['request_id'] ?? 0);

// Scoped lookup: if this request belongs to another college, this returns
// null even though the row exists in the DB -- the employee gets a 404,
// not the data.
$request = $repo->getRequestForEmployeeCollege($requestId, $collegeId);

if (!$request) {
    http_response_code(404);
    die('Request not found, or it is not assigned to your college.');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($request['request_code']) ?> - CERTICHAIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h4>Request <?= htmlspecialchars($request['request_code']) ?></h4>
    <table class="table table-bordered bg-white">
        <tr><th>Requester</th><td><?= htmlspecialchars($request['first_name'] . ' ' . $request['last_name']) ?> (<?= htmlspecialchars($request['email']) ?>)</td></tr>
        <tr><th>Program</th><td><?= htmlspecialchars($request['degree_code']) ?></td></tr>
        <tr><th>Document Requested</th><td><?= htmlspecialchars($request['document_name']) ?></td></tr>
        <tr><th>Status</th><td><?= htmlspecialchars($request['status']) ?></td></tr>
        <tr><th>Submitted</th><td><?= htmlspecialchars($request['created_at']) ?></td></tr>
        <tr><th>Remarks</th><td><?= nl2br(htmlspecialchars($request['remarks'] ?? '')) ?></td></tr>
    </table>
    <a href="requests.php" class="btn btn-secondary">Back to My Requests</a>
</div>
</body>
</html>
