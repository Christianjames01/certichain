<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/certificates.php'; 

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

$spotlightCerts = [
    [
        'title'   => 'Certificate of Enrollment (COE)',
        'sub'     => "Official proof that a student is currently enrolled at Holy Cross of Davao College. Commonly required for scholarship applications, bank requirements, visa processing, and government transactions.",
        'videoId' => 'QnlHfEGHNMA',
    ],
    [
        'title'   => 'Certificate of Registration (COR)',
        'sub'     => 'Official proof of your registered subjects and units for the term — often requested for allowance, subsidy, or scholarship applications.',
        'videoId' => 'QnlHfEGHNMA',
    ],
    [
        'title'   => 'Certification of Grades',
        'sub'     => "Provides an officially certified copy of the student's grades for a specific semester or academic year.",
        'videoId' => '_rS2uEvG0ik',
    ],
    [
        'title'   => 'Certificate of Graduation',
        'sub'     => 'Official confirmation that a student has graduated from their program — commonly required for employment and further studies.',
        'videoId' => 'YA9IOYDH0xY',
    ],
    [
        'title'   => 'Certificate of Active Student Status',
        'sub'     => 'Confirms you are an active, currently enrolled student in good academic standing, based on records maintained by the Registrar.',
        'videoId' => '_rS2uEvG0ik',
    ],
];
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
<h1 class="hero-title">Academic Certifications, <em>available online</em>. Securely verified with CertiChain.</h1>
<p class="lede">CertiChain enables HCDC students and alumni to request official academic certifications online.
    Every issued certification is securely processed and verified by the Registrar's Office, ensuring accuracy
    and authenticity from request to release.</p>
                <div class="hero-actions">
                    <?php if ($isLoggedIn): ?>
                        <a href="<?= htmlspecialchars($dashboardUrl) ?>" class="btn btn-gold"><i class="ti ti-file-certificate"></i>Go to your dashboard</a>
                    <?php else: ?>
                       <a href="auth/register.php" class="btn btn-gold"><i class="ti ti-file-certificate"></i>Request a Certification</a>
                    <?php endif; ?>
                    <a href="#verify" class="btn btn-ghost"><i class="ti ti-shield-check"></i>Verify a Certificate</a>
                </div>
                <div class="hero-quicklinks">
    <a href="certservices.php?cert=<?= certSlug('Certificate of Enrollment (COE)') ?>">Certificate of Enrollment</a>
    <a href="certservices.php?cert=<?= certSlug('Certification of Grades') ?>">Certification of Grades</a>
    <a href="certservices.php?cert=<?= certSlug('Certificate of Active Student Status') ?>">Certificate of Academic Standing</a>
    <a href="certservices.php?cert=<?= certSlug('Certificate of Graduation') ?>">Certificate of Graduation</a>
