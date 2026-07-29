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

/**
 * ------------------------------------------------------------------
 * Stats + recent requests
 * ------------------------------------------------------------------
 * NOTE: Column names below are best-guess placeholders (request_id,
 * user_id, status, document_type_id / cert_type, created_at) since
 * the exact `requests` table schema wasn't available when this file
 * was generated. If a query below throws, adjust the column names
 * to match `DESCRIBE requests;` from phpMyAdmin — same fix pattern
 * as the login.php `user_id` issue. Everything fails soft to an
 * empty state so this page won't fatal-error either way.
 * ------------------------------------------------------------------
 */
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
         WHERE user_id = :uid
         GROUP BY status'
    );
    $stmt->execute([':uid' => $userId]);
    foreach ($stmt->fetchAll() as $row) {
        $status = strtolower((string) $row['status']);
        $count  = (int) $row['c'];
        $stats['total'] += $count;
        if (in_array($status, ['pending', 'processing', 'under_review'], true)) {
            $stats['pending'] += $count;
        } elseif (in_array($status, ['ready', 'ready_for_pickup', 'approved'], true)) {
            $stats['ready'] += $count;
        } elseif (in_array($status, ['released', 'completed', 'claimed'], true)) {
            $stats['released'] += $count;
        }
    }

    $stmt = $pdo->prepare(
        "SELECT r.request_id, r.status, r.created_at,
                COALESCE(dt.name, r.cert_type) AS cert_name
         FROM requests r
         LEFT JOIN document_types dt ON dt.document_type_id = r.document_type_id
         WHERE r.user_id = :uid
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
    if (in_array($s, ['pending', 'processing', 'under_review'], true)) return 'badge-pending';
    if (in_array($s, ['ready', 'ready_for_pickup', 'approved'], true)) return 'badge-ready';
    if (in_array($s, ['released', 'completed', 'claimed'], true)) return 'badge-released';
    if (in_array($s, ['rejected', 'declined', 'cancelled'], true)) return 'badge-rejected';
    return 'badge-pending';
}
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
            <nav class="dash-nav" id="dash-nav">
                <button type="button" class="active" data-tab="overview">
                    <i class="ti ti-layout-dashboard"></i>Overview
                </button>
                <button type="button" data-tab="requests">
                    <i class="ti ti-file-text"></i>My Requests
                </button>
                <button type="button" data-tab="account">
                    <i class="ti ti-user"></i>Account
                </button>
            </nav>
            <div class="header-cta">
                <span style="font-size:13.5px;color:var(--hcdc-ink-soft);margin-right:4px;">
                    Hi, <?= htmlspecialchars($firstName) ?>
                </span>
                <a href="submit_request.php" class="btn btn-gold">
                    <i class="ti ti-file-plus"></i>New Request
                </a>
                <a href="../auth/logout.php" class="btn btn-ghost"><i class="ti ti-logout"></i>Logout</a>
            </div>
        </div>
    </header>

    <section class="dash-hero">
        <div class="wrap">
            <div>
                <h1>Welcome back, <?= htmlspecialchars($firstName) ?></h1>
                <p>Here's an overview of your certificate requests and account status.</p>
            </div>
            <a href="submit_request.php" class="btn btn-gold">
                <i class="ti ti-file-certificate"></i>Request a Certificate
            </a>
        </div>
    </section>

    <div class="wrap">

        <div class="dash-stats">
            <div class="dash-stat-card">
                <div class="num"><?= (int) $stats['total'] ?></div>
                <div class="lab">Total requests</div>
            </div>
            <div class="dash-stat-card">
                <div class="num"><?= (int) $stats['pending'] ?></div>
                <div class="lab">Pending / in review</div>
            </div>
            <div class="dash-stat-card">
                <div class="num"><?= (int) $stats['ready'] ?></div>
                <div class="lab">Ready for pickup</div>
            </div>
            <div class="dash-stat-card">
                <div class="num"><?= (int) $stats['released'] ?></div>
                <div class="lab">Released</div>
            </div>
        </div>

        <div class="dash-layout">
            <div>
                <div class="dash-card">
                    <div class="dash-card-head">
                        <h2>Recent requests</h2>
                        <a href="submit_request.php" class="section-link" style="font-size:12.5px;">View all</a>
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
                                    <th>Date requested</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentRequests as $r): ?>
                                    <tr>
                                        <td class="cert-name"><?= htmlspecialchars($r['cert_name'] ?? 'Certificate') ?></td>
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
                    <?php endif; ?>
                </div>
            </div>

            <div>
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
                </div>
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