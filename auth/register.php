<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';   // session_start(), csrf_token(), csrf_verify()
require_once __DIR__ . '/../config/db.php';       // $pdo

if (!empty($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$errors = [];
$old = [
    'first_name'     => '',
    'middle_name'    => '',
    'last_name'      => '',
    'email'          => '',
    'id_number'      => '',
    'phone'          => '',
    'user_type'      => 'student',
    'program_id'     => '',
    'year_level'     => '',
    'year_graduated' => '',
];

// Programs grouped by college, for the <select> and for server-side validation
$programs = $pdo->query(
    'SELECT p.program_id, p.program_name, p.degree_code, c.college_name
     FROM programs p
     JOIN colleges c ON c.college_id = p.college_id
     WHERE p.is_active = 1
     ORDER BY c.college_name, p.program_name'
)->fetchAll();
$validProgramIds = array_column($programs, 'program_id');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify($_POST['csrf_token'] ?? '');

    foreach ($old as $key => $_) {
        $old[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $password        = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
    $agreedToTerms    = isset($_POST['terms']);

    if ($old['first_name'] === '') $errors[] = 'First name is required.';
    if ($old['last_name'] === '')  $errors[] = 'Last name is required.';
    if ($old['email'] === '' || !filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($old['id_number'] === '') $errors[] = 'Student / Alumni ID is required.';
    if ($old['phone'] === '')     $errors[] = 'Phone number is required.';
    if (!in_array($old['user_type'], ['student', 'alumni'], true)) {
        $errors[] = 'Please select a valid account type.';
    }

    $programId = (int) $old['program_id'];
    if ($programId <= 0 || !in_array($programId, $validProgramIds, true)) {
        $errors[] = 'Please select a valid program.';
    }

    if ($old['user_type'] === 'alumni') {
        if ($old['year_graduated'] === '' || !ctype_digit($old['year_graduated'])) {
            $errors[] = 'Please enter a valid year graduated.';
        }
    }

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }
    if (!$agreedToTerms) {
        $errors[] = 'You must agree to the Terms and Conditions to continue.';
    }

    if (!$errors) {
        $check = $pdo->prepare('SELECT user_id FROM users WHERE email = :email LIMIT 1');
        $check->execute([':email' => $old['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account with this email already exists.';
        }
    }

    if (!$errors) {
        $pdo->beginTransaction();
        try {
            // users: auth/identity only — no role-specific fields
            $stmt = $pdo->prepare(
                'INSERT INTO users (first_name, last_name, email, password_hash, role, is_active, created_at)
                 VALUES (:first_name, :last_name, :email, :password_hash, :role, 1, NOW())'
            );
            $stmt->execute([
                ':first_name'    => $old['first_name'],
                ':last_name'     => $old['last_name'],
                ':email'         => $old['email'],
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':role'          => $old['user_type'],
            ]);

            $newUserId = (int) $pdo->lastInsertId();

            // Role-specific fields (middle_name, phone, id number, program)
            // live on students/alumni, never on users.
            if ($old['user_type'] === 'student') {
                $stmt = $pdo->prepare(
                    'INSERT INTO students (user_id, student_number, middle_name, phone, program_id, year_level)
                     VALUES (:user_id, :student_number, :middle_name, :phone, :program_id, :year_level)'
                );
                $stmt->execute([
                    ':user_id'        => $newUserId,
                    ':student_number' => $old['id_number'],
                    ':middle_name'    => $old['middle_name'] ?: null,
                    ':phone'          => $old['phone'],
                    ':program_id'     => $programId,
                    ':year_level'     => $old['year_level'] !== '' ? (int) $old['year_level'] : 1,
                ]);
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO alumni (user_id, student_number, middle_name, phone, program_id, year_graduated)
                     VALUES (:user_id, :student_number, :middle_name, :phone, :program_id, :year_graduated)'
                );
                $stmt->execute([
                    ':user_id'        => $newUserId,
                    ':student_number' => $old['id_number'],
                    ':middle_name'    => $old['middle_name'] ?: null,
                    ':phone'          => $old['phone'],
                    ':program_id'     => $programId,
                    ':year_graduated' => $old['year_graduated'],
                ]);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        session_regenerate_id(true);
        $_SESSION['user_id']    = $newUserId;
        $_SESSION['role']       = $old['user_type'];
        $_SESSION['first_name'] = $old['first_name'];

        log_activity($pdo, $newUserId, $old['user_type'], 'register');

        header('Location: ../student/submit_request.php');
        exit;
    }
}

$authMode = 'register';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account | CertiChain &middot; Holy Cross of Davao College</title>
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
            <div class="auth-card auth-card-fade auth-card-wide">

                <div class="auth-card-head">
                    <div class="auth-card-eyebrow">Create account</div>
                    <h2>Join CertiChain</h2>
                    <p>Register as a student or alumnus to start requesting certifications online.</p>
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

                <form method="post" action="register.php" novalidate class="auth-form" id="register-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">

                    <div class="form-section">
                        <div class="form-section-title"><i class="ti ti-id-badge-2"></i>Personal information</div>
                        <div class="field-grid field-grid-3">
                            <div class="field">
                                <label for="first_name">First name</label>
                                <input type="text" id="first_name" name="first_name" required autocomplete="given-name"
                                    value="<?= htmlspecialchars($old['first_name']) ?>">
                            </div>
                            <div class="field">
                                <label for="middle_name">Middle name <span class="optional">(optional)</span></label>
                                <input type="text" id="middle_name" name="middle_name" autocomplete="additional-name"
                                    value="<?= htmlspecialchars($old['middle_name']) ?>">
                            </div>
                            <div class="field">
                                <label for="last_name">Last name</label>
                                <input type="text" id="last_name" name="last_name" required autocomplete="family-name"
                                    value="<?= htmlspecialchars($old['last_name']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="ti ti-school"></i>Account details</div>
                        <div class="field-grid field-grid-2">
                            <div class="field">
                                <label for="email">Email address</label>
                                <div class="input-wrap">
                                    <i class="ti ti-mail"></i>
                                    <input type="email" id="email" name="email" placeholder="you@example.com" required
                                        autocomplete="email" value="<?= htmlspecialchars($old['email']) ?>">
                                </div>
                                <span class="field-hint" id="email-hint"></span>
                            </div>
                            <div class="field">
                                <label for="id_number">Student / Alumni ID</label>
                                <div class="input-wrap">
                                    <i class="ti ti-id"></i>
                                    <input type="text" id="id_number" name="id_number" required
                                        value="<?= htmlspecialchars($old['id_number']) ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label for="phone">Phone number</label>
                                <div class="input-wrap">
                                    <i class="ti ti-phone"></i>
                                    <input type="tel" id="phone" name="phone" placeholder="09XX XXX XXXX" required
                                        autocomplete="tel" value="<?= htmlspecialchars($old['phone']) ?>">
                                </div>
                            </div>
                            <div class="field">
                                <label for="user_type">I am a</label>
                                <div class="input-wrap select-wrap">
                                    <i class="ti ti-users"></i>
                                    <select id="user_type" name="user_type" required>
                                        <option value="student" <?= $old['user_type'] === 'student' ? 'selected' : '' ?>>Student</option>
                                        <option value="alumni" <?= $old['user_type'] === 'alumni' ? 'selected' : '' ?>>Alumni</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="ti ti-building-bank"></i>Program</div>
                        <div class="field-grid field-grid-2">
                            <div class="field">
                                <label for="program_id">Program</label>
                                <div class="input-wrap select-wrap">
                                    <i class="ti ti-book"></i>
                                    <select id="program_id" name="program_id" required>
                                        <option value="">-- Select your program --</option>
                                        <?php
                                        $currentCollege = null;
                                        foreach ($programs as $p):
                                            if ($p['college_name'] !== $currentCollege):
                                                if ($currentCollege !== null) echo '</optgroup>';
                                                $currentCollege = $p['college_name'];
                                                echo '<optgroup label="' . htmlspecialchars($currentCollege) . '">';
                                            endif;
                                        ?>
                                            <option value="<?= (int) $p['program_id'] ?>"
                                                <?= ((string) $p['program_id'] === $old['program_id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($p['degree_code'] . ' - ' . $p['program_name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                        <?php if ($programs): echo '</optgroup>'; endif; ?>
                                    </select>
                                </div>
                                <span class="field-hint">This determines which office handles your requests.</span>
                            </div>
                            <div class="field" id="student-year-field">
                                <label for="year_level">Year level</label>
                                <div class="input-wrap select-wrap">
                                    <i class="ti ti-stairs-up"></i>
                                    <select id="year_level" name="year_level">
                                        <?php for ($y = 1; $y <= 6; $y++): ?>
                                            <option value="<?= $y ?>" <?= $old['year_level'] === (string) $y ? 'selected' : '' ?>><?= $y ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="field" id="alumni-year-field" style="display:none;">
                                <label for="year_graduated">Year graduated</label>
                                <div class="input-wrap">
                                    <i class="ti ti-calendar-event"></i>
                                    <input type="number" id="year_graduated" name="year_graduated" min="1950"
                                        max="<?= (int) date('Y') ?>"
                                        value="<?= htmlspecialchars($old['year_graduated']) ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="form-section-title"><i class="ti ti-shield-lock"></i>Security</div>
                        <div class="field-grid field-grid-2">
                            <div class="field">
                                <label for="password">Password</label>
                                <div class="input-wrap">
                                    <i class="ti ti-lock"></i>
                                    <input type="password" id="password" name="password" required minlength="8"
                                        autocomplete="new-password">
                                    <button type="button" class="toggle-visibility" data-target="password"
                                        aria-label="Show password">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                                <div class="strength-meter" id="strength-meter">
                                    <div class="strength-bar"><span></span><span></span><span></span><span></span></div>
                                    <span class="strength-label" id="strength-label">Password strength</span>
                                </div>
                            </div>
                            <div class="field">
                                <label for="confirm_password">Confirm password</label>
                                <div class="input-wrap">
                                    <i class="ti ti-lock-check"></i>
                                    <input type="password" id="confirm_password" name="confirm_password" required
                                        autocomplete="new-password">
                                    <button type="button" class="toggle-visibility" data-target="confirm_password"
                                        aria-label="Show password">
                                        <i class="ti ti-eye"></i>
                                    </button>
                                </div>
                                <span class="field-hint" id="confirm-hint"></span>
                            </div>
                        </div>
                    </div>

                    <label class="checkbox checkbox-terms">
                        <input type="checkbox" name="terms" value="1" required>
                        <span>I agree to the <a href="../terms.php" target="_blank">Terms and Conditions</a> and
                            <a href="../privacy.php" target="_blank">Privacy Policy</a>.</span>
                    </label>

                    <button type="submit" class="btn btn-gold btn-block">
                        <i class="ti ti-user-plus"></i>Create account
                    </button>

                    <p class="auth-switch">
                        Already have an account? <a href="login.php">Log in</a>
                    </p>
                </form>
            </div>
        </div>

    </div>

    <script>
        // Toggle year-level vs year-graduated field based on account type
        const userTypeSelect = document.getElementById('user_type');
        const studentYearField = document.getElementById('student-year-field');
        const alumniYearField = document.getElementById('alumni-year-field');

        function toggleYearFields() {
            const isAlumni = userTypeSelect.value === 'alumni';
            studentYearField.style.display = isAlumni ? 'none' : '';
            alumniYearField.style.display = isAlumni ? '' : 'none';
        }
        userTypeSelect.addEventListener('change', toggleYearFields);
        toggleYearFields();
    </script>
    <script src="../public/assets/js/auth.js"></script>
</body>

</html>