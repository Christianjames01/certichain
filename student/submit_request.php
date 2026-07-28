<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/RequestRepository.php';

require_role(['student', 'alumni']);

$repo = new RequestRepository($pdo);
$userId = (int) $_SESSION['user_id'];
$role   = $_SESSION['role']; // 'student' or 'alumni'

// Look up this user's program_id from the students/alumni table
$table = $role === 'student' ? 'students' : 'alumni';
$stmt = $pdo->prepare("SELECT program_id FROM {$table} WHERE user_id = :uid");
$stmt->execute([':uid' => $userId]);
$row = $stmt->fetch();

if (!$row) {
    die('No program record found for this account.');
}
$programId = (int) $row['program_id'];

// Show the requester which college/employee pool will handle their request
$routingInfo = $repo->getCollegeForProgram($programId);

$documentTypes = $pdo->query('SELECT document_type_id, document_name FROM document_types WHERE is_active = 1 ORDER BY document_name')->fetchAll();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf_token'] ?? '');

    $documentTypeId = (int) ($_POST['document_type_id'] ?? 0);
    $remarks        = trim($_POST['remarks'] ?? '');

    if ($documentTypeId <= 0) {
        $errors[] = 'Please select a document type.';
    }

    if (empty($errors)) {
        $requestId = $repo->createRequest($userId, $role, $programId, $documentTypeId, $remarks ?: null);
        log_activity($pdo, $userId, $role, "Submitted request #{$requestId}");
        $success = "Your request has been submitted and routed to {$routingInfo['college_name']}.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Request a Document - CERTICHAIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4" style="max-width:600px;">
    <h4>Request an Academic Document</h4>

    <?php if ($routingInfo): ?>
        <div class="alert alert-info">
            Your program routes this request to:
            <strong><?= htmlspecialchars($routingInfo['college_name']) ?> (<?= htmlspecialchars($routingInfo['college_code']) ?>)</strong>
        </div>
    <?php endif; ?>

    <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="mb-3">
            <label class="form-label">Document Type</label>
            <select name="document_type_id" class="form-select" required>
                <option value="">-- Select --</option>
                <?php foreach ($documentTypes as $dt): ?>
                    <option value="<?= (int) $dt['document_type_id'] ?>">
                        <?= htmlspecialchars($dt['document_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Remarks (optional)</label>
            <textarea name="remarks" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Request</button>
    </form>
</div>
</body>
</html>
