<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php'; // $pdo

$selector  = $_GET['selector'] ?? $_POST['selector'] ?? '';
$validator = $_GET['validator'] ?? $_POST['validator'] ?? '';

$errors = [];
$done = false;
$validLink = false;
$userId = null;

if ($selector !== '' && $validator !== '') {
    $stmt = $pdo->prepare(
        'SELECT id, user_id, validator_hash FROM password_reset_tokens
         WHERE selector = :selector AND used = 0 AND expires_at > NOW() LIMIT 1'
    );
    $stmt->execute([':selector' => $selector]);
    $row = $stmt->fetch();

    if ($row && hash_equals($row['validator_hash'], hash('sha256', $validator))) {
        $validLink = true;
        $userId = (int) $row['user_id'];
    }
}

if ($validLink && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
    csrf_verify($_POST['csrf_token'] ?? '');

    $password = (string) ($_POST['password'] ?? '');
    $confirm  = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE user_id = :uid');
        $update->execute([':hash' => $hash, ':uid' => $userId]);

        $markUsed = $pdo->prepare('UPDATE password_reset_tokens SET used = 1 WHERE selector = :selector');
        $markUsed->execute([':selector' => $selector]);

        log_activity($pdo, $userId, 'user', 'password_reset_completed');

        $done = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | CertiChain &middot; Holy Cross of Davao College</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/jpeg" href="../public/assets/logo/hcdc-logo.jpg">
    <link
        href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&family=IBM+Plex+Mono:wght@400;500&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/tabler-icons/2.44.0/iconfont/tabler-icons.min.css">
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="stylesheet" href="../public/assets/css/auth.css">
</head>

<body class="auth-body">

    <div class="auth-shell">

        <?php $authMode = 'login'; require __DIR__ . '/_auth-hero.php'; ?>

        <div class="auth-panel">
            <div class="auth-card auth-card-fade">

                <?php if (!$validLink && !$done): ?>
                    <div class="auth-card-head">
                        <div class="auth-card-eyebrow">Invalid link</div>
                        <h2>This reset link isn't valid</h2>
                        <p>It may have expired or already been used. Request a new one below.</p>
                    </div>
                    <p class="auth-switch">
                        <a href="forgot_password.php">Request a new reset link</a>
                    </p>

                <?php elseif ($done): ?>
                    <div class="auth-card-head">
                        <div class="auth-card-eyebrow">Success</div>
                        <h2>Password updated</h2>
                        <p>Your password has been changed. You can now log in with your new password.</p>
                    </div>
                    <p class="auth-switch">
                        <a href="login.php">Go to login</a>
                    </p>

                <?php else: ?>
                    <div class="auth-card-head">
                        <div class="auth-card-eyebrow">Reset password</div>
                        <h2>Choose a new password</h2>
                        <p>Enter and confirm your new password below.</p>
                    </div>

                    <?php if ($errors): ?>
                        <div class="auth-alert" role="alert">
                            <i class="ti ti-alert-circle"></i>
                            <div>
                                <?php foreach ($errors as $e): ?>
                                    <div><?= htmlspecialchars($e) ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="reset_password.php" novalidate class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
                        <input type="hidden" name="selector" value="<?= htmlspecialchars($selector) ?>">
                        <input type="hidden" name="validator" value="<?= htmlspecialchars($validator) ?>">

                        <div class="field">
                            <label for="password">New password</label>
                            <div class="input-wrap">
                                <i class="ti ti-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Enter new password"
                                    required autocomplete="new-password" minlength="8">
                                <button type="button" class="toggle-visibility" data-target="password"
                                    aria-label="Show password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="field">
                            <label for="confirm_password">Confirm new password</label>
                            <div class="input-wrap">
                                <i class="ti ti-lock"></i>
                                <input type="password" id="confirm_password" name="confirm_password"
                                    placeholder="Re-enter new password" required autocomplete="new-password"
                                    minlength="8">
                                <button type="button" class="toggle-visibility" data-target="confirm_password"
                                    aria-label="Show password">
                                    <i class="ti ti-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gold btn-block">
                            <i class="ti ti-check"></i>Update password
                        </button>
                    </form>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <script src="../public/assets/js/auth.js"></script>
</body>

</html>