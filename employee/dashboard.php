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

$dbError = false;
$noProgram = false;

$programId = null;
$programName = null;
$programCode = null;

$stats = [
    'total' => 0,
    'pending' => 0,
    'ready' => 0,
    'released' => 0,
];

$recentRequests = [];
$allRequests = [];

try {
    // 1. Get the employee's assigned college and assigned program from the database record
    $stmt = $pdo->prepare("
        SELECT assigned_college_id, program_id
        FROM employees
        WHERE user_id = :uid
        LIMIT 1
    ");

    $stmt->execute([
        ':uid' => $userId
    ]);
    $employeeRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employeeRow || empty($employeeRow['program_id'])) {
        $noProgram = true;
    } else {
        $collegeId = (int)$employeeRow['assigned_college_id'];
        $assignedProgramId = (int)$employeeRow['program_id'];

        // Get the specific program details that match the employee's assigned program_id
        $stmt = $pdo->prepare("
            SELECT program_id,
                   program_name,
                   degree_code
            FROM programs
            WHERE program_id = :program_id
            LIMIT 1
        ");
        $stmt->execute([
            ':program_id' => $assignedProgramId
        ]);

        $programRow = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($programRow) {
            $programId = $programRow['program_id'];
            $programName = $programRow['program_name'];
            $programCode = $programRow['degree_code'];
        }

        // Count requests strictly filtered by the specific program_id
        $stmt = $pdo->prepare("
            SELECT status,
                   COUNT(*) AS total
            FROM requests
            WHERE program_id = :program_id
            GROUP BY status
        ");

        $stmt->execute([
            ':program_id' => $assignedProgramId
        ]);

        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $status = strtolower($row['status']);
            $count = (int)$row['total'];

            $stats['total'] += $count;

            if (strpos($status, 'pending') !== false) {
                $stats['pending'] += $count;
            } elseif (
                strpos($status, 'ready') !== false ||
                strpos($status, 'approved') !== false
            ) {
                $stats['ready'] += $count;
            } elseif (
                strpos($status, 'released') !== false ||
                strpos($status, 'completed') !== false
            ) {
                $stats['released'] += $count;
            }
        }

        // Load all requests strictly filtered by the employee's assigned program_id
        $stmt = $pdo->prepare("
            SELECT
                r.request_id,
                r.status,
                r.created_at,
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
            WHERE r.program_id = :program_id
            ORDER BY r.created_at DESC
        ");

        $stmt->execute([
            ':program_id' => $assignedProgramId
        ]);

        $allRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $recentRequests = array_slice($allRequests, 0, 8);
    }

} catch (PDOException $e) {
    $dbError = true;
    $noProgram = true;
}

function studentFullName(array $r): string
{
    $name = trim(($r['first_name'] ?? '') . ' ' . ($r['last_name'] ?? ''));
    return $name !== '' ? $name : ($r['student_number'] ?? 'Unknown Student');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | CertiChain &middot; Holy Cross of Davao College</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    
    <!-- Base styles link -->
    <link rel="stylesheet" href="../public/assets/css/index.css">
</head>
<body style="font-family: 'Inter', sans-serif; background-color: #fbf6ee; color: #1e1e1e; margin: 0; padding: 0; min-height: 100vh; display: flex;">

    <div style="display: flex; width: 100%; min-height: 100vh;">

        <!-- ===================== SIDEBAR ===================== -->
        <aside style="width: 260px; background-color: #0f2c59; color: #ffffff; display: flex; flex-direction: column; padding: 24px 16px; flex-shrink: 0;">
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 32px; padding: 0 8px;">
                <img src="../public/assets/logo/hcdc-logo.jpg" alt="HCDC logo" style="width: 32px; height: 32px; border-radius: 4px;">
                <div style="display: flex; flex-direction: column;">
                    <div style="font-size: 11px; font-weight: 500; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">Holy Cross of Davao College</div>
                    <div style="font-size: 14px; font-weight: 700; letter-spacing: -0.3px; line-height: 1.2;">CertiChain &middot; Staff</div>
                </div>
            </div>

            <nav style="display: flex; flex-direction: column; gap: 4px; flex-grow: 1;">
                <button type="button" class="nav-btn active" onclick="switchPanel('overview')" style="display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); border: none; color: #ffffff; padding: 12px 16px; font-size: 14px; font-weight: 500; border-radius: 6px; cursor: pointer; text-align: left; width: 100%; transition: all 0.2s ease;">
                    <i class="ti ti-smart-home" style="font-size: 18px;"></i>Overview
                </button>
                <button type="button" class="nav-btn" onclick="switchPanel('requests')" style="display: flex; align-items: center; gap: 12px; background: none; border: none; color: #ffffff; padding: 12px 16px; font-size: 14px; font-weight: 500; border-radius: 6px; cursor: pointer; text-align: left; width: 100%; opacity: 0.65; transition: all 0.2s ease;">
                    <i class="ti ti-file-description" style="font-size: 18px;"></i>My Tasks
                </button>
                <button type="button" class="nav-btn" onclick="switchPanel('account')" style="display: flex; align-items: center; gap: 12px; background: none; border: none; color: #ffffff; padding: 12px 16px; font-size: 14px; font-weight: 500; border-radius: 6px; cursor: pointer; text-align: left; width: 100%; opacity: 0.65; transition: all 0.2s ease;">
                    <i class="ti ti-user-circle" style="font-size: 18px;"></i>Account
                </button>

                <div style="font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin: 28px 8px 8px 8px; opacity: 0.4;">Assigned Scope</div>
                <div style="padding: 0 8px; font-size: 12px; opacity: 0.7; line-height: 1.5; font-weight: 500;">
                    <?= htmlspecialchars($programName ?: 'Workspace') ?>
                </div>
            </nav>

            <div style="margin-top: auto; padding-top: 16px;">
                <a href="../auth/logout.php" style="display: flex; align-items: center; justify-content: center; gap: 8px; background-color: rgba(255, 255, 255, 0.08); color: #ffffff; border: none; padding: 12px; width: 100%; border-radius: 6px; font-weight: 600; font-size: 13px; cursor: pointer; text-decoration: none; transition: background 0.2s ease;">
                    <i class="ti ti-logout"></i>Logout
                </a>
            </div>
        </aside>

        <!-- ===================== MAIN CONTENT CANVAS ===================== -->
        <div style="flex-grow: 1; display: flex; flex-direction: column; padding: 40px 48px; overflow-y: auto;">

            <header style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px;">
                <div>
                    <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; margin-bottom: 4px;">Welcome back</div>
                    <h1 style="font-size: 24px; font-weight: 700; letter-spacing: -0.5px; color: #1e1e1e; margin: 0;"><?= htmlspecialchars($firstName ?: 'Employee') ?></h1>
                </div>
                <div>
                    <span style="background-color: #ffffff; border: 1px solid #e2e8f0; padding: 6px 12px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Staff Panel</span>
                </div>
            </header>

            <main style="display: flex; flex-direction: column; gap: 28px; width: 100%; max-width: 1000px;">

                <?php if ($noProgram): ?>
                    <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px 32px;">
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; text-align: center; color: #64748b; font-size: 14px; gap: 12px;">
                            <i class="ti ti-shield-alert" style="font-size: 32px; opacity: 0.5;"></i>
                            Account program tracking parameters missing. Please consult the Registrar Head.
                        </div>
                    </div>
                <?php else: ?>

                    <!-- ---------- OVERVIEW WORKSPACE PANEL ---------- -->
                    <section class="dash-panel" id="panel-overview">
                        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px 32px; margin-bottom: 28px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h2 style="font-size: 15px; font-weight: 700; color: #1e1e1e; margin: 0;">Recent requests</h2>
                                <button type="button" onclick="switchPanel('requests')" style="font-size: 13px; font-weight: 600; color: #0f2c59; background: none; border: none; cursor: pointer; text-decoration: none;">View all</button>
                            </div>

                            <?php if ($dbError): ?>
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; text-align: center; color: #64748b; font-size: 14px; gap: 12px;">
                                    <i class="ti ti-alert-triangle" style="font-size: 32px; opacity: 0.5;"></i>
                                    Failed to establish secure processing connection pipeline context.
                                </div>
                            <?php elseif (!$recentRequests): ?>
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; text-align: center; color: #64748b; font-size: 14px; gap: 12px;">
                                    <i class="ti ti-checkbox" style="font-size: 32px; opacity: 0.5;"></i>
                                    No certificate requests have been filed by students in this program yet.
                                </div>
                            <?php else: ?>
                                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Certificate</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Program</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Date Requested</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Status</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentRequests as $r): ?>
                                            <tr>
                                                <td style="padding: 16px 0; font-size: 13px; color: #1e1e1e; border-bottom: 1px solid #f8fafc; font-weight: 500;"><?= htmlspecialchars($r['cert_name'] ?? 'Certificate') ?></td>
                                                <td style="padding: 16px 0; font-size: 13px; color: #475569; border-bottom: 1px solid #f8fafc;"><?= htmlspecialchars($programName) ?></td>
                                                <td style="padding: 16px 0; font-size: 13px; color: #64748b; border-bottom: 1px solid #f8fafc;"><?= htmlspecialchars(date('M j, Y', strtotime((string)$r['created_at']))) ?></td>
                                                <td style="padding: 16px 0; font-size: 13px; border-bottom: 1px solid #f8fafc;">
                                                    <span style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; background-color: #fef3c7; color: #d97706; text-transform: capitalize;">
                                                        <?= htmlspecialchars(str_replace('_', ' ', (string)$r['status'])) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 16px 0; font-size: 13px; border-bottom: 1px solid #f8fafc; text-align: right;">
                                                    <a href="view_request.php?id=<?= (int)$r['request_id'] ?>" style="color: #1e1e1e; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                                        View <i class="ti ti-arrow-narrow-right"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>

                        <!-- Quick Actions Grid layout -->
                        <div>
                            <h2 style="font-size: 11px; font-weight: 700; margin-bottom: 12px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Quick actions</h2>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <button type="button" onclick="switchPanel('requests')" style="display: flex; align-items: center; gap: 12px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 20px; font-size: 13px; font-weight: 500; color: #1e1e1e; text-decoration: none; cursor: pointer; text-align: left; font-family: inherit;">
                                    <i class="ti ti-list-search" style="font-size: 16px; color: #64748b;"></i> Open full validation queue registry
                                </button>
                                <button type="button" onclick="switchPanel('account')" style="display: flex; align-items: center; gap: 12px; width: 100%; background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 16px 20px; font-size: 13px; font-weight: 500; color: #1e1e1e; text-decoration: none; cursor: pointer; text-align: left; font-family: inherit;">
                                    <i class="ti ti-id-badge" style="font-size: 16px; color: #64748b;"></i> Review tracking permission assignments
                                </button>
                            </div>
                        </div>
                    </section>

                    <!-- ---------- ALL REQUESTS PANELS ---------- -->
                    <section class="dash-panel" id="panel-requests" style="display: none;">
                        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px 32px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h2 style="font-size: 15px; font-weight: 700; color: #1e1e1e; margin: 0;">All program requests registry</h2>
                                <span style="background-color: #ffffff; border: 1px solid #e2e8f0; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; color: #64748b;"><?= (int)$stats['total'] ?> total</span>
                            </div>

                            <?php if (!$allRequests): ?>
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 40px 0; text-align: center; color: #64748b; font-size: 14px; gap: 12px;">
                                    <i class="ti ti-folder-off" style="font-size: 32px; opacity: 0.5;"></i>
                                    No records present inside this program database lookup frame.
                                </div>
                            <?php else: ?>
                                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                                    <thead>
                                        <tr>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Certificate</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Student</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Date Filed</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">Status</th>
                                            <th style="font-size: 11px; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($allRequests as $r): ?>
                                            <tr>
                                                <td style="padding: 16px 0; font-size: 13px; color: #1e1e1e; border-bottom: 1px solid #f8fafc; font-weight: 500;"><?= htmlspecialchars($r['cert_name'] ?? 'Certificate') ?></td>
                                                <td style="padding: 16px 0; font-size: 13px; color: #1e1e1e; border-bottom: 1px solid #f8fafc;"><?= htmlspecialchars(studentFullName($r)) ?></td>
                                                <td style="padding: 16px 0; font-size: 13px; color: #64748b; border-bottom: 1px solid #f8fafc;"><?= htmlspecialchars(date('M j, Y', strtotime((string)$r['created_at']))) ?></td>
                                                <td style="padding: 16px 0; font-size: 13px; border-bottom: 1px solid #f8fafc;">
                                                    <span style="display: inline-flex; align-items: center; padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; background-color: #fef3c7; color: #d97706; text-transform: capitalize;">
                                                        <?= htmlspecialchars(str_replace('_', ' ', (string)$r['status'])) ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 16px 0; font-size: 13px; border-bottom: 1px solid #f8fafc; text-align: right;">
                                                    <a href="view_request.php?id=<?= (int)$r['request_id'] ?>" style="color: #1e1e1e; text-decoration: none; font-weight: 500; display: inline-flex; align-items: center; gap: 4px;">
                                                        View <i class="ti ti-arrow-narrow-right"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- ---------- ACCOUNT MANAGEMENT PANEL ---------- -->
                    <section class="dash-panel" id="panel-account" style="display: none;">
                        <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 24px 32px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                                <h2 style="font-size: 15px; font-weight: 700; color: #1e1e1e; margin: 0;">Account Privileges Configuration</h2>
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 8px;">
                                <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                                    <span style="color: #64748b; font-size: 14px;">Staff Officer Name</span>
                                    <span style="font-weight: 600; font-size: 14px; color: #1e1e1e;"><?= htmlspecialchars($firstName) ?></span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                                    <span style="color: #64748b; font-size: 14px;">System Access Authority</span>
                                    <span style="font-weight: 600; font-size: 14px; text-transform: uppercase; color: #0f2c59;">Employee Officer</span>
                                </div>
                                <div style="display: flex; justify-content: space-between; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
                                    <span style="color: #64748b; font-size: 14px;">Assigned Track Program</span>
                                    <span style="font-weight: 600; font-size: 14px; color: #1e1e1e;"><?= htmlspecialchars($programName) ?> (<?= htmlspecialchars($programCode) ?>)</span>
                                </div>
                            </div>
                        </div>
                    </section>

                <?php endif; ?>

            </main>
        </div>
    </div>

    <!-- Client side tab toggle routing -->
    <script>
        function switchPanel(panelName) {
            // Manage dashboard nav highlight toggling
            const buttons = document.querySelectorAll('.nav-btn');
            buttons.forEach(btn => {
                btn.style.background = 'none';
                btn.style.opacity = '0.65';
                btn.classList.remove('active');
            });

            // Find matching trigger source
            const activeTrigger = Array.from(buttons).find(btn => btn.getAttribute('onclick').includes(panelName));
            if (activeTrigger) {
                activeTrigger.style.background = 'rgba(255, 255, 255, 0.1)';
                activeTrigger.style.opacity = '1';
                activeTrigger.classList.add('active');
            }

            // Handle dashboard wrapper panels toggles
            const panels = document.querySelectorAll('.dash-panel');
            panels.forEach(p => p.style.display = 'none');
            
            const activePanel = document.getElementById('panel-' + panelName);
            if (activePanel) {
                activePanel.style.display = 'block';
            }
        }
    </script>
</body>
</html>
