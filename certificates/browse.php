<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['student', 'alumni']);

$userId    = (int) $_SESSION['user_id'];
$role      = $_SESSION['role'];
$firstName = $_SESSION['first_name'] ?? '';

// ---- Sidebar pipeline stats (same shape as dashboard.php / submit_request.php) ----
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
         WHERE user_id = :uid
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

// ---- Fetch all active document types, grouped by category ----
// Assumes: categories(category_id, category_name) joined via document_types.category_id
$catalogError = false;
$categories   = []; // category_id => ['name' => ..., 'items' => [...]]

try {
    $stmt = $pdo->query(
        "SELECT dt.document_type_id, dt.document_name, dt.description,
                c.category_id, c.category_name
         FROM document_types dt
         LEFT JOIN categories c ON c.category_id = dt.category_id
         WHERE dt.is_active = 1
         ORDER BY c.category_name ASC, dt.document_name ASC"
    );
    foreach ($stmt->fetchAll() as $row) {
        $catId   = $row['category_id'] !== null ? (int) $row['category_id'] : 0;
        $catName = $row['category_name'] ?? 'Other';

        if (!isset($categories[$catId])) {
            $categories[$catId] = [
                'name'  => $catName,
                'items' => [],
            ];
        }
        $categories[$catId]['items'][] = [
            'id'          => (int) $row['document_type_id'],
            'name'        => $row['document_name'],
            'description' => $row['description'] ?? '',
        ];
    }
} catch (\PDOException $e) {
    $catalogError = true;
}

function categoryIcon(string $categoryName): string
{
    $icons = [
        'Enrollment & Student Status' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9L12 5 2 9l10 4 10-4z" /><path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5" /><path d="M22 9v6" /></svg>',
        'Academic Records' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M9 13h6M9 17h6M9 9h1" /></svg>',
        'Graduation' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="6" /><path d="M9.5 8.5l1.8 1.8L15 6.5" /><path d="M8 14.5L6 22l6-3 6 3-2-7.5" /></svg>',
        'Diploma' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" /></svg>',
        'Transfer & Withdrawal' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H5a2 2 0 0 0-2 2v4m18 0V5a2 2 0 0 0-2-2h-4m0 18h4a2 2 0 0 0 2-2v-4M3 15v4a2 2 0 0 0 2 2h4" /></svg>',
        'Authentication & Verification' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 19h2" /></svg>',
        'Curriculum & Course' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></svg>',
        'Special Purpose' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M8 12l3 3 5-6" /></svg>',
        'Clearance & Payments' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2" /><path d="M3 9h18M8 15h4" /></svg>',
        'Printouts & Simple Requests' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V3h12v6M6 18h12v3H6z" /><rect x="4" y="9" width="16" height="8" rx="1" /></svg>',
        'Maritime (BSMT) Program Documents' =>
            '<svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 18l1.5-6h15L21 18" /><path d="M12 3v9M8 6h8" /><path d="M2 21c2 1.5 4 1.5 6 0s4-1.5 6 0 4 1.5 6 0" /></svg>',
    ];

    return $icons[$categoryName]
        ?? '<i class="ti ti-folder"></i>';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Certificates | CertiChain &middot; Holy Cross of Davao College</title>
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
    <link rel="stylesheet" href="../public/assets/css/browse.css">
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
                <a href="../student/dashboard.php">
                    <i class="ti ti-layout-dashboard"></i>Overview
                </a>
                <a href="../student/dashboard.php#requests">
                    <i class="ti ti-file-text"></i>My Requests
                    <?php if ($stats['total'] > 0): ?><span class="side-nav-count"><?= (int) $stats['total'] ?></span><?php endif; ?>
                </a>
                <a href="../student/dashboard.php#account">
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
                    <div class="topbar-eyebrow">Dashboard / Browse certificates</div>
                    <h1>Browse Certificates</h1>
                </div>
                <div class="topbar-right">
                    <span class="role-chip"><?= htmlspecialchars(ucfirst($role)) ?></span>
                </div>
            </header>

            <main class="dash-content dash-content-wide">

                <p class="page-lede">
                    Every certificate you can request through CertiChain. Find what you need, then head to
                    <a href="submit_request.php" class="link-muted">New Request</a> to submit it.
                </p>

                <div class="catalog-search">
                    <input type="text" id="catalogSearchInput" placeholder='Search certificates, e.g. "transcript" or "diploma"…'>
                </div>

                <?php if ($catalogError): ?>
                    <div class="dash-card">
                        <div class="empty-state">
                            <i class="ti ti-alert-triangle"></i>
                            Couldn't load the certificate catalog right now. Please try again later.
                        </div>
                    </div>
                <?php elseif (!$categories): ?>
                    <div class="dash-card">
                        <div class="empty-state">
                            <i class="ti ti-file-off"></i>
                            No certificate types are available right now.
                        </div>
                    </div>
                <?php else: ?>
                    <div id="catalogRoot">
                        <?php foreach ($categories as $cat): ?>
                            <section class="catalog-section" data-category>
                                <div class="catalog-cat-title">
                                    <div class="category-icon">
                                        <?= categoryIcon($cat['name']) ?>
                                    </div>
                                    <h2><?= htmlspecialchars($cat['name']) ?></h2>
                                </div>
                                <div class="catalog-grid">
<?php foreach ($cat['items'] as $item): ?>
                                        <a href="/certichain/student/submit_request.php?document_type_id=<?= (int) $item['id'] ?>"
                                            class="cert-tile" data-cert-name="<?= htmlspecialchars(strtolower($item['name'])) ?>">
                                            <div class="name"><?= htmlspecialchars($item['name']) ?></div>
                                            <?php if ($item['description']): ?>
                                                <div class="desc"><?= htmlspecialchars($item['description']) ?></div>
                                            <?php endif; ?>
                                            <div class="cta">Request this &rarr;</div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </section>
                        <?php endforeach; ?>
                        <div class="empty-state" id="catalogNoResults" style="display:none;">
                            <i class="ti ti-search-off"></i>
                            No certificates match your search.
                        </div>
                    </div>
                <?php endif; ?>

            </main>
        </div>
    </div>

  <script>
        // Live filter — shows only tiles matching the query, hides empty categories entirely.
        (function () {
            const input = document.getElementById('catalogSearchInput');
            if (!input) return;

            const sections  = document.querySelectorAll('#catalogRoot [data-category]');
            const noResults = document.getElementById('catalogNoResults');

            function applyFilter() {
                const q = input.value.trim().toLowerCase();
                let anyVisible = false;

                sections.forEach(section => {
                    let sectionHasMatch = false;

                    section.querySelectorAll('.cert-tile').forEach(tile => {
                        const match = q === '' || tile.dataset.certName.includes(q);
                        tile.style.display = match ? '' : 'none';
                        if (match) sectionHasMatch = true;
                    });

                    section.style.display = sectionHasMatch ? '' : 'none';
                    if (sectionHasMatch) anyVisible = true;
                });

                noResults.style.display = anyVisible ? 'none' : 'block';
            }

            input.addEventListener('input', applyFilter);
            applyFilter();
        })();
    </script>

</body>

</html>