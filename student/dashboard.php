<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';        // $pdo

// Must be logged in, and must be a student/alumni.
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (!in_array($_SESSION['role'] ?? '', ['student', 'alumni'], true)) {
    header('Location: ../index.php');
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? '';
$role      = $_SESSION['role'];

$stats = [
    'total'    => 0,
    'pending'  => 0,
    'ready'    => 0,
    'released' => 0,
];
$recentRequests = [];
$allRequests    = [];
$dbError = false;

try {
   $stmt = $pdo->prepare(
        'SELECT status, COUNT(*) AS c
         FROM requests
         WHERE requester_user_id = :uid
         GROUP BY status'
    );
    $stmt->execute([':uid' => $userId]);
    foreach ($stmt->fetchAll() as $row) {
        $status = strtolower((string) $row['status']);
        $count  = (int) $row['c'];
        $stats['total'] += $count;
        if (in_array($status, ['pending_review', 'pending', 'processing', 'under_review'], true)) {
            $stats['pending'] += $count;
        } elseif (in_array($status, ['ready', 'ready_for_pickup', 'approved'], true)) {
            $stats['ready'] += $count;
        } elseif (in_array($status, ['released', 'completed', 'claimed'], true)) {
            $stats['released'] += $count;
        }
    }

   $stmt = $pdo->prepare(
        "SELECT r.request_id, r.request_code, r.status, r.created_at,
                dt.document_name AS cert_name,
                p.program_name
         FROM requests r
         LEFT JOIN document_types dt ON dt.document_type_id = r.document_type_id
         LEFT JOIN programs p        ON p.program_id = r.program_id
         WHERE r.requester_user_id = :uid
         ORDER BY r.created_at DESC"
    );
    $stmt->execute([':uid' => $userId]);
    $allRequests    = $stmt->fetchAll();
    $recentRequests = array_slice($allRequests, 0, 5); // used on the Overview tab
} catch (\PDOException $e) {
    $dbError = true;
}

function statusBadgeClass(string $status): string
{
    $s = strtolower($status);
    if (in_array($s, ['pending_review', 'pending', 'processing', 'under_review'], true)) return 'badge-pending';
    if (in_array($s, ['ready', 'ready_for_pickup', 'approved'], true)) return 'badge-ready';
    if (in_array($s, ['released', 'completed', 'claimed'], true)) return 'badge-released';
    if (in_array($s, ['rejected', 'declined', 'cancelled'], true)) return 'badge-rejected';
    return 'badge-pending';
}

// Pipeline widget fill state — a node is "lit" once it has ever had volume.
$pipelineHasPending  = $stats['pending'] > 0;
$pipelineHasReady    = $stats['ready'] > 0;
$pipelineHasReleased = $stats['released'] > 0;

$currentStep = 1;

if (!empty($allRequests)) {

    $latestStatus = strtolower((string) $allRequests[0]['status']);

    switch ($latestStatus) {

        case 'pending':
        case 'pending_review':
            $currentStep = 1;
            break;

        case 'finance_payment':
            $currentStep = 2;
            break;

        case 'receipt_uploaded':
            $currentStep = 3;
            break;

        case 'processing':
        case 'under_review':
            $currentStep = 4;
            break;

        case 'approved':
        case 'ready':
        case 'ready_for_pickup':
            $currentStep = 5;
            break;

        case 'released':
        case 'completed':
        case 'claimed':
            $currentStep = 6;
            break;
    }
}

$steps = [
    1 => 'Request',
    2 => 'Finance Payment',
    3 => 'Show Receipt',
    4 => 'Processing',
    5 => 'Approval',
    6 => 'Claim Physical'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard | CertiChain &middot; Holy Cross of Davao College</title>
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
                <button type="button" class="active" data-tab="overview">
                    <i class="ti ti-layout-dashboard"></i>Overview
                </button>
                <button type="button" data-tab="requests">
                    <i class="ti ti-file-text"></i>My Requests
                    <?php if ($stats['total'] > 0): ?><span class="side-nav-count"><?= (int) $stats['total'] ?></span><?php endif; ?>
                </button>
                <button type="button" data-tab="account">
                    <i class="ti ti-user"></i>Account
                </button>
            </nav>

           <div class="chain-status">

    <div class="chain-status-label">
        Certificate Request Process
    </div>

    <div class="process-list">

        <?php foreach ($steps as $number => $label): ?>

            <div class="process-item">

                <span class="process-icon">

                    <?php if ($number < $currentStep): ?>

                        ✓

                    <?php elseif ($number == $currentStep): ?>

                        ●

                    <?php else: ?>

                        ○

                    <?php endif; ?>

                </span>

                <span class="process-text">

                    <?= htmlspecialchars($label) ?>

                </span>

            </div>

        <?php endforeach; ?>

    </div>

    <div class="process-legend">

        ✓ = Completed<br>
        ● = Current step<br>
        ○ = Upcoming step

    </div>

</div>
            <div class="sidebar-footer">
                <a href="../student/dashboard.php" class="btn btn-gold btn-block">
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
                    <div class="topbar-eyebrow">Welcome back</div>
                    <h1><?= htmlspecialchars($firstName ?: 'Student') ?></h1>
                </div>
                <div class="topbar-right">
                    <span class="role-chip"><?= htmlspecialchars(ucfirst($role)) ?></span>
                </div>
            </header>

            <main class="dash-content">

                <!-- ---------- OVERVIEW TAB ---------- -->
                <section class="dash-tab active" data-tab-panel="overview">
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h2>Recent requests</h2>
                            <button type="button" class="section-link link-btn" data-goto-tab="requests">View all</button>
                        </div>

                        <?php if ($dbError): ?>
                            <div class="empty-state">
                                <i class="ti ti-alert-triangle"></i>
                                Couldn't load your requests right now. Please try again later.
                            </div>
                        <?php elseif (!$recentRequests): ?>
                            <div class="empty-state">
                                <i class="ti ti-file-off"></i>
                                You haven't submitted any certificate requests yet.<br>
                                <a href="submit_request.php" class="link-muted">Submit your first request &rarr;</a>
                            </div>
                        <?php else: ?>
                            <table class="req-table">
                              <thead>
                                    <tr>
                                        <th>Certificate</th>
                                        <th>Program</th>
                                        <th>Date requested</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentRequests as $r): ?>
                                        <tr>
                                            <td class="cert-name"><?= htmlspecialchars($r['cert_name'] ?? 'Certificate') ?></td>
                                            <td><?= htmlspecialchars($r['program_name'] ?? '—') ?></td>
                                            <td class="req-date">
                                                <?= htmlspecialchars(date('M j, Y', strtotime((string) $r['created_at']))) ?>
                                            </td>
                                            <td>
                                                <span class="badge <?= statusBadgeClass((string) $r['status']) ?>">
                                                    <?= htmlspecialchars(str_replace('_', ' ', (string) $r['status'])) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view_request.php?code=<?= urlencode((string) $r['request_code']) ?>" class="link-muted"                                                    style="font-size:12.5px;">View &rarr;</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>

                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h2>Quick actions</h2>
                        </div>
                        <div class="quick-actions">
                            <a href="submit_request.php" class="quick-action">
                                <i class="ti ti-file-certificate"></i>Request a certificate
                            </a>
                            <a href="submit_request.php" class="quick-action">
                                <i class="ti ti-upload"></i>Upload official receipt
                            </a>
                            <a href="../certservices.php" class="quick-action">
                                <i class="ti ti-list-details"></i>Browse all certificate types
                            </a>
                        </div>
                    </div>
                </section>

                <!-- ---------- MY REQUESTS TAB ---------- -->
                <section class="dash-tab" data-tab-panel="requests">
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h2>All requests</h2>
                            <a href="submit_request.php" class="section-link" style="font-size:12.5px;">New request</a>
                        </div>

                        <?php if ($dbError): ?>
                            <div class="empty-state">
                                <i class="ti ti-alert-triangle"></i>
                                Couldn't load your requests right now. Please try again later.
                            </div>
                        <?php elseif (!$allRequests): ?>
                            <div class="empty-state">
                                <i class="ti ti-file-off"></i>
                                You haven't submitted any certificate requests yet.<br>
                                <a href="submit_request.php" class="link-muted">Submit your first request &rarr;</a>
                            </div>
                        <?php else: ?>
                            <div class="full-req-table-wrap">
                                <table class="req-table">
                                  <thead>
                                        <tr>
                                            <th>Certificate</th>
                                            <th>Program</th>
                                            <th>Date requested</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allRequests as $r): ?>
                                            <tr>
