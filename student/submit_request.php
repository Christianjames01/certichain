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

$errors  = [];
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
    <link rel="stylesheet" href="../public/assets/css/submit-request.css">


</head>

<body>

    <div class="utility-social">
        <a href="#" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
        <a href="#" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
        <a href="#" aria-label="YouTube"><i class="ti ti-brand-youtube"></i></a>
    </div>

    <header class="main">
        <div class="header-row">
            <div class="brand">
                <img class="crest" src="../public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
                <div class="brand-text">
                    <div class="school">Holy Cross of Davao College</div>
                    <div class="office">The Office of Registration and Records Management &middot; CertiChain</div>
                </div>
            </div>
            <nav class="primary">
                <a href="../index.php">Home</a>
                <a href="dashboard.php">Dashboard</a>
                <a href="../certservices.php">Registrar Services</a>
            </nav>
            <div class="header-cta">
                <span style="font-size:13.5px;color:var(--hcdc-ink-soft);margin-right:4px;">
                    Hi, <?= htmlspecialchars($firstName) ?>
                </span>
                <a href="dashboard.php" class="btn btn-ghost"><i class="ti ti-layout-dashboard"></i>Dashboard</a>
                <a href="../auth/logout.php" class="btn btn-ghost"><i class="ti ti-logout"></i>Logout</a>
            </div>
        </div>
    </header>

    <section class="page-hero">
        <div class="wrap">
            <div class="breadcrumb">
                <a href="dashboard.php">Dashboard</a>
                <span>/</span>
                <span>Request a document</span>
            </div>
            <h1>Request an Academic Document</h1>
            <p class="sub">Select the document you need. Your request will be routed automatically to the college
                that handles certifications for your program.</p>
        </div>
    </section>

    <div class="wrap" style="padding:44px 0 72px;">
        <div class="req-form-layout">

            <div>
                <div class="form-card">
                    <h2 class="block-title">Document details</h2>

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
                                    <option value="<?= (int) $dt['document_type_id'] ?>">
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

                        <button type="submit" class="btn btn-gold" style="width:100%;justify-content:center;margin-top:6px;">
                            <i class="ti ti-file-certificate"></i>Submit Request
                        </button>
                    </form>
                </div>
            </div>

            <div>
                <?php if ($routingInfo): ?>
                    <div class="routing-card">
                        <div class="label">Routes to</div>
                        <div class="college-name"><?= htmlspecialchars($routingInfo['college_name']) ?></div>
                        <div class="college-code"><?= htmlspecialchars($routingInfo['college_code']) ?></div>
                        <p class="note">Requests are automatically assigned to the college handling certifications
                            for your program. You'll be notified once it's reviewed.</p>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <footer id="about">
        <div class="wrap">
            <div class="footer-bottom" style="border-top:none;padding-top:0;">
                <span>&copy; <?= date('Y') ?> Holy Cross of Davao College &middot; The Office of Registration and Records
                    Management</span>
            </div>
        </div>
    </footer>

</body>

</html>