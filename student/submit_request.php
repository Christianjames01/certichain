<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/RequestRepository.php';

require_role(['student', 'alumni']);

$repo   = new RequestRepository($pdo);
$userId = (int) $_SESSION['user_id'];
$role   = $_SESSION['role']; // 'student' or 'alumni'
$firstName = $_SESSION['first_name'] ?? '';

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

// Pre-select a document type when arriving from Browse Certificates (?document_type_id=X)
$selectedDocumentTypeId = isset($_GET['document_type_id']) ? (int) $_GET['document_type_id'] : 0;

$errors  = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf_token'] ?? '');

    $documentTypeId = (int) ($_POST['document_type_id'] ?? 0);
    $remarks        = trim($_POST['remarks'] ?? '');

    $selectedDocumentTypeId = $documentTypeId; // keep the choice sticky if the form re-renders

    if ($documentTypeId <= 0) {
        $errors[] = 'Please select a document type.';
    } else {
        // Block a new request if the user already has an active (unreleased) request
        // for this same document type.
        $dupStmt = $pdo->prepare(
            "SELECT COUNT(*) FROM requests
             WHERE requester_user_id = :uid
             AND document_type_id = :dtid
             AND status NOT IN ('released', 'completed', 'claimed', 'rejected', 'cancelled')"
        );
        $dupStmt->execute([
            ':uid'  => $userId,
            ':dtid' => $documentTypeId,
        ]);

        if ((int) $dupStmt->fetchColumn() > 0) {
            $errors[] = 'You already have an active request for this document type. Please wait for it to be processed before submitting another one.';
        }
    }

    if (empty($errors)) {
        $requestId = $repo->createRequest($userId, $role, $programId, $documentTypeId, $remarks ?: null);
        log_activity($pdo, $userId, $role, "Submitted request #{$requestId}");
        $success = "Your request has been submitted and routed to {$routingInfo['college_name']}.";
    }
}

// ---- Sidebar pipeline stats (same shape as dashboard.php) ----
$stats = [
    'total'    => 0,
    'pending'  => 0,
    'ready'    => 0,
    'released' => 0,
];

try {
    $stmt = $pdo->prepare(
        'SELECT status, COUNT(*) AS c
         FROM requests
         WHERE requester_user_id = :uid
         GROUP BY status'
    );
    $stmt->execute([':uid' => $userId]);
    foreach ($stmt->fetchAll() as $r) {
        $status = strtolower((string) $r['status']);
        $count  = (int) $r['c'];
        $stats['total'] += $count;
        if (in_array($status, ['pending', 'processing', 'under_review'], true)) {
            $stats['pending'] += $count;
        } elseif (in_array($status, ['ready', 'ready_for_pickup', 'approved'], true)) {
            $stats['ready'] += $count;
        } elseif (in_array($status, ['released', 'completed', 'claimed'], true)) {
            $stats['released'] += $count;
        }
    }
} catch (\PDOException $e) {
    // Non-fatal on this page — the pipeline widget just shows zeros.
}

$pipelineHasPending  = $stats['pending']  > 0;
$pipelineHasReady    = $stats['ready']    > 0;
$pipelineHasReleased = $stats['released'] > 0;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request a Document | CertiChain &middot; Holy Cross of Davao College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="../public/assets/logo/hcdc-logo.jpg">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/student-dashboard.css">
    <link rel="stylesheet" href="../public/assets/css/submit_request.css">
</head>

