<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';   // session_start(), csrf_token(), csrf_verify()
require_once __DIR__ . '/../config/db.php';       // $pdo

// Already logged in? Send them to their dashboard instead of showing the form.
if (!empty($_SESSION['user_id'])) {
    $dashboardByRole = [
        'student'        => '../student/submit_request.php',
        'alumni'         => '../student/submit_request.php',
        'employee'       => '../employee/requests.php',
        'registrar_head' => '../registrar_head/assign_employee.php',
    ];
    header('Location: ' . ($dashboardByRole[$_SESSION['role'] ?? ''] ?? '../index.php'));
    exit;
}

$errors = [];
$oldEmail = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf_token'] ?? '');

    $email    = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $oldEmail = $email;

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($password === '') {
        $errors[] = 'Please enter your password.';
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
    'SELECT user_id AS id, first_name, password_hash, role, is_active
     FROM users WHERE email = :email LIMIT 1'
);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'Incorrect email or password.';
        } elseif (isset($user['is_active']) && (int) $user['is_active'] === 0) {
            $errors[] = 'This account has been deactivated. Please contact the Registrar\'s Office.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int) $user['id'];
            $_SESSION['role']       = $user['role'];
            $_SESSION['first_name'] = $user['first_name'];

            log_activity($pdo, (int) $user['id'], $user['role'], 'login');

            $dashboardByRole = [
                'student'        => '../student/submit_request.php',
                'alumni'         => '../student/submit_request.php',
                'employee'       => '../employee/requests.php',
                'registrar_head' => '../registrar_head/assign_employee.php',
            ];
            header('Location: ' . ($dashboardByRole[$user['role']] ?? '../index.php'));
            exit;
        }
    }
}

$authMode = 'login';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | CertiChain &middot; Holy Cross of Davao College</title>
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

        <?php require __DIR__ . '/_auth-hero.php'; ?>

        <div class="auth-panel">
            <div class="auth-card auth-card-fade">

                <div class="auth-card-head">
                    <div class="auth-card-eyebrow">Sign in</div>
                    <h2>Welcome back</h2>
                    <p>Log in to submit requests and track your certifications.</p>
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

                <form method="post" action="login.php" novalidate class="auth-form" id="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                    <div class="field">
                        <label for="email">Email address</label>
                        <div class="input-wrap">
                            <i class="ti ti-mail"></i>
                            <input type="email" id="email" name="email" placeholder="you@example.com"
                                value="<?= htmlspecialchars($oldEmail) ?>" required autocomplete="email">
                        </div>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="input-wrap">
                            <i class="ti ti-lock"></i>
                            <input type="password" id="password" name="password" placeholder="Enter your password"
                                required autocomplete="current-password">
                            <button type="button" class="toggle-visibility" data-target="password"
                                aria-label="Show password">
                                <i class="ti ti-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="field-row">
                        <label class="checkbox">
                            <input type="checkbox" name="remember" value="1">
                            <span>Remember me</span>
                        </label>
                        <a href="forgot_password.php" class="link-muted">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-gold btn-block">
                        <i class="ti ti-login"></i>Log in
                    </button>

                    <div class="auth-divider"><span>or</span></div>

                    <p class="auth-switch">
                        Don&rsquo;t have an account? <a href="register.php">Create one</a>
                    </p>
                </form>
            </div>
        </div>

    </div>

    <script src="../public/assets/js/auth.js"></script>
</body>

</html>