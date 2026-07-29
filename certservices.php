<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/certificates.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? null;
$firstName  = $_SESSION['first_name'] ?? '';

$dashboardByRole = [
    'student'        => 'student/submit_request.php',
    'alumni'         => 'student/submit_request.php',
    'employee'       => 'employee/requests.php',
    'registrar_head' => 'registrar_head/assign_employee.php',
];
$dashboardUrl = $dashboardByRole[$role] ?? 'index.php';

// The link a "request this certificate" button should point to.
// Logged-in users go straight to their request dashboard; everyone
// else goes to login first (they can still read everything on this
// page without an account).
function requestUrl(bool $isLoggedIn, string $dashboardUrl): string
{
    return $isLoggedIn ? $dashboardUrl : 'auth/login.php';
}

$requestedSlug = isset($_GET['cert']) ? trim((string)$_GET['cert']) : '';
$cert = $requestedSlug !== '' ? certFind($CERT_CATEGORIES, $requestedSlug) : null;

$pageTitle = $cert
    ? $cert['title'] . ' | The Office of Registration and Records Management'
    : 'Registrar Services Catalog | Holy Cross of Davao College';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="public/assets/logo/hcdc-logo.jpg">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="public/assets/css/index.css">
    <link rel="stylesheet" href="public/assets/css/certservices.css">
</head>

