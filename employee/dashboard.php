<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';        // $pdo

// Must be logged in, and must be an employee.
if (empty($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
if (($_SESSION['role'] ?? '') !== 'employee') {
    header('Location: ../index.php');
    exit;
}

$userId    = (int) $_SESSION['user_id'];
$firstName = $_SESSION['first_name'] ?? '';

/**
 * ------------------------------------------------------------------
 * Schema notes (best-guess placeholders — adjust to match your DB):
 *
 *   users(user_id, first_name, last_name, role, program_id, ...)
 *     - an employee's assigned program lives on their own user row,
 *       set via registrar_head/assign_employee.php.
 *   programs(program_id, program_name, program_code)
 *   requests(request_id, user_id, document_type_id, cert_type,
 *            status, created_at, ...)
 *     - requests.user_id references the STUDENT who filed it.
 *
 * If any query below throws, check these column names against
 * `DESCRIBE users;`, `DESCRIBE programs;`, `DESCRIBE requests;`
 * in phpMyAdmin and adjust. Everything fails soft to an empty
 * state so this page won't fatal-error either way.
 * ------------------------------------------------------------------
 */

$programId    = null;
$programName  = null;
$programCode  = null;
$dbError      = false;
$noProgram    = false;

$stats = [
    'total'    => 0,
    'pending'  => 0,
    'ready'    => 0,
    'released' => 0,
];
$recentRequests = [];
$allRequests    = [];

try {
    // 1. Find which program this employee is assigned to.
    $stmt = $pdo->prepare(
        'SELECT u.program_id, p.program_name, p.program_code
         FROM users u
         LEFT JOIN programs p ON p.program_id = u.program_id
         WHERE u.user_id = :uid'
    );
    $stmt->execute([':uid' => $userId]);
    $employeeRow = $stmt->fetch();

    if (!$employeeRow || empty($employeeRow['program_id'])) {
        $noProgram = true;
    } else {
        $programId   = (int) $employeeRow['program_id'];
        $programName = $employeeRow['program_name'] ?? null;
        $programCode = $employeeRow['program_code'] ?? null;

        // 2. Status counts, scoped to students in this program only.
        $stmt = $pdo->prepare(
            'SELECT r.status, COUNT(*) AS c
             FROM requests r
             JOIN users u ON u.user_id = r.user_id
             WHERE u.program_id = :pid
             GROUP BY r.status'
        );
        $stmt->execute([':pid' => $programId]);
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

        // 3. Full request list, scoped to students in this program only.
        $stmt = $pdo->prepare(
            "SELECT r.request_id, r.status, r.created_at,
                    COALESCE(dt.name, r.cert_type) AS cert_name,
                    u.first_name, u.last_name, u.user_id AS student_id
             FROM requests r
             JOIN users u ON u.user_id = r.user_id
             LEFT JOIN document_types dt ON dt.document_type_id = r.document_type_id
             WHERE u.program_id = :pid
             ORDER BY r.created_at DESC"
        );
        $stmt->execute([':pid' => $programId]);
        $allRequests    = $stmt->fetchAll();
        $recentRequests = array_slice($allRequests, 0, 8); // Overview tab
    }
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

function studentFullName(array $r): string
{
    $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
    return $name !== '' ? $name : 'Student #' . ($r['student_id'] ?? '');
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
    <title>Employee Dashboard | CertiChain &middot; Holy Cross of Davao College</title>
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
    <link rel="stylesheet" href="../public/assets/css/employee-dashboard.css">
</head>

<body>

    <div class="app-shell">

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <img class="crest" src="../public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
                <div class="brand-text">
                    <div class="school">Holy Cross of Davao College</div>
                    <div class="office">CertiChain &middot; Employee Portal</div>
                </div>
            </div>

            <?php if (!$noProgram): ?>
                <div class="program-badge">
                    <span class="program-badge-label">Assigned program</span>
                    <span class="program-badge-name">
                        <?= htmlspecialchars($programName ?: 'Program #' . $programId) ?>
                        <?php if ($programCode): ?><span class="program-badge-code"><?= htmlspecialchars($programCode) ?></span><?php endif; ?>
                    </span>
                </div>
            <?php endif; ?>

            <nav class="side-nav" id="dash-nav">
                <button type="button" class="active" data-tab="overview">
                    <i class="ti ti-layout-dashboard"></i>Overview
                </button>
                <button type="button" data-tab="requests">
                    <i class="ti ti-file-text"></i>Program Requests
                    <?php if ($stats['total'] > 0): ?><span class="side-nav-count"><?= (int) $stats['total'] ?></span><?php endif; ?>
                </button>
                <button type="button" data-tab="account">
                    <i class="ti ti-user"></i>Account
                </button>
            </nav>

            <?php if (!$noProgram): ?>
                <div class="chain-status" aria-label="Program request pipeline">
                    <div class="chain-status-label">Program pipeline</div>
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
            <?php endif; ?>

            <div class="sidebar-footer">
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
                    <h1><?= htmlspecialchars($firstName ?: 'Employee') ?></h1>
                </div>
                <div class="topbar-right">
                    <span class="role-chip">Employee</span>
                </div>
            </header>

            <main class="dash-content">

                <?php if ($noProgram): ?>
                    <div class="dash-card">
                        <div class="empty-state">
                            <i class="ti ti-building-warehouse"></i>
                            You haven't been assigned to a program yet.<br>
                            Ask the Registrar Head to assign you to a program before you can view student requests.
                        </div>
                    </div>
                <?php else: ?>

                    <!-- ---------- OVERVIEW TAB ---------- -->
                    <section class="dash-tab active" data-tab-panel="overview">
                        <div class="dash-card">
                            <div class="dash-card-head">
                                <h2>Recent requests &middot; <?= htmlspecialchars($programName ?: 'your program') ?></h2>
                                <button type="button" class="section-link link-btn" data-goto-tab="requests">View all</button>
                            </div>

                            <?php if ($dbError): ?>
                                <div class="empty-state">
                                    <i class="ti ti-alert-triangle"></i>
                                    Couldn't load requests right now. Please try again later.
                                </div>
                            <?php elseif (!$recentRequests): ?>
                                <div class="empty-state">
                                    <i class="ti ti-file-off"></i>
                                    No certificate requests have been filed by students in this program yet.
                                </div>
                            <?php else: ?>
                                <table class="req-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Certificate</th>
                                            <th>Date requested</th>
                                            <th>Status</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRequests as $r): ?>
                                            <tr>
                                                <td class="cert-name"><?= htmlspecialchars(studentFullName($r)) ?></td>
                                                <td><?= htmlspecialchars($r['cert_name'] ?? 'Certificate') ?></td>
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
                                                        style="font-size:12.5px;">Review &rarr;</a>
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
                                <a href="requests.php" class="quick-action">
                                    <i class="ti ti-list-details"></i>Go to the full requests queue
                                </a>
                                <button type="button" class="quick-action" data-goto-tab="requests" style="cursor:pointer;">
                                    <i class="ti ti-search"></i>Search requests in this program
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- ---------- PROGRAM REQUESTS TAB ---------- -->
                    <section class="dash-tab" data-tab-panel="requests">
                        <div class="dash-card">
                            <div class="dash-card-head">
                                <h2>All requests &middot; <?= htmlspecialchars($programName ?: 'your program') ?></h2>
                                <span class="req-count-pill"><?= (int) $stats['total'] ?> total</span>
                            </div>

                            <?php if (!$dbError && $allRequests): ?>
                                <div class="req-search">
                                    <i class="ti ti-search"></i>
                                    <input type="text" id="req-search-input" placeholder="Search by student name, certificate, or status&hellip;" autocomplete="off">
                                </div>
                            <?php endif; ?>

                            <?php if ($dbError): ?>
                                <div class="empty-state">
                                    <i class="ti ti-alert-triangle"></i>
                                    Couldn't load requests right now. Please try again later.
                                </div>
                            <?php elseif (!$allRequests): ?>
                                <div class="empty-state">
                                    <i class="ti ti-file-off"></i>
                                    No certificate requests have been filed by students in this program yet.
                                </div>
                            <?php else: ?>
                                <div class="full-req-table-wrap">
                                    <table class="req-table" id="req-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Certificate</th>
                                                <th>Date requested</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($allRequests as $r): ?>
                                                <?php
                                                    $studentName = studentFullName($r);
                                                    $certName    = $r['cert_name'] ?? 'Certificate';
                                                    $statusText  = str_replace('_', ' ', (string) $r['status']);
                                                    $searchBlob  = strtolower($studentName . ' ' . $certName . ' ' . $statusText);
                                                ?>
                                                <tr data-search="<?= htmlspecialchars($searchBlob) ?>">
                                                    <td class="cert-name"><?= htmlspecialchars($studentName) ?></td>
                                                    <td><?= htmlspecialchars($certName) ?></td>
                                                    <td class="req-date">
                                                        <?= htmlspecialchars(date('M j, Y', strtotime((string) $r['created_at']))) ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge <?= statusBadgeClass((string) $r['status']) ?>">
                                                            <?= htmlspecialchars($statusText) ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="view_request.php?id=<?= (int) $r['request_id'] ?>" class="link-muted"
                                                            style="font-size:12.5px;">Review &rarr;</a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                    <div class="empty-state" id="req-no-match" style="display:none;">
                                        <i class="ti ti-search-off"></i>
                                        No requests match your search.
                                    </div>
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
                                <span class="v">Employee</span>
                            </div>
                            <div class="account-row">
                                <span class="k">Assigned program</span>
                                <span class="v"><?= htmlspecialchars($programName ?: 'Program #' . $programId) ?></span>
                            </div>
                            <div class="account-row">
                                <span class="k">Requests in program</span>
                                <span class="v"><?= (int) $stats['total'] ?></span>
                            </div>
                        </div>
                    </section>

                <?php endif; ?>

            </main>
        </div>
    </div>

    <script>
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

            document.querySelectorAll('[data-goto-tab]').forEach(el => {
                el.addEventListener('click', () => activate(el.dataset.gotoTab));
            });

            // Client-side search across the program's full request table.
            const searchInput = document.getElementById('req-search-input');
            if (searchInput) {
                const table = document.getElementById('req-table');
                const noMatch = document.getElementById('req-no-match');
                searchInput.addEventListener('input', () => {
                    const q = searchInput.value.trim().toLowerCase();
                    let visibleCount = 0;
                    table.querySelectorAll('tbody tr').forEach(row => {
                        const hay = row.getAttribute('data-search') || '';
                        const show = q === '' || hay.includes(q);
                        row.style.display = show ? '' : 'none';
                        if (show) visibleCount++;
                    });
                    noMatch.style.display = visibleCount === 0 ? '' : 'none';
                    table.style.display = visibleCount === 0 ? 'none' : '';
                });
            }
        })();
    </script>

</body>

</html>