<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';

$isLoggedIn = !empty($_SESSION['user_id']);
$role       = $_SESSION['role'] ?? null;
$firstName  = $_SESSION['first_name'] ?? '';

// Where "Go to dashboard" should send an already-logged-in user.
$dashboardByRole = [
    'student'        => 'student/submit_request.php',
    'alumni'         => 'student/submit_request.php',
    'employee'       => 'employee/requests.php',
    'registrar_head' => 'registrar_head/assign_employee.php',
];
$dashboardUrl = $dashboardByRole[$role] ?? 'index.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About HCDC | Holy Cross of Davao College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="public/assets/logo/hcdc-logo.jpg">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="public/assets/css/index.css">
    <style>
        .about-hero {
            background: var(--hcdc-navy);
            color: var(--white);
            padding: 56px 0 52px;
        }

        .about-hero h1 {
            font-family: var(--font-display);
            font-size: 36px;
            font-weight: 600;
            margin: 0 0 14px;
        }

        .about-hero p {
            color: rgba(255, 255, 255, 0.78);
            font-size: 16px;
            max-width: 680px;
            margin: 0;
        }

        .about-body {
            padding: 64px 0;
        }

        .about-grid {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 48px;
            align-items: start;
        }

        .about-block {
            margin-bottom: 44px;
        }

        .about-block:last-child {
            margin-bottom: 0;
        }

        .about-block h2 {
            font-family: var(--font-display);
            font-size: 24px;
            font-weight: 600;
            color: var(--hcdc-navy);
            margin: 0 0 14px;
        }

        .about-block p {
            font-size: 15px;
            color: var(--ink-soft);
            margin: 0 0 12px;
        }

        .about-block p:last-child {
            margin-bottom: 0;
        }

        .mv-cards {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 8px;
}


        .mv-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 22px 22px 20px;
        }

        .mv-card .mv-icon {
            width: 36px;
            height: 36px;
            border-radius: 9px;
            background: var(--parchment-dim);
            color: var(--hcdc-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
        }

        .mv-card h3 {
            font-size: 15.5px;
            font-weight: 600;
            color: var(--ink);
            margin: 0 0 8px;
        }

        .mv-card p {
            font-size: 13.5px;
            color: var(--ink-soft);
            margin: 0;
            line-height: 1.6;
        }
        .goal-card {
    background: var(--hcdc-navy);
    color: var(--white);
    border-radius: 12px;
    padding: 26px 28px;
    margin-top: 16px;
    display: flex;
    gap: 18px;
    align-items: flex-start;
}

.goal-card .mv-icon {
    background: rgba(255, 255, 255, 0.12);
    color: var(--hcdc-gold-light);
}

.goal-card h3 {
    font-size: 15.5px;
    font-weight: 600;
    color: var(--white);
    margin: 0 0 8px;
}

.goal-card p {
    font-size: 13.5px;
    color: rgba(255, 255, 255, 0.82);
    margin: 0;
    line-height: 1.6;
}

@media (max-width: 560px) {
    .goal-card {
        flex-direction: column;
    }
}

        .timeline {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .timeline li {
            display: flex;
            gap: 16px;
            padding: 0 0 22px;
            position: relative;
        }

        .timeline li:not(:last-child)::before {
            content: "";
            position: absolute;
            left: 27px;
            top: 34px;
            bottom: -4px;
            width: 2px;
            background: var(--line);
        }

        .timeline .yr {
            flex-shrink: 0;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--parchment-dim);
            border: 1px solid var(--line);
            color: var(--hcdc-navy);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: var(--font-mono);
            font-size: 11.5px;
            font-weight: 700;
            text-align: center;
            line-height: 1.2;
        }

        .timeline .tl-body h4 {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--ink);
            margin: 6px 0 4px;
        }

        .timeline .tl-body p {
            font-size: 13.5px;
            color: var(--ink-soft);
            margin: 0;
            line-height: 1.6;
        }

        .campus-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-top: 8px;
        }

        .campus-card {
            background: var(--white);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 16px 18px;
            display: flex;
            gap: 14px;
            align-items: flex-start;
        }

        .campus-card .c-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--parchment-dim);
            color: var(--hcdc-red);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .campus-card .c-name {
            font-size: 14.5px;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 3px;
        }

        .campus-card .c-desc {
            font-size: 13px;
            color: var(--ink-soft);
        }

        .school-list {
            list-style: none;
            margin: 8px 0 0;
            padding: 0;
        }

        .school-list li {
            border-top: 1px solid var(--line);
            padding: 12px 0;
            font-size: 14px;
            color: var(--ink);
        }

        .school-list li:first-child {
            border-top: none;
        }

        .about-sidebar-card {
            background: var(--hcdc-navy);
            color: var(--white);
            border-radius: 12px;
            padding: 26px;
            margin-bottom: 20px;
        }

        .about-sidebar-card .label {
            font-family: var(--font-mono);
            font-size: 11px;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--hcdc-gold-light);
            margin-bottom: 12px;
        }

        .about-sidebar-row {
            display: flex;
            gap: 10px;
            padding: 12px 0;
            border-top: 1px solid rgba(255, 255, 255, 0.14);
            font-size: 13.5px;
        }

        .about-sidebar-row:first-of-type {
            border-top: none;
        }

        .about-sidebar-row i {
            color: var(--hcdc-gold-light);
            flex-shrink: 0;
            margin-top: 2px;
        }

        @media (max-width: 900px) {
            .about-grid {
                grid-template-columns: 1fr;
            }

            .mv-cards {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>

    <div class="utility-social">
        <a href="https://www.facebook.com/hcdcofficial" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
        <a href="https://www.instagram.com/hcdcofficial/" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
        <a href="#" aria-label="YouTube"><i class="ti ti-brand-youtube"></i></a>
    </div>

    <header class="main">
        <div class="header-row">
            <div class="brand">
                <img class="crest" src="public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
                <div class="brand-text">
                    <div class="school">Holy Cross of Davao College</div>
                    <div class="office">The Office of Registration and Records Management</div>
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
                    <span style="font-size:13.5px;color:var(--hcdc-ink-soft);margin-right:4px;">
                        Hi, <?= htmlspecialchars($firstName) ?>
                    </span>
                    <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-gold">
                        <i class="ti ti-layout-dashboard"></i>Dashboard
                    </a>
                    <a href="auth/logout.php" class="btn btn-ghost"><i class="ti ti-logout"></i>Logout</a>
                <?php else: ?>
                    
                    <a href="auth/login.php" class="btn btn-primary"><i class="ti ti-login"></i>Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="about-hero">
        <div class="wrap">
            <div class="breadcrumb">
                <a href="index.php">Home</a>
                <span>/</span>
                <span>About</span>
            </div>
            <h1>About Holy Cross of Davao College</h1>
            <p>An Archdiocesan Catholic institution in Davao City, forming students in faith, academic excellence, and service since 1951.</p>
        </div>
    </section>

    <section class="about-body">
        <div class="wrap">
            <div class="about-grid">
                <div>
                    <div class="about-block">
                        <h2>Who We Are</h2>
                        <p>Holy Cross of Davao College (HCDC) is a private Catholic educational institution run by the Archdiocese of Davao. Rooted in the Catholic faith, the school forms students in academics, character, and community service across its basic education and higher education programs.</p>
                        <p>As an archdiocesan institution, HCDC is committed to providing accessible, high-quality Catholic education, with particular attention to the needs of the less fortunate, while nurturing a culture of academic excellence and holiness.</p>
                    </div>

                    <div class="about-block">
                        <h2>Our Vision, Mission &amp; Goal</h2>
                        <div class="mv-cards">
                            <div class="mv-card">
                                <div class="mv-icon">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 4v5c0 4.5-3 8-7 9-4-1-7-4.5-7-9V7z" /></svg>
                                </div>
                                <h3>Vision</h3>
                                <p>The Holy Cross of Davao College envisions a fully vibrant community of believers and Christ-centered evangelizers, educated in the faith, animated by the passion for truth, and engaged in building a more humane world.</p>
                            </div>
                            <div class="mv-card">
                                <div class="mv-icon">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20" /><circle cx="12" cy="12" r="9" /></svg>
                                </div>
                                <h3>Mission</h3>
                                <p>As members of this Filipino archdiocesan educational institution, we commit ourselves to cultivate high quality Catholic education for all, attentive to the needs of the less fortunate; nurture a culture of excellence and holiness; and provide a human and Christian learning environment for the integral liberating formation of persons who will become effective agents of social transformation.</p>
                            </div>
                            </div>

                        <div class="goal-card">
                            <div class="mv-icon">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10M18 20V4M6 20v-4" /></svg>
                            </div>
                            <div>
                                <h3>Goal</h3>
                                <p>From faith to truth, we uphold the values of servant leadership, dialogue, justice, peace, and integrity of creation, with wisdom as the underlying principle.</p>
                                <p>Ex Fide Ad Veritatem — From Faith to Truth.
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="about-block">
                        <h2>Our History</h2>
                        <ul class="timeline">
                            <li>
                                <div class="yr">1951</div>
                                <div class="tl-body">
                                    <h4>Founded by the RVM Sisters</h4>
                                    <p>The Religious of the Virgin Mary Sisters founded the school as an annex of the Immaculate Conception College, laying the foundation of Catholic education in Davao.</p>
                                </div>
                            </li>
                            <li>
                                <div class="yr">1956</div>
                                <div class="tl-body">
                                    <h4>Turned over to the PME Fathers</h4>
                                    <p>The Foreign Mission Society of Quebec (PME Fathers) took over administration. The school became an exclusive school for boys under the name Holy Cross Academy of Davao.</p>
                                </div>
                            </li>
                            <li>
                                <div class="yr">1966</div>
                                <div class="tl-body">
                                    <h4>Became Holy Cross of Davao College</h4>
                                    <p>The school began offering college-level courses and was officially renamed Holy Cross of Davao College.</p>
                                </div>
                            </li>
                            <li>
                                <div class="yr">1978</div>
                                <div class="tl-body">
                                    <h4>Entrusted to the Archdiocese of Davao</h4>
                                    <p>Ownership was fully transferred to the Archdiocese of Davao, which continues to guide HCDC today as an archdiocesan educational institution.</p>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="about-block">
                        <h2>Schools &amp; Colleges</h2>
                        <ul class="school-list">
                            <li>School of Humanities, Social Sciences and Communication (HUSOCOM)</li>
                            <li>School of Business and Management Education (SBME)</li>
                            <li>School of Teacher Education (STE)</li>
                            <li>College of Engineering and Technology (CET)</li>
                            <li>College of Criminal Justice Education (CCJE)</li>
                            <li>College of Maritime Education (COME)</li>
                            <li>Graduate School</li>
                        </ul>
                    </div>
                </div>

                <div>
                    <div class="about-sidebar-card">
                        <div class="label">At a Glance</div>
                        <div class="about-sidebar-row">
                            <i class="ti ti-calendar"></i>
                            <span>Founded in 1951, archdiocesan since 1978</span>
                        </div>
                        <div class="about-sidebar-row">
                            <i class="ti ti-building-church"></i>
                            <span>Run by the Archdiocese of Davao</span>
                        </div>
                        <div class="about-sidebar-row">
                            <i class="ti ti-quote"></i>
                            <span>Ex Fide Ad Veritatem &mdash; "From Faith to Truth"</span>
                        </div>
                        <div class="about-sidebar-row">
                            <i class="ti ti-certificate"></i>
                            <span>PAASCU accredited, CHED recognized, ISO certified, AUN-QA compliant</span>
                        </div>
                        <div class="about-sidebar-row">
                            <i class="ti ti-map-pin"></i>
                            <span>Three campuses across Davao City and Samal</span>
                        </div>
                        <div class="about-sidebar-row">
                            <i class="ti ti-school"></i>
                            <span>Basic education through graduate programs</span>
                        </div>
                    </div>

                    <div class="about-block" style="margin-bottom:0;">
                        <h2>Our Campuses</h2>
                        <div class="campus-list">
                            <div class="campus-card">
                                <div class="c-icon">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-6h6v6" /></svg>
                                </div>
                                <div>
                                    <div class="c-name">Main Campus &mdash; Sta. Ana Avenue</div>
                                    <div class="c-desc">Undergraduate, graduate, Davao City.</div>
                                </div>
                            </div>
                           <div class="campus-card">
                                <div class="c-icon">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></svg>
                                </div>
                                <div>
                                    <div class="c-name">J.P. Laurel Campus</div>
                                    <div class="c-desc">Basic education &mdash;  Maritime Education facilities, Bajada, Davao City.</div>
                                </div>
                            </div>
                           <div class="campus-card">
                                <div class="c-icon">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12h20M12 2c2.5 2.5 4 6.5 4 10s-1.5 7.5-4 10c-2.5-2.5-4-6.5-4-10s1.5-7.5 4-10z" /></svg>
                                </div>
                                <div>
                                    <div class="c-name">Camudmud Campus &mdash; Babak</div>
                                    <div class="c-desc">Maritime Education facilities, Island Garden City of Samal.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer id="about-footer">
        <div class="wrap">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="school">Holy Cross of Davao College</div>
                    <p>An Archdiocesan Catholic institution in Davao City, serving students since 1951. The Office of
                        Registration and Records Management is proud to bring secure, streamlined credential services
                        to every student.</p>
                </div>
                <div>
                    <h4>Registrar services</h4>
                    <ul>
                        <li><a href="index.php#services">Graduation application</a></li>
                        <li><a href="index.php#services">Transcript request</a></li>
                        <li><a href="index.php#services">Diploma request</a></li>
                        <li><a href="index.php#services">Online clearance</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Helpful links</h4>
                    <ul>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'auth/login.php' ?>">Student portal</a></li>
                        <li><a href="https://studentportal.hcdc.edu.ph" target="_blank" rel="noopener">HCDC Student Portal</a></li>
                        <li><a href="https://www.instagram.com/hcdcofficial/" target="_blank" rel="noopener">Instagram</a></li>
                        <li><a href="https://www.facebook.com/hcdcofficial" target="_blank" rel="noopener">Facebook</a></li>
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
                    <a href="https://www.facebook.com/hcdcofficial" aria-label="Facebook"><i class="ti ti-brand-facebook"></i></a>
                    <a href="https://www.instagram.com/hcdcofficial/" aria-label="Instagram"><i class="ti ti-brand-instagram"></i></a>
                    <a href="#" aria-label="YouTube"><i class="ti ti-brand-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>