<body>

    <div class="app-shell">

        <!-- ===================== SIDEBAR ===================== -->
       <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img class="crest" src="../public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
                <div class="brand-text">
                    <div class="school">Holy Cross of Davao College</div>
                    <div class="office">CertiChain &middot; Student Portal</div>
                </div>
            </div>

            <nav class="side-nav" id="dash-nav">
                <a href="dashboard.php">
                    <i class="ti ti-layout-dashboard"></i>Overview
                </a>
                <a href="dashboard.php#requests">
                    <i class="ti ti-file-text"></i>My Requests
                    <?php if ($stats['total'] > 0): ?><span class="side-nav-count"><?= (int) $stats['total'] ?></span><?php endif; ?>
                </a>
                <a href="dashboard.php#account">
                    <i class="ti ti-user"></i>Account
                </a>
            </nav>

            <div class="chain-status" aria-label="Your request pipeline">
                <div class="chain-status-label">Your pipeline</div>
                <div class="chain-track">
                    <div class="chain-node <?= $pipelineHasPending ? 'lit lit-pending' : '' ?>">
                        <span class="chain-num"><?= (int) $stats['pending'] ?></span>
                        <span class="chain-tag">Pending</span>
                    </div>
                    <div class="chain-link <?= $pipelineHasPending ? 'lit' : '' ?>"></div>
                    <div class="chain-node <?= $pipelineHasReady ? 'lit lit-ready' : '' ?>">
                        <span class="chain-num"><?= (int) $stats['ready'] ?></span>
                        <span class="chain-tag">Ready</span>
                    </div>
                    <div class="chain-link <?= $pipelineHasReady ? 'lit' : '' ?>"></div>
                    <div class="chain-node <?= $pipelineHasReleased ? 'lit lit-released' : '' ?>">
                        <span class="chain-num"><?= (int) $stats['released'] ?></span>
                        <span class="chain-tag">Released</span>
                    </div>
                </div>
            </div>

            <div class="sidebar-footer">
                <a href="dashboard.php" class="btn btn-gold btn-block">
                    <i class="ti ti-arrow-left"></i>Back to Dashboard
                </a>
                <a href="../auth/logout.php" class="btn btn-ghost-dark btn-block">
                    <i class="ti ti-logout"></i>Logout
                </a>
            </div>
        </aside>

        <!-- ===================== MAIN COLUMN ===================== -->
        <div class="main-col">

            <header class="topbar">
                <div>
                    <div class="topbar-eyebrow">Dashboard / New request</div>
                    <h1>Request a Document</h1>
                </div>
                <div class="topbar-right">
                    <span class="role-chip"><?= htmlspecialchars(ucfirst($role)) ?></span>
                </div>
            </header>

            <main class="dash-content dash-content-wide">

                <p class="page-lede">
                    Select the document you need. Your request will be routed automatically to the college
                    that handles certifications for your program.
                </p>

                <div class="req-form-layout">

                    <div class="dash-card form-card">
                        <div class="dash-card-head">
                            <h2>Document details</h2>
                        </div>

                        <?php foreach ($errors as $e): ?>
                            <div class="form-alert form-alert-danger">
                                <i class="ti ti-alert-circle"></i>
                                <div><?= htmlspecialchars($e) ?></div>
                            </div>
                        <?php endforeach; ?>

                        <?php if ($success): ?>
                            <div class="form-alert form-alert-success">
                                <i class="ti ti-circle-check"></i>
                                <div><?= htmlspecialchars($success) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if ($routingInfo && !$success): ?>
                            <div class="form-alert form-alert-info">
                                <i class="ti ti-route"></i>
                                <div>
                                    Your program routes this request to
                                    <strong><?= htmlspecialchars($routingInfo['college_name']) ?>
                                        (<?= htmlspecialchars($routingInfo['college_code']) ?>)</strong>.
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="post" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                            <div class="field">
                                <label for="document_type_id">Document type</label>
                               <select id="document_type_id" name="document_type_id" required>
                                    <option value="">-- Select a document --</option>
                                    <?php foreach ($documentTypes as $dt): ?>
                                        <option value="<?= (int) $dt['document_type_id'] ?>"
                                            <?= $selectedDocumentTypeId === (int) $dt['document_type_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($dt['document_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="remarks">Remarks <span class="hint">(optional)</span></label>
                                <textarea id="remarks" name="remarks" rows="4"
                                    placeholder="Any additional details for the registrar..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-gold btn-block" style="margin-top:6px;">
                                <i class="ti ti-file-certificate"></i>Submit Request
                            </button>
                        </form>
                    </div>

                    <div>
                        <?php if ($routingInfo): ?>
                            <div class="dash-card routing-card">
                                <div class="label">Routes to</div>
                                <div class="college-name"><?= htmlspecialchars($routingInfo['college_name']) ?></div>
                                <div class="college-code"><?= htmlspecialchars($routingInfo['college_code']) ?></div>
                                <p class="note">Requests are automatically assigned to the college handling
                                    certifications for your program. You'll be notified once it's reviewed.</p>
                            </div>
                        <?php endif; ?>

                        <div class="dash-card">
                            <div class="dash-card-head">
                                <h2>Need something else?</h2>
                            </div>
                            <div class="quick-actions">
                                <a href="/certichain/certificates/browse.php" class="quick-action">
                                    <i class="ti ti-list-details"></i>Browse all certificate types
                                </a>
                                <a href="dashboard.php#requests" class="quick-action">
                                    <i class="ti ti-history"></i>View request history
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>

</body>

</html>