<body>

    <header class="main">
        <div class="header-row">
            <div class="brand">
                <img class="crest" src="public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
                <div class="brand-text">
                    <div class="school">Holy Cross of Davao College</div>
                    <div class="office">The Office of Registration and Records Management &middot; CertiChain</div>
                </div>
            </div>
            <nav class="primary">
                <a href="index.php">Home</a>
                <a href="certservices.php">Registrar Services</a>
                <a href="index.php#verify">How to Request</a>
                <a href="index.php#news">Announcements</a>
                <a href="about.php">About</a>
            </nav>
            <div class="header-cta">
                <?php if ($isLoggedIn): ?>
                    <span style="font-size:13.5px;color:var(--ink-soft);margin-right:4px;">
                        Hi, <?= htmlspecialchars($firstName) ?>
                    </span>
                    <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-gold">
                        <i class="ti ti-layout-dashboard"></i>Dashboard
                    </a>
                    <a href="auth/logout.php" class="btn btn-ghost"><i class="ti ti-logout"></i>Logout</a>
                <?php else: ?>
                    <a href="auth/register.php" class="btn btn-ghost"><i class="ti ti-user-plus"></i>Register</a>
                    <a href="auth/login.php" class="btn btn-primary"><i class="ti ti-login"></i>Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <?php if ($cert): ?>

        <!-- ============= DETAIL VIEW: one specific certificate ============= -->
        <section class="page-hero">
            <div class="wrap">
                <div class="breadcrumb">
                    <a href="index.php">Home</a><span>/</span>
                    <a href="certservices.php">Registrar Services</a><span>/</span>
                    <a href="certservices.php#cat-<?= htmlspecialchars($cert['category_key']) ?>"><?= htmlspecialchars($cert['category_label']) ?></a><span>/</span>
                    <span><?= htmlspecialchars($cert['title']) ?></span>
                </div>
                <h1><?= htmlspecialchars($cert['title']) ?></h1>
                <p class="sub"><?= htmlspecialchars($cert['summary']) ?></p>
            </div>
        </section>

        <section>
            <div class="wrap">
                <div class="detail-layout">
                    <div>
                        <div class="detail-card">
                            <h2 class="block-title">What you'll need</h2>
                            <ul class="req-list">
                                <?php foreach ($cert['requirements'] as $req): ?>
                                    <li><span class="chk"><i class="ti ti-square-check"></i></span><span><?= htmlspecialchars($req) ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div class="detail-card">
                            <h2 class="block-title">How to request it</h2>
                            <ol class="step-list">
                                <?php foreach ($cert['steps'] as $step): ?>
                                    <li><span class="t"><?= htmlspecialchars($step) ?></span></li>
                                <?php endforeach; ?>
                            </ol>
                        </div>
                    </div>

                    <div>
                        <div class="sidebar-card">
                            <div class="label">Request summary</div>
                            <div class="sidebar-row"><span class="k">Category</span><span class="v"><?= htmlspecialchars($cert['category_label']) ?></span></div>
                            <div class="sidebar-row"><span class="k">Typical fee</span><span class="v"><?= htmlspecialchars($cert['fee']) ?></span></div>
                            <div class="sidebar-row"><span class="k">Processing time</span><span class="v"><?= htmlspecialchars($cert['processing']) ?></span></div>
                            <a href="<?= htmlspecialchars(requestUrl($isLoggedIn, $dashboardUrl)) ?>" class="btn btn-gold">
                                <i class="ti ti-file-certificate"></i>
                                <?= $isLoggedIn ? 'Request this certificate' : 'Log in to request this' ?>
                            </a>
                        </div>

                        <div class="related-card">
                            <h3>More in <?= htmlspecialchars($cert['category_label']) ?></h3>
                            <ul class="related-list">
                                <?php
                                $siblingItems = $CERT_CATEGORIES[$cert['category_key']]['items'];
                                $count = 0;
                                foreach ($siblingItems as $siblingTitle => $siblingSummary):
                                    if ($siblingTitle === $cert['title']) continue;
                                    if ($count >= 6) break;
                                    $count++;
                                ?>
                                    <li><a href="certservices.php?cert=<?= htmlspecialchars(certSlug($siblingTitle)) ?>"><?= htmlspecialchars($siblingTitle) ?></a></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    <?php elseif ($requestedSlug !== ''): ?>

        <!-- ============= Unknown slug requested ============= -->
        <section class="page-hero">
            <div class="wrap">
                <div class="breadcrumb"><a href="index.php">Home</a><span>/</span><span>Registrar Services</span></div>
                <h1>Registrar Services</h1>
                <p class="sub">Everything the Office of Registration and Records Management issues, in one place.</p>
            </div>
        </section>
        <section>
            <div class="wrap">
                <div class="not-found-card">
                    <h2>We couldn't find that certificate</h2>
                    <p style="color:var(--ink-soft);">"<?= htmlspecialchars($requestedSlug) ?>" doesn't match anything in our catalog. Browse the full list below instead.</p>
                </div>
            </div>
        </section>

    <?php endif; ?>

    <?php if (!$cert): ?>

        <!-- ============= CATALOG VIEW: full directory ============= -->
        <?php if ($requestedSlug === ''): ?>
            <section class="page-hero">
                <div class="wrap">
                    <div class="breadcrumb"><a href="index.php">Home</a><span>/</span><span>Registrar Services</span></div>
                    <h1>Registrar Services</h1>
                    <p class="sub">Every certificate and credential the registrar issues — tap one to see exactly what's required and how the request works before you start.</p>
                </div>
            </section>
        <?php endif; ?>

        <section>
            <div class="wrap">
                <div class="catalog-search">
                    <input type="text" id="cert-search" placeholder="Search certificates, e.g. &quot;transcript&quot; or &quot;diploma&quot;&hellip;" autocomplete="off">
                </div>

                <?php foreach ($CERT_CATEGORIES as $catKey => $cat): ?>
                    <div class="catalog-section" id="cat-<?= htmlspecialchars($catKey) ?>">
                        <div class="catalog-cat-title">
                            <div class="category-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $cat['icon'] ?></svg>
                            </div>
                            <h2><?= htmlspecialchars($cat['label']) ?></h2>
                        </div>
                        <div class="catalog-grid">
                            <?php foreach ($cat['items'] as $title => $summary): ?>
                                <a class="cert-tile" data-search="<?= htmlspecialchars(strtolower($title . ' ' . $summary)) ?>" href="certservices.php?cert=<?= htmlspecialchars(certSlug($title)) ?>">
                                    <div class="name"><?= htmlspecialchars($title) ?></div>
                                    <div class="desc"><?= htmlspecialchars($summary) ?></div>
                                    <div class="cta">View requirements &amp; steps →</div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    <?php endif; ?>

    <footer id="about">
        <div class="wrap">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="school">Holy Cross of Davao College</div>
                    <p>An Archdiocesan Catholic institution in Davao City, serving students since 1951. The Office of
                        Registration and Records Management is proud to bring secure, hash-verified credential
                        services to every student.</p>
                </div>
                <div>
                    <h4>Registrar services</h4>
                    <ul>
                        <li><a href="certservices.php#cat-graduation">Graduation application</a></li>
                        <li><a href="certservices.php#cat-academic-records">Transcript request</a></li>
                        <li><a href="certservices.php#cat-diploma">Diploma request</a></li>
                        <li><a href="certservices.php#cat-clearance">Online clearance</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Helpful links</h4>
                    <ul>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'auth/login.php' ?>">Student portal</a></li>
                        <li><a href="index.php#academics">Academic calendar</a></li>
                        <li><a href="index.php#services">Downloadable forms</a></li>
                        <li><a href="index.php#verify">Credential verification</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Contact</h4>
                    <ul>
                        <li>Sta. Ana Avenue, Davao City</li>
                        <li>Monday to Friday, 8:00 AM to 5:00 PM</li>
                        <li>registrar@hcdc.edu.ph</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <span>&copy; <?= date('Y') ?> Holy Cross of Davao College &middot; The Office of Registration and Records
                    Management</span>
                <div class="footer-social">
                    <a href="#" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
                    <a href="#" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="ti ti-brand-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const searchInput = document.getElementById('cert-search');
        if (searchInput) {
            searchInput.addEventListener('input', () => {
                const q = searchInput.value.trim().toLowerCase();
                document.querySelectorAll('.cert-tile').forEach(tile => {
                    const hay = tile.getAttribute('data-search') || '';
                    tile.style.display = (q === '' || hay.includes(q)) ? '' : 'none';
                });
                document.querySelectorAll('.catalog-section').forEach(section => {
                    const anyVisible = Array.from(section.querySelectorAll('.cert-tile'))
                        .some(t => t.style.display !== 'none');
                    section.style.display = anyVisible ? '' : 'none';
                });
            });
        }
    </script>

</body>
</html>