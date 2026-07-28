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
    <title>The Office of Registration and Records Management | Holy Cross of Davao College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="public/assets/logo/hcdc-logo.jpg">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="public/assets/css/index.css">
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
                <img class="crest" src="public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
                <div class="brand-text">
                    <div class="school">Holy Cross of Davao College</div>
                    <div class="office">The Office of Registration and Records Management &middot; CertiChain AI</div>
                </div>
            </div>
            <nav class="primary">
                <a href="#services">Registrar Services</a>
                <a href="#verify">Credential Verification</a>
                <a href="#academics">Academic References</a>
                <a href="#news">Announcements</a>
                <a href="#about">About</a>
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
                    <a href="auth/register.php" class="btn btn-ghost"><i class="ti ti-user-plus"></i>Register</a>
                    <a href="auth/login.php" class="btn btn-primary"><i class="ti ti-login"></i>Login</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <section class="hero">
        <div class="wrap">
            <div>
                <div class="eyebrow"><span class="dot"></span>Blaze your trail, securely verified</div>
                <h1 class="hero-title">Registrar services, <em>fully online</em>. Credentials, blockchain-verified.</h1>
                <p class="lede">CertiChain AI lets HCDC students apply for graduation, request transcripts and diplomas,
                    settle clearance, and pay online — while every credential we issue is sealed on the blockchain and
                    instantly verifiable by employers and partner institutions.</p>
                <div class="hero-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-gold"><i class="ti ti-file-certificate"></i>Go to your dashboard</a>
                    <?php else: ?>
                        <a href="auth/register.php" class="btn btn-gold"><i class="ti ti-file-certificate"></i>Start a registrar request</a>
                    <?php endif; ?>
                    <a href="#verify" class="btn btn-ghost"><i class="ti ti-shield-check"></i>Verify a document</a>
                </div>
                <div class="hero-quicklinks">
                    <a href="#services">Graduation application</a>
                    <a href="#services">Transcript request</a>
                    <a href="#services">Diploma request</a>
                    <a href="#services">Online clearance</a>
                </div>
            </div>
            <div class="seal-card">
                <div class="verify-pill"><i class="ti ti-camera"></i>Registrar spotlight</div>
                <div class="spotlight-media">
                    <div id="spotlight-player"></div>
                    <div class="next-up-overlay" id="next-up-overlay">
                        <div class="next-up-card">
                            <div class="next-up-label">Up next</div>
                            <div class="next-up-title" id="next-up-title"></div>
                            <div class="next-up-actions">
                                <button type="button" class="next-up-cancel" id="next-up-cancel">Cancel</button>
                                <button type="button" class="next-up-play" id="next-up-play">Play now (<span
                                        id="next-up-countdown">5</span>)</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="spotlight-body">
                    <div class="label">What's happening at the registrar</div>
                    <p class="spotlight-title" id="spotlight-title">How to request your Transcript of Records</p>
                    <p class="spotlight-sub" id="spotlight-sub">A quick walkthrough of the counter, the queue, and what
                        to
                        bring for your request &mdash; posted by the registrar's office.</p>
                </div>
                <div class="spotlight-dots" id="spotlight-dots"></div>
            </div>
        </div>
    </section>


    <section id="services">
        <div class="wrap">
            <div class="section-head">
                <div>
                    <p class="section-eyebrow">Online registrar services</p>
                    <h2 class="section-title">Every certificate and credential, without the counter line</h2>
                </div>
                <a href="#verify" class="section-link">Verify a credential</a>
            </div>
            <div class="services-categories">

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 9L12 5 2 9l10 4 10-4z" /><path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5" /><path d="M22 9v6" /></svg>
                        </div>
                        <h3>Enrollment &amp; student status</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Enrollment (COE)</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Registration (COR)</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Student Status</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Current Enrollment</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Attendance</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Academic Load</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Residency</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Active Student Status</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M9 13h6M9 17h6M9 9h1" /></svg>
                        </div>
                        <h3>Academic records</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Official Transcript of Records (TOR)</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certified True Copy of Transcript of Records</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Transcript of Records for Employment Purposes</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Transcript of Records for Board Examination Purposes</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Transcript of Records for Foreign Evaluation</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Grades</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of General Weighted Average (GWA)</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Academic Records</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Units Earned</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Subjects Taken</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Completion of Academic Requirements</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="6" /><path d="M9.5 8.5l1.8 1.8L15 6.5" /><path d="M8 14.5L6 22l6-3 6 3-2-7.5" /></svg>
                        </div>
                        <h3>Graduation</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Graduation</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Graduation Completion</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Candidacy for Graduation</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Degree Completion</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Academic Completion</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Honors / Awards</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5" /><path d="M8.5 12.5L7 22l5-3 5 3-1.5-9.5" /></svg>
                        </div>
                        <h3>Diploma</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Original Diploma</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certified True Copy of Diploma</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Replacement / Duplicate Diploma</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Diploma Authentication Certificate</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 3H5a2 2 0 0 0-2 2v4m18 0V5a2 2 0 0 0-2-2h-4m0 18h4a2 2 0 0 0 2-2v-4M3 15v4a2 2 0 0 0 2 2h4" /></svg>
                        </div>
                        <h3>Transfer &amp; withdrawal</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Transfer Credential</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Honorable Dismissal</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Transfer Credential</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Withdrawal</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of No Objection for Transfer</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of No Record</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" /><rect x="14" y="3" width="7" height="7" /><rect x="3" y="14" width="7" height="7" /><path d="M14 14h3v3h-3zM19 14h2M14 19h2M19 19h2" /></svg>
                        </div>
                        <h3>Authentication &amp; verification</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification, Authentication &amp; Verification (CAV)</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Academic Credential Verification Certificate</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">School Record Verification Certificate</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Document Authentication Certificate</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Authenticity of Academic Records</a></li>
                        <li><a href="#verify">Verify an issued credential online →</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" /><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" /></svg>
                        </div>
                        <h3>Curriculum &amp; course</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate of Curriculum</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Course Description Certificate</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Course Syllabus</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Subjects &amp; Units</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Medium of Instruction</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certification of Program Completion</a></li>
                    </ul>
                </div>

                <div class="category-card">
                    <div class="category-head">
                        <div class="category-icon">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" /><path d="M8 12l3 3 5-6" /></svg>
                        </div>
                        <h3>Special purpose</h3>
                    </div>
                    <ul class="cert-list">
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Employment Requirement</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Scholarship Requirement</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Internship/OJT Requirement</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Visa Requirement</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Embassy Requirement</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Graduate School Admission</a></li>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'certservices.php' ?>">Certificate for Professional Examination Requirement</a></li>
                    </ul>
                </div>

               

            </div>
        </div>
    </section>

    <section class="trust-band" id="verify">
        <div class="wrap">
            <div>
                <p class="section-eyebrow" style="color:var(--hcdc-gold-light)">How verification works</p>
                <h2>A credential you can trust, checked in seconds</h2>
                <p>Every diploma and transcript HCDC issues is hashed with BLAKE3 and anchored in our secure database.
                    Uploading a document or scanning its QR code re-computes the hash and compares it against the
                    stored record — no phone calls to the registrar required.</p>
                <ul class="steps">
                    <li><span class="t">Registrar approves the credential and generates a BLAKE3 hash.</span></li>
                    <li><span class="t">The hash is stored securely alongside the certificate record.</span></li>
                    <li><span class="t">A QR code is generated and attached to the digital document.</span></li>
                    <li><span class="t">Anyone can scan or upload the file to confirm authenticity.</span></li>
                </ul>
            </div>
            <div class="trust-stats">
                <div class="stat-card">
                    <div class="num">100%</div>
                    <div class="lab">Registrar transactions available online</div>
                </div>
                <div class="stat-card">
                    <div class="num">BLAKE3</div>
                    <div class="lab">Credential hashing standard</div>
                </div>
                <div class="stat-card">
                    <div class="num">24/7</div>
                    <div class="lab">System availability</div>
                </div>
                <div class="stat-card">
                    <div class="num">&lt;1 min</div>
                    <div class="lab">Average verification time</div>
                </div>
            </div>
        </div>
    </section>

    <section id="news">
        <div class="wrap">
            <div class="section-head">
                <div>
                    <p class="section-eyebrow">Announcements</p>
                    <h2 class="section-title">News from the registrar's office</h2>
                </div>
                <a href="#" class="section-link">View all announcements</a>
            </div>
            <div class="news-grid">
                <div class="news-card">
                    <div class="news-thumb"></div>
                    <div class="news-body">
                        <div class="news-date">S.Y. 2026-2027</div>
                        <h3>CertiChain now live for graduating students</h3>
                        <p>Graduation applications, clearance, and diploma requests can now be completed fully online.
                        </p>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-thumb"></div>
                    <div class="news-body">
                        <div class="news-date">Registration period</div>
                        <h3>Enrollment schedule for the upcoming term</h3>
                        <p>Check the academic calendar for registration deadlines across all programs.</p>
                    </div>
                </div>
                <div class="news-card">
                    <div class="news-thumb"></div>
                    <div class="news-body">
                        <div class="news-date">Advisory</div>
                        <h3>Requesting documents during the semestral break</h3>
                        <p>Transcript and diploma requests continue to be processed online during the break.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="#services">Graduation application</a></li>
                        <li><a href="#services">Transcript request</a></li>
                        <li><a href="#services">Diploma request</a></li>
                        <li><a href="#services">Online clearance</a></li>
                    </ul>
                </div>
                <div>
                    <h4>Helpful links</h4>
                    <ul>
                        <li><a href="<?= $isLoggedIn ? htmlspecialchars($dashboardUrl) : 'auth/login.php' ?>">Student portal</a></li>
                        <li><a href="#">Academic calendar</a></li>
                        <li><a href="#">Downloadable forms</a></li>
                        <li><a href="#verify">Credential verification</a></li>
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

