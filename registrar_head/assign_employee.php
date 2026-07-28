<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['registrar_head']);

$colleges = $pdo->query('SELECT college_id, college_code, college_name FROM colleges WHERE is_active = 1 ORDER BY college_name')->fetchAll();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf_token'] ?? '');

    $employeeId = (int) ($_POST['employee_id'] ?? 0);
    $collegeId  = (int) ($_POST['college_id'] ?? 0);

    if ($employeeId <= 0 || $collegeId <= 0) {
        $errors[] = 'Please select both an employee and a college.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE employees SET assigned_college_id = :cid WHERE employee_id = :eid');
        $stmt->execute([':cid' => $collegeId, ':eid' => $employeeId]);
        log_activity($pdo, (int) $_SESSION['user_id'], 'registrar_head', "Reassigned employee #{$employeeId} to college #{$collegeId}");
        $success = 'Employee college assignment updated.';
    }
}

$employees = $pdo->query(
    'SELECT e.employee_id, u.first_name, u.last_name, c.college_name, c.college_id
     FROM employees e
     JOIN users u ON u.user_id = e.user_id
     LEFT JOIN colleges c ON c.college_id = e.assigned_college_id
     ORDER BY u.last_name'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assign Employees to Colleges - CERTICHAIN</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h4>Employee &rarr; College Assignment</h4>
    <p class="text-muted">An employee will only ever see requests from students/alumni whose program belongs to the college assigned here.</p>

    <?php foreach ($errors as $e): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
    <?php endforeach; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="row g-3 mb-4">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
        <div class="col-md-5">
            <select name="employee_id" class="form-select" required>
                <option value="">-- Select Employee --</option>
                <?php foreach ($employees as $emp): ?>
                    <option value="<?= (int) $emp['employee_id'] ?>">
                        <?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?>
                        (currently: <?= htmlspecialchars($emp['college_name'] ?? 'unassigned') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-5">
            <select name="college_id" class="form-select" required>
                <option value="">-- Assign to College --</option>
                <?php foreach ($colleges as $c): ?>
                    <option value="<?= (int) $c['college_id'] ?>">
                        <?= htmlspecialchars($c['college_name']) ?> (<?= htmlspecialchars($c['college_code']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Save</button>
        </div>
    </form>

    <table class="table table-bordered bg-white">
        <thead><tr><th>Employee</th><th>Assigned College</th></tr></thead>
        <tbody>
        <?php foreach ($employees as $emp): ?>
            <tr>
                <td><?= htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']) ?></td>
                <td><?= htmlspecialchars($emp['college_name'] ?? 'Unassigned') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
