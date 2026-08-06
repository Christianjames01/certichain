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

$requestCode = isset($_GET['code']) ? trim((string) $_GET['code']) : '';

if ($requestCode === '') {
    header('Location: dashboard.php#requests');
    exit;
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
        if (in_array($status, ['pending_review', 'pending', 'processing', 'under_review'], true)) {
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

// ---- Load the request itself ----
// NOTE: requester_user_id = :uid in the WHERE clause is what stops a student
// from viewing someone else's request just by changing ?id= in the URL.
$request  = null;
$dbError  = false;
$notFound = false;

try {
   $stmt = $pdo->prepare(
        "SELECT r.request_id, r.request_code, r.status, r.remarks,
                r.created_at, r.updated_at, r.assigned_employee_id,
                dt.document_name AS cert_name,
                c.college_name, c.college_code,
                p.program_name
         FROM requests r
         LEFT JOIN document_types dt ON dt.document_type_id = r.document_type_id
         LEFT JOIN colleges c        ON c.college_id = r.college_id
         LEFT JOIN programs p        ON p.program_id = r.program_id
         WHERE r.request_code = :code
           AND r.requester_user_id = :uid
         LIMIT 1"
    );
    $stmt->execute([':code' => $requestCode, ':uid' => $userId]);
    $request = $stmt->fetch();

    if (!$request) {
        $notFound = true;
    }
} catch (\PDOException $e) {
    $dbError = true;
}

// Optional: who it's assigned to. Kept in its own try/catch and its own
// query so that if the "users" table/column names here don't match your
// schema, it just fails quietly instead of breaking the whole page.
$assignedName = null;
if ($request && !empty($request['assigned_employee_id'])) {
    try {
        $empStmt = $pdo->prepare(
            'SELECT first_name, last_name FROM users WHERE user_id = :eid LIMIT 1'
        );
        $empStmt->execute([':eid' => (int) $request['assigned_employee_id']]);
        $emp = $empStmt->fetch();
        if ($emp) {
            $assignedName = trim(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? ''));
        }
    } catch (\PDOException $e) {
        // Ignore — falls back to "Staff assigned" with no name below.
    }
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

// Which pipeline step is the request currently on, for the timeline widget.
// Rejected/cancelled requests get their own terminal state instead of
// pretending they reached "Released".
function timelineStep(string $status): string
{
    $s = strtolower($status);
    if (in_array($s, ['released', 'completed', 'claimed'], true)) return 'released';
    if (in_array($s, ['ready', 'ready_for_pickup', 'approved'], true)) return 'ready';
    if (in_array($s, ['rejected', 'declined', 'cancelled'], true)) return 'rejected';
    return 'submitted'; // pending_review, pending, processing, under_review
}

$currentStep = $request ? timelineStep((string) $request['status']) : 'submitted';
$isRejected  = $currentStep === 'rejected';

// Step order used to light up the timeline progressively.
$stepOrder = ['submitted', 'ready', 'released'];
$currentIndex = $isRejected ? 0 : array_search($currentStep, $stepOrder, true);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Details | CertiChain &middot; Holy Cross of Davao College</title>
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
    <link rel="stylesheet" href="../public/assets/css/view_request.css">
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
                    <div class="topbar-eyebrow">Dashboard / My Requests / Details</div>
                    <h1>Request Details</h1>
                </div>
                <div class="topbar-right">
                    <span class="role-chip"><?= htmlspecialchars(ucfirst($role)) ?></span>
                </div>
            </header>

            <main class="dash-content dash-content-wide">

                <?php if ($dbError): ?>

                    <div class="dash-card">
                        <div class="empty-state">
                            <i class="ti ti-alert-triangle"></i>
                            Couldn't load this request right now. Please try again later.
                        </div>
                    </div>

                <?php elseif ($notFound): ?>

                    <div class="dash-card">
                        <div class="empty-state">
                            <i class="ti ti-file-off"></i>
                            We couldn't find that request, or it doesn't belong to your account.<br>
                            <a href="dashboard.php#requests" class="link-muted">Back to My Requests &rarr;</a>
                        </div>
                    </div>

                <?php else: ?>

                    <a href="dashboard.php#requests" class="back-link">
                        <i class="ti ti-arrow-left"></i>Back to My Requests
                    </a>

                    <div class="view-req-head">
                        <div>
                            <div class="view-req-code"><?= htmlspecialchars($request['request_code'] ?? ('#' . $request['request_id'])) ?></div>
                            <h2 class="view-req-title"><?= htmlspecialchars($request['cert_name'] ?? 'Certificate Request') ?></h2>
                        </div>
                        <span class="badge <?= statusBadgeClass((string) $request['status']) ?>">
                            <?= htmlspecialchars(str_replace('_', ' ', (string) $request['status'])) ?>
                        </span>
                    </div>

                    <div class="req-form-layout">

                        <div>
                            <!-- Timeline -->
                            <div class="dash-card">
                                <div class="dash-card-head">
                                    <h2>Status timeline</h2>
                                </div>

                                <?php if ($isRejected): ?>
                                    <div class="timeline timeline-rejected">
                                        <div class="timeline-step done">
                                            <span class="timeline-dot"><i class="ti ti-check"></i></span>
                                            <span class="timeline-label">Submitted</span>
                                        </div>
                                        <div class="timeline-bar done rejected"></div>
                                        <div class="timeline-step done rejected">
                                            <span class="timeline-dot"><i class="ti ti-x"></i></span>
                                            <span class="timeline-label">Rejected</span>
                                        </div>
                                    </div>
                                    <p class="note" style="margin-top:14px;">
                                        This request was not approved. See remarks below, or contact the office
                                        that handles your program for details.
                                    </p>
                                <?php else: ?>
                                    <div class="timeline">
                                        <?php foreach ($stepOrder as $i => $step): ?>
                                            <?php
                                                $labels = ['submitted' => 'Submitted', 'ready' => 'Ready', 'released' => 'Released'];
                                                $isDone = $i <= $currentIndex;
                                            ?>
                                            <div class="timeline-step <?= $isDone ? 'done' : '' ?>">
                                                <span class="timeline-dot">
                                                    <?= $isDone ? '<i class="ti ti-check"></i>' : ($i + 1) ?>
                                                </span>
                                                <span class="timeline-label"><?= $labels[$step] ?></span>
                                            </div>
                                            <?php if ($i < count($stepOrder) - 1): ?>
                                                <div class="timeline-bar <?= $i < $currentIndex ? 'done' : '' ?>"></div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Remarks -->
                            <div class="dash-card">
                                <div class="dash-card-head">
                                    <h2>Remarks</h2>
                                </div>
                                <?php if (!empty($request['remarks'])): ?>
                                    <p class="remarks-text"><?= nl2br(htmlspecialchars($request['remarks'])) ?></p>
                                <?php else: ?>
                                    <p class="note">No remarks were added with this request.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                            <div class="dash-card routing-card">
                                <div class="label">Handled by</div>
                                <div class="college-name"><?= htmlspecialchars($request['college_name'] ?? 'Not yet assigned') ?></div>
                                <?php if (!empty($request['college_code'])): ?>
                                    <div class="college-code"><?= htmlspecialchars($request['college_code']) ?></div>
                                <?php endif; ?>
                            </div>

                            <div class="dash-card">
                                <div class="dash-card-head">
                                    <h2>Details</h2>
                                </div>
                                <div class="account-row">
                                    <span class="k">Request code</span>
                                    <span class="v"><?= htmlspecialchars($request['request_code'] ?? ('#' . $request['request_id'])) ?></span>
                                </div>
                                <div class="account-row">
                                    <span class="k">Program</span>
                                    <span class="v"><?= htmlspecialchars($request['program_name'] ?? 'Not specified') ?></span>
                                </div>
                                <div class="account-row">
                                    <span class="k">Date filed</span>
                                    <span class="v"><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $request['created_at']))) ?></span>
                                </div>
                                <div class="account-row">
                                    <span class="k">Last updated</span>
                                    <span class="v"><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $request['updated_at']))) ?></span>
                                </div>
                                <div class="account-row">
                                    <span class="k">Assigned to</span>
                                    <span class="v"><?= htmlspecialchars($assignedName ?: ($request['assigned_employee_id'] ? 'Staff assigned' : 'Unassigned')) ?></span>
                                </div>
                            </div>

                            <div class="dash-card">
                                <div class="dash-card-head">
                                    <h2>Need something else?</h2>
                                </div>
                                <div class="quick-actions">
                                    <a href="submit_request.php" class="quick-action">
                                        <i class="ti ti-file-certificate"></i>Submit a new request
                                    </a>
                                    <a href="dashboard.php#requests" class="quick-action">
                                        <i class="ti ti-history"></i>View request history
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>

                <?php endif; ?>

            </main>
        </div>
    </div>

</body>

</html>