<td class="cert-name"><?= htmlspecialchars($r['cert_name'] ?? 'Certificate') ?></td>
                                                <td><?= htmlspecialchars($r['program_name'] ?? '—') ?></td>
                                                <td class="req-date">
                                                    <?= htmlspecialchars(date('M j, Y', strtotime((string) $r['created_at']))) ?>
                                                </td>
                                                <td>
                                                    <span class="badge <?= statusBadgeClass((string) $r['status']) ?>">
                                                        <?= htmlspecialchars(str_replace('_', ' ', (string) $r['status'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <a href="view_request.php?id=<?= (int) $r['request_id'] ?>" class="link-muted"
                                                        style="font-size:12.5px;">View &rarr;</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- ---------- ACCOUNT TAB ---------- -->
                <section class="dash-tab" data-tab-panel="account">
                    <div class="dash-card">
                        <div class="dash-card-head">
                            <h2>Account</h2>
                        </div>
                        <div class="account-row">
                            <span class="k">Name</span>
                            <span class="v"><?= htmlspecialchars($firstName) ?></span>
                        </div>
                        <div class="account-row">
                            <span class="k">Role</span>
                            <span class="v"><?= htmlspecialchars(ucfirst($role)) ?></span>
                        </div>
                        <div class="account-row">
                            <span class="k">Total requests filed</span>
                            <span class="v"><?= (int) $stats['total'] ?></span>
                        </div>
                    </div>
                </section>

            </main>
        </div>
    </div>

    <script>
        // Sidebar tab switching — shows/hides .dash-tab panels and toggles
        // the .active state on the corresponding .side-nav button.
        (function () {
            const navButtons = document.querySelectorAll('#dash-nav button[data-tab]');
            const panels = document.querySelectorAll('.dash-tab[data-tab-panel]');

            function activate(tabName) {
                navButtons.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tabName));
                panels.forEach(p => p.classList.toggle('active', p.dataset.tabPanel === tabName));
            }

            navButtons.forEach(btn => {
                btn.addEventListener('click', () => activate(btn.dataset.tab));
            });

            // "View all" link on the Overview card jumps straight to the Requests tab.
            document.querySelectorAll('[data-goto-tab]').forEach(el => {
                el.addEventListener('click', () => activate(el.dataset.gotoTab));
            });
        })();
    </script>

</body>

</html>