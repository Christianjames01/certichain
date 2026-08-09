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
        
        <h1 class="auth-hero-title">
            <?php if ($authMode === 'register'): ?>
                Create your account, <em>request with confidence</em>.
            <?php else: ?>
                Welcome back to <em>CertiChain</em>.
            <?php endif; ?>
        </h1>
       <p class="auth-hero-lede">
            Request your academic certifications online. Fast, reliable, and officially managed by the Registrar's Office.
        </p>

       <div class="auth-hero-cards">
            <div class="auth-float-card auth-float-card-1" id="fc-1">
                <div class="fc-icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="9" r="6" /><path d="M9.5 8.5l1.8 1.8L15 6.5" /><path d="M8 14.5L6 22l6-3 6 3-2-7.5" /></svg>
                </div>
                <div class="fc-text">
                    <div class="fc-title">Certificate of Enrollment</div>
                    <div class="fc-sub">Approved &amp; ready</div>
                </div>
            </div>
            <div class="auth-float-card auth-float-card-2" id="fc-2">
                <div class="fc-icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l7 4v5c0 4.5-3 8-7 9-4-1-7-4.5-7-9V7z" /><path d="M9 12l2 2 4-4" /></svg>
                </div>
                <div class="fc-text">
                    <div class="fc-title">Registrar Approved</div>
                    <div class="fc-sub">Officially issued record</div>
                </div>
            </div>
            <div class="auth-float-card auth-float-card-3" id="fc-3">
                <div class="fc-icon">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" /><path d="M14 2v6h6" /><path d="M9 13h6M9 17h6M9 9h1" /></svg>
                </div>
                <div class="fc-text">
                    <div class="fc-title">Official Transcript of Records</div>
                    <div class="fc-sub">Processing request</div>
                </div>
            </div>
        </div>

        <script>
            (function () {
                var certList = [
                    { title: 'Certificate of Enrollment', sub: 'Approved & ready' },
                    { title: 'Registrar Approved', sub: 'Officially issued record' },
                    { title: 'Official Transcript of Records', sub: 'Processing request' },
                    { title: 'Certificate of Graduation', sub: 'Officially issued record' },
                    { title: 'Original Diploma', sub: 'Ready for release' },
                    { title: 'Certification of Grades', sub: 'Approved & ready' },
                    { title: 'Certificate of Registration (COR)', sub: 'Approved & ready' }
                ];

                var cards = [
                    document.getElementById('fc-1'),
                    document.getElementById('fc-2'),
                    document.getElementById('fc-3')
                ];

                if (!cards[0] || !cards[1] || !cards[2]) return;

                var indexes = [0, 1, 2];

                function renderCard(card, item) {
                    var titleEl = card.querySelector('.fc-title');
                    var subEl = card.querySelector('.fc-sub');
                    card.classList.add('fc-fade-out');
                    setTimeout(function () {
                        titleEl.textContent = item.title;
                        subEl.textContent = item.sub;
                        card.classList.remove('fc-fade-out');
                    }, 200);
                }

                setInterval(function () {
                    for (var i = 0; i < cards.length; i++) {
                        renderCard(cards[i], certList[indexes[i]]);
                        indexes[i] = (indexes[i] + 1) % certList.length;
                    }
                }, 5000);
            })();
        </script>
    </div>

    <div class="auth-hero-foot">
        <div class="auth-hero-foot-item"><i class="ti ti-building-bank"></i>Holy Cross of Davao College &middot; Official Registrar Portal</div>
    </div>
</div>