</body>
<script>
    const spotlightPlaylist = [
        {
            videoId: "_rS2uEvG0ik",
            title: "How to request your Transcript of Records",
            sub: "A quick walkthrough of the counter, the queue, and what to bring for your request."
        },
        {
            videoId: "YA9IOYDH0xY",
            title: "Applying for graduation online",
            sub: "Step-by-step guide to submitting your graduation application through the portal."
        },
        {
            videoId: "fgYtNPEtpm4",
            title: "Requesting a diploma or replacement copy",
            sub: "What to prepare and how QR-verifiable diplomas are issued."
        },
        {
            videoId: "QnlHfEGHNMA",
            title: "Certificate of enrollment & good moral",
            sub: "How to request common registrar certifications online."
        },
        {
            videoId: "D0uOGq7fMn0",
            title: "Completing your online clearance",
            sub: "Routing clearance across departments and settling balances online."
        }
    ];

    let spotlightIndex = 0;
    let spotlightPlayer;

    function loadYouTubeAPI() {
        const tag = document.createElement("script");
        tag.src = "https://www.youtube.com/iframe_api";
        document.body.appendChild(tag);
    }

    function renderDots() {
        const dotsEl = document.getElementById("spotlight-dots");
        dotsEl.innerHTML = "";
        spotlightPlaylist.forEach((item, i) => {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.className = "spotlight-dot" + (i === spotlightIndex ? " active" : "");
            dot.textContent = i + 1;
            dot.setAttribute("title", item.title);
            dot.setAttribute("aria-label", item.title);

            const tip = document.createElement("span");
            tip.className = "spotlight-dot-tip";
            tip.textContent = item.title;
            dot.appendChild(tip);

            dot.addEventListener("click", () => playSpotlight(i));
            dotsEl.appendChild(dot);
        });
    }

    function updateSpotlightText() {
        const item = spotlightPlaylist[spotlightIndex];
        document.getElementById("spotlight-title").textContent = item.title;
        document.getElementById("spotlight-sub").textContent = item.sub;
        renderDots();
    }

    function getNextIndex() {
        return (spotlightIndex + 1) % spotlightPlaylist.length;
    }

    let progressWatcher = null;
    let nextUpShown = false;
    let nextUpCancelled = false;
    let countdownValue = 5;
    const COUNTDOWN_START = 5;

    function showNextUpOverlay() {
        if (nextUpCancelled) return;
        nextUpShown = true;
        countdownValue = COUNTDOWN_START;
        const nextItem = spotlightPlaylist[getNextIndex()];
        document.getElementById("next-up-title").textContent = nextItem.title;
        document.getElementById("next-up-countdown").textContent = countdownValue;
        document.getElementById("next-up-overlay").classList.add("visible");
    }

    function hideNextUpOverlay() {
        document.getElementById("next-up-overlay").classList.remove("visible");
    }

    function resetNextUpState() {
        nextUpShown = false;
        nextUpCancelled = false;
        hideNextUpOverlay();
    }

    function startProgressWatcher() {
        stopProgressWatcher();
        progressWatcher = setInterval(() => {
            if (!spotlightPlayer || !spotlightPlayer.getCurrentTime) return;
            const duration = spotlightPlayer.getDuration();
            const current = spotlightPlayer.getCurrentTime();
            if (!duration) return;
            const remaining = duration - current;

            if (remaining <= COUNTDOWN_START && remaining > 0 && !nextUpShown && !nextUpCancelled) {
                showNextUpOverlay();
            }
            if (nextUpShown && !nextUpCancelled) {
                const newCount = Math.max(0, Math.ceil(remaining));
                if (newCount !== countdownValue) {
                    countdownValue = newCount;
                    document.getElementById("next-up-countdown").textContent = countdownValue;
                }
            }
        }, 500);
    }

    function stopProgressWatcher() {
        if (progressWatcher) {
            clearInterval(progressWatcher);
            progressWatcher = null;
        }
    }

    function playSpotlight(index) {
        spotlightIndex = index;
        resetNextUpState();
        updateSpotlightText();
        if (spotlightPlayer && spotlightPlayer.loadVideoById) {
            spotlightPlayer.loadVideoById(spotlightPlaylist[spotlightIndex].videoId);
        }
    }

    function onYouTubeIframeAPIReady() {
        spotlightPlayer = new YT.Player("spotlight-player", {
            videoId: spotlightPlaylist[spotlightIndex].videoId,
            playerVars: { rel: 0 },
            events: {
                onStateChange: function (event) {
                    if (event.data === YT.PlayerState.PLAYING) {
                        startProgressWatcher();
                    }
                    if (event.data === YT.PlayerState.PAUSED) {
                        stopProgressWatcher();
                    }
                    if (event.data === YT.PlayerState.ENDED) {
                        stopProgressWatcher();
                        playSpotlight(getNextIndex());
                    }
                }
            }
        });
    }

    document.getElementById("next-up-cancel").addEventListener("click", () => {
        nextUpCancelled = true;
        hideNextUpOverlay();
    });

    document.getElementById("next-up-play").addEventListener("click", () => {
        stopProgressWatcher();
        playSpotlight(getNextIndex());
    });

    window.onYouTubeIframeAPIReady = onYouTubeIframeAPIReady;
    renderDots();
    loadYouTubeAPI();
</script>

</html>