</div>
            </div>
            <div class="seal-card">
                <div class="verify-pill"><i class="ti ti-camera"></i>Certification Spotlight</div>
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
    <div class="label">Featured Academic Certifications</div>
    <p class="spotlight-title" id="spotlight-title"><?= htmlspecialchars($spotlightCerts[0]['title']) ?></p>
    <p class="spotlight-sub" id="spotlight-sub"><?= htmlspecialchars($spotlightCerts[0]['sub']) ?></p>
    <a href="certservices.php?cert=<?= certSlug($spotlightCerts[0]['title']) ?>" id="spotlight-request-btn" class="btn btn-gold" style="margin-top:12px;">
        <i class="ti ti-file-certificate"></i>Request this Certificate
    </a>
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
                      <li><a href="certservices.php?cert=<?= certSlug('Certificate of Enrollment (COE)') ?>">Certificate of Enrollment (COE)</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Registration (COR)') ?>">Certificate of Registration (COR)</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Student Status') ?>">Certificate of Student Status</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Current Enrollment') ?>">Certificate of Current Enrollment</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Attendance') ?>">Certificate of Attendance</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Academic Load') ?>">Certificate of Academic Load</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Residency') ?>">Certificate of Residency</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Active Student Status') ?>">Certificate of Active Student Status</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Official Transcript of Records (TOR)') ?>">Official Transcript of Records (TOR)</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certified True Copy of Transcript of Records') ?>">Certified True Copy of Transcript of Records</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Transcript of Records for Employment Purposes') ?>">Transcript of Records for Employment Purposes</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Transcript of Records for Board Examination Purposes') ?>">Transcript of Records for Board Examination Purposes</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Transcript of Records for Foreign Evaluation') ?>">Transcript of Records for Foreign Evaluation</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Grades') ?>">Certification of Grades</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of General Weighted Average (GWA)') ?>">Certification of General Weighted Average (GWA)</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Academic Records') ?>">Certification of Academic Records</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Units Earned') ?>">Certification of Units Earned</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Subjects Taken') ?>">Certification of Subjects Taken</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Completion of Academic Requirements') ?>">Certification of Completion of Academic Requirements</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Graduation') ?>">Certificate of Graduation</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Graduation Completion') ?>">Certificate of Graduation Completion</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Candidacy for Graduation') ?>">Certificate of Candidacy for Graduation</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Degree Completion') ?>">Certificate of Degree Completion</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Academic Completion') ?>">Certificate of Academic Completion</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Honors / Awards') ?>">Certificate of Honors / Awards</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Original Diploma') ?>">Original Diploma</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certified True Copy of Diploma') ?>">Certified True Copy of Diploma</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Replacement / Duplicate Diploma') ?>">Replacement / Duplicate Diploma</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Diploma Authentication Certificate') ?>">Diploma Authentication Certificate</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Transfer Credential') ?>">Certificate of Transfer Credential</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Honorable Dismissal') ?>">Certificate of Honorable Dismissal</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Transfer Credential') ?>">Transfer Credential</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Withdrawal') ?>">Certificate of Withdrawal</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of No Objection for Transfer') ?>">Certificate of No Objection for Transfer</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of No Record') ?>">Certificate of No Record</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Certification, Authentication &amp; Verification (CAV)') ?>">Certification, Authentication &amp; Verification (CAV)</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Academic Credential Verification Certificate') ?>">Academic Credential Verification Certificate</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('School Record Verification Certificate') ?>">School Record Verification Certificate</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Document Authentication Certificate') ?>">Document Authentication Certificate</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Authenticity of Academic Records') ?>">Certificate of Authenticity of Academic Records</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate of Curriculum') ?>">Certificate of Curriculum</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Course Description Certificate') ?>">Course Description Certificate</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Course Syllabus') ?>">Certification of Course Syllabus</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Subjects & Units') ?>">Certification of Subjects &amp; Units</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Medium of Instruction') ?>">Certification of Medium of Instruction</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certification of Program Completion') ?>">Certification of Program Completion</a></li>
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
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate for Employment Requirement') ?>">Certificate for Employment Requirement</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate for Scholarship Requirement') ?>">Certificate for Scholarship Requirement</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate for Internship/OJT Requirement') ?>">Certificate for Internship/OJT Requirement</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate for Visa Requirement') ?>">Certificate for Visa Requirement</a></li>
                        <li><a href="certservices.php?cert=<?= certSlug('Certificate for Embassy Requirement') ?>">Certificate for Embassy Requirement</a></li>
    <li><a href="certservices.php?cert=<?= certSlug('Certificate for Graduate School Admission') ?>">Certificate for Graduate School Admission</a></li>
    <li><a href="certservices.php?cert=<?= certSlug('Certificate for Professional Examination Requirement') ?>">Certificate for Professional Examination Requirement</a></li>
