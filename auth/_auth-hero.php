<?php
/**
 * Shared hero/branding panel for auth pages (login.php, register.php).
 * Expects $authMode = 'login' | 'register' to tweak copy slightly.
 */
$authMode = $authMode ?? 'login';
?>
<div class="auth-hero">
    <div class="auth-hero-glow"></div>
    <div class="auth-hero-shape auth-hero-shape-1"></div>
    <div class="auth-hero-shape auth-hero-shape-2"></div>

    <div class="auth-hero-top">
        <a href="../index.php" class="auth-brand">
            <img class="crest" src="../public/assets/logo/hcdc-logo.jpg" alt="Holy Cross of Davao College logo">
            <div class="brand-text">
                <div class="school">Holy Cross of Davao College</div>
                <div class="office">Office of Registration &amp; Records Management</div>
            </div>
        </a>
    </div>

    <div class="auth-hero-body">
        <div class="eyebrow"><span class="dot"></span>CertiChain &middot; Secure certification portal</div>
        <h1 class="auth-hero-title">
            <?php if ($authMode === 'register'): ?>
                Create your account, <em>request with confidence</em>.
            <?php else: ?>
                Welcome back to <em>CertiChain</em>.
            <?php endif; ?>
        </h1>
        <p class="auth-hero-lede">
            Securely request and verify academic certifications online. Fast, reliable, and officially
            managed by the Registrar&rsquo;s Office.
        </p>

        <div class="auth-hero-cards">
            <div class="auth-float-card auth-float-card-1">
                <div class="fc-icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="6" /><path d="M9.5 8.5l1.8 1.8L15 6.5" /><path d="M8 14.5L6 22l6-3 6 3-2-7.5" /></svg>
                </div>
                <div class="fc-text">
                    <div class="fc-title">Certificate of Enrollment</div>
                    <div class="fc-sub">Approved &amp; ready</div>
                </div>
            </div>
            <div class="auth-float-card auth-float-card-2">
                <div class="fc-icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 4v5c0 4.5-3 8-7 9-4-1-7-4.5-7-9V7z" /><path d="M9 12l2 2 4-4" /></svg>
                </div>
                <div class="fc-text">
                    <div class="fc-title">Registrar Verified</div>
                    <div class="fc-sub">Officially issued record</div>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-hero-foot">
        <div class="auth-hero-foot-item"><i class="ti ti-lock"></i>Encrypted connection</div>
        <div class="auth-hero-foot-item"><i class="ti ti-building-bank"></i>Official Registrar Service</div>
    </div>
</div>