<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php'; // $pdo

if (!empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$sent = false;
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf_token'] ?? '');

    $email = trim((string) ($_POST['email'] ?? ''));
    $oldEmail = $email;

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT user_id, first_name, is_active FROM users WHERE email = :email LIMIT 1');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        // Always show the same success message whether or not the email
        // exists, so we don't leak which emails are registered.
        if ($user && (int) $user['is_active'] !== 0) {
            $selector  = bin2hex(random_bytes(9));
            $validator = bin2hex(random_bytes(33));
            $expiresAt = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour

            $insert = $pdo->prepare(
                'INSERT INTO password_reset_tokens (user_id, selector, validator_hash, expires_at, used)
                 VALUES (:user_id, :selector, :validator_hash, :expires_at, 0)'
            );
            $insert->execute([
                ':user_id'        => (int) $user['user_id'],
                ':selector'       => $selector,
                ':validator_hash' => hash('sha256', $validator),
                ':expires_at'     => $expiresAt,
            ]);

            $resetLink = 'http://' . $_SERVER['HTTP_HOST']
                . dirname($_SERVER['SCRIPT_NAME']) . '/reset_password.php'
                . '?selector=' . urlencode($selector) . '&validator=' . urlencode($validator);

            $subject = 'Reset your CertiChain password';
            $body = "Hello {$user['first_name']},\n\n"
                . "We received a request to reset your CertiChain password. Click the link below to choose a new password. This link expires in 1 hour.\n\n"
                . $resetLink . "\n\n"
                . "If you didn't request this, you can safely ignore this email.\n\n"
                . "Holy Cross of Davao College \xE2\x80\x93 Office of Registration and Records Management";

            $headers = "From: no-reply@hcdc.edu.ph\r\nContent-Type: text/plain; charset=UTF-8";

           // Best-effort send. Configure a proper SMTP mailer (e.g. PHPMailer)
            // in production — plain mail() is unreliable on most hosts.
            @mail($email, $subject, $body, $headers);

            log_activity($pdo, (int) $user['user_id'], 'user', 'password_reset_requested');
        }

        $sent = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | CertiChain &middot; Holy Cross of Davao College</title>
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

                <?php if ($sent): ?>
                    <div class="auth-card-head">
                        <div class="auth-card-eyebrow">Check your inbox</div>
                        <h2>Reset link sent</h2>
                        <p>If an account exists for that email, we've sent a link to reset your password. The link expires in 1 hour.</p>
                    </div>
                    <p class="auth-switch">
                        <a href="login.php">Back to login</a>
                    </p>
                <?php else: ?>
                    <div class="auth-card-head">
                        <div class="auth-card-eyebrow">Forgot password</div>
                        <h2>Reset your password</h2>
                        <p>Enter your account email and we'll send you a link to choose a new password.</p>
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

                    <form method="post" action="forgot_password.php" novalidate class="auth-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                        <div class="field">
                            <label for="email">Email address</label>
                            <div class="input-wrap">
                                <i class="ti ti-mail"></i>
                                <input type="email" id="email" name="email" placeholder="you@example.com"
                                    value="<?= htmlspecialchars($oldEmail) ?>" required autocomplete="email">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-gold btn-block">
                            <i class="ti ti-send"></i>Send reset link
                        </button>

                        <div class="auth-divider"><span>or</span></div>

                        <p class="auth-switch">
                            <a href="login.php">Back to login</a>
                        </p>
                    </form>
                <?php endif; ?>

            </div>
        </div>

    </div>

    <script src="../public/assets/js/auth.js"></script>
</body>

</html>