</ul>
                </div>

               

            </div>
        </div>
    </section>

    <style>
    .request-steps {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0;
    }

    .request-steps li {
        position: relative;
        display: flex;
        gap: 16px;
        padding: 0 0 28px 0;
    }

    .request-steps li:not(:last-child)::before {
        content: "";
        position: absolute;
        left: 21px;
        top: 44px;
        bottom: -6px;
        width: 2px;
        background: linear-gradient(180deg, var(--hcdc-gold-light, #d9b568) 0%, rgba(217, 181, 104, 0.15) 100%);
    }

    .request-steps .step-num {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: rgba(217, 181, 104, 0.12);
        border: 1px solid var(--hcdc-gold-light, #d9b568);
        color: var(--hcdc-gold-light, #d9b568);
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        font-family: 'IBM Plex Mono', monospace;
        z-index: 1;
        transition: transform 0.25s ease, background 0.25s ease;
    }

    .request-steps li:hover .step-num {
        transform: scale(1.08);
        background: var(--hcdc-gold-light, #d9b568);
        color: #10233f;
    }

    .request-steps .step-body .t-title {
        display: block;
        font-weight: 600;
        color: #fff;
        margin-bottom: 4px;
        font-size: 15.5px;
    }

    .request-steps .step-body .t-desc {
        display: block;
        color: rgba(255, 255, 255, 0.72);
        font-size: 14px;
        line-height: 1.5;
    }

    .process-cards {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }

    .process-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        padding: 22px 18px;
        transition: transform 0.25s ease, background 0.25s ease, border-color 0.25s ease;
    }

    .process-card:hover {
        transform: translateY(-4px);
        background: rgba(217, 181, 104, 0.08);
        border-color: var(--hcdc-gold-light, #d9b568);
    }

    .process-card .p-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(217, 181, 104, 0.15);
        color: var(--hcdc-gold-light, #d9b568);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 12px;
    }

    .process-card .p-icon i {
        font-size: 19px;
    }

    .process-card .num {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 12.5px;
        color: var(--hcdc-gold-light, #d9b568);
        letter-spacing: 0.06em;
        margin-bottom: 6px;
    }

    .process-card .lab {
        font-weight: 600;
        color: #fff;
        font-size: 15px;
        margin-bottom: 6px;
    }

    .process-card .desc {
        color: rgba(255, 255, 255, 0.7);
        font-size: 13.5px;
        line-height: 1.5;
    }

    @media (max-width: 640px) {
        .process-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<section class="trust-band" id="verify">
    <div class="wrap">
        <div>
            <p class="section-eyebrow" style="color:var(--hcdc-gold-light)">How to request a certificate</p>
            <h2>Request your academic certificate in four simple steps</h2>
            <p>Requesting academic certificates through CertiChain is fast and convenient. Students and alumni can
                submit requests online, upload the required documents, complete the manual payment process, and
                receive updates from the Registrar's Office until the certificate is ready for release or
                download.</p>
            <ul class="request-steps">
                <li>
                    <div class="step-num">01</div>
                    <div class="step-body">
                        <span class="t-title">Select the Certificate</span>
                        <span class="t-desc">Browse the available academic certificates and choose the document you need.</span>
                    </div>
                </li>
                <li>
                    <div class="step-num">02</div>
                    <div class="step-body">
                        <span class="t-title">Upload the Required Documents</span>
                        <span class="t-desc">Submit the required supporting documents based on the selected certificate. The Registrar will review your submission.</span>
                    </div>
                </li>
               <li>
    <div class="step-num">03</div>
    <div class="step-body">
        <span class="t-title">Pay at the Finance Office</span>
        <span class="t-desc">Complete the required payment manually at the Finance Office. After payment, upload a clear image or PDF of your Official Receipt through the system for validation.</span>
    </div>
</li>
                <li>
    <div class="step-num">04</div>
    <div class="step-body">
        <span class="t-title">Registrar Approval &amp; Certificate Release</span>
        <span class="t-desc">Once your request, requirements, and payment are verified, the Registrar approves your request. The certificate is then generated and made available for physical pickup at the Registrar's Office.</span>
    </div>
</li>
            </ul>
        </div>
        <div class="process-cards">
            <div class="process-card">
                <div class="p-icon">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M4 4h16M4 10h10M4 16h6" />
        <circle cx="17" cy="17" r="3" />
        <path d="M19.5 19.5L22 22" />
    </svg>
</div>
                <div class="num">STEP 01</div>
                <div class="lab">Choose Certificate</div>
                <div class="desc">Browse available certifications.</div>
            </div>
            <div class="process-card">
                <div class="p-icon">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
        <path d="M14 2v6h6" />
        <path d="M12 18v-6M9.5 14.5L12 12l2.5 2.5" />
    </svg>
</div>
                <div class="num">STEP 02</div>
                <div class="lab">Upload Requirements</div>
                <div class="desc">Submit the necessary documents online.</div>
            </div>
            <div class="process-card">
                <div class="p-icon">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16l-2.5-1.5L14 21l-2.5-1.5L9 21l-2.5-1.5z" />
        <path d="M8 8h8M8 12h8M8 16h4" />
    </svg>
</div>
                <div class="num">STEP 03</div>
                <div class="lab">Upload Official Receipt</div>
<div class="desc">After paying at the Finance Office, upload your Official Receipt for payment confirmation.</div>
            </div>
            <div class="process-card">
                <div class="p-icon">
    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="9" r="6" />
        <path d="M9.5 8.5l1.8 1.8L15 6.5" />
        <path d="M8 14.5L6 22l6-3 6 3-2-7.5" />
    </svg>
</div>
                <div class="num">STEP 04</div>
                <div class="lab">Receive Your Certificate</div>
<div class="desc">Claim your physical certificate at the Registrar's Office once approved.</div>
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
    Registration and Records Management is proud to bring secure, streamlined credential services
    to every student.</p>
                </div>
                <div>
                    <h4>Registrar services</h4>
<ul>
    <li><a href="certservices.php?cert=<?= certSlug('Certificate of Graduation') ?>">Graduation application</a></li>
    <li><a href="certservices.php?cert=<?= certSlug('Official Transcript of Records (TOR)') ?>">Transcript request</a></li>
    <li><a href="certservices.php?cert=<?= certSlug('Original Diploma') ?>">Diploma request</a></li>
    <li><a href="#services">Online clearance</a></li>
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
<script>
    const spotlightPlaylist = <?= json_encode(array_map(function ($c) {
        return [
            'videoId'  => $c['videoId'],
            'title'    => $c['title'],
            'sub'      => $c['sub'],
            'certSlug' => certSlug($c['title']),
        ];
    }, $spotlightCerts), JSON_UNESCAPED_SLASHES) ?>;

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
    document.getElementById("spotlight-request-btn").href = "certservices.php?cert=" + item.certSlug;
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