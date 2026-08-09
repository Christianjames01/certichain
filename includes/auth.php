<?php
declare(strict_types=1);

/**
 * CERTICHAIN - Authentication & Authorization Helpers
 */

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);

// Needed here (not just in the pages that already require it) so the
// remember-me check below can query the database before session_start
// alone would otherwise leave the user logged out.
require_once __DIR__ . '/../config/db.php'; // $pdo

/**
 * Remember-me: if there's no active session but a valid remember_me
 * cookie exists, restore the session from the stored token.
 */
if (empty($_SESSION['user_id']) && !empty($_COOKIE['remember_me'])) {
    error_log('REMEMBER RESTORE: cookie present = ' . $_COOKIE['remember_me']);

    $parts = explode(':', $_COOKIE['remember_me'], 2);

    if (count($parts) === 2) {
        [$selector, $validator] = $parts;
        error_log('REMEMBER RESTORE: selector=' . $selector . ' validator_len=' . strlen($validator));

        $stmt = $pdo->prepare(
            'SELECT rt.user_id, rt.validator_hash, u.first_name, u.role, u.is_active
             FROM remember_tokens rt
             JOIN users u ON u.user_id = rt.user_id
             WHERE rt.selector = :selector AND rt.expires_at > NOW() LIMIT 1'
        );
        $stmt->execute([':selector' => $selector]);
        $row = $stmt->fetch();

        if (!$row) {
            error_log('REMEMBER RESTORE: no matching row found for selector (bad selector, or expired, or already deleted)');
        } else {
            $hashMatch = hash_equals($row['validator_hash'], hash('sha256', $validator));
            error_log('REMEMBER RESTORE: row found. hashMatch=' . var_export($hashMatch, true) . ' is_active=' . var_export($row['is_active'], true));
        }

        if ($row && hash_equals($row['validator_hash'], hash('sha256', $validator)) && (int) $row['is_active'] !== 0) {
            session_regenerate_id(true);
            $_SESSION['user_id']    = (int) $row['user_id'];
            $_SESSION['role']       = $row['role'];
            $_SESSION['first_name'] = $row['first_name'];
            error_log('REMEMBER RESTORE: session restored for user_id=' . $row['user_id']);
        } else {
            // Invalid or expired token — clear the stale cookie.
            error_log('REMEMBER RESTORE: FAILED validation, clearing cookie');
            setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
        }
    } else {
        error_log('REMEMBER RESTORE: cookie malformed, only ' . count($parts) . ' part(s)');
    }
} elseif (!empty($_SESSION['user_id'])) {
    // already logged in via normal session, nothing to do
} else {
    error_log('REMEMBER RESTORE: no session AND no remember_me cookie present at all');
}

/**
 * Require the current session to belong to one of the given roles.
 * Redirects to login if not authenticated, or shows 403 if wrong role.
 */
function require_role(array $allowedRoles): void
{
    if (empty($_SESSION['user_id']) || empty($_SESSION['role'])) {
        header('Location: /login.php');
        exit;
    }

    if (!in_array($_SESSION['role'], $allowedRoles, true)) {
        http_response_code(403);
        die('Access denied: insufficient privileges.');
    }
}

/**
 * Returns the college_id an employee is scoped to.
 * Every request-listing query for an employee MUST filter by this value.
 */
function current_employee_college_id(PDO $pdo): int
{
    if (($_SESSION['role'] ?? null) !== 'employee') {
        http_response_code(403);
        die('Access denied.');
    }

    $stmt = $pdo->prepare(
        'SELECT assigned_college_id FROM employees WHERE user_id = :uid LIMIT 1'
    );
    $stmt->execute([':uid' => $_SESSION['user_id']]);
    $row = $stmt->fetch();

    if (!$row) {
        http_response_code(403);
        die('Access denied: no college assignment found for this employee.');
    }

    return (int) $row['assigned_college_id'];
}

/** Simple CSRF token helpers */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_verify(string $token): void
{
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        die('Invalid CSRF token.');
    }
}

function log_activity(PDO $pdo, int $userId, string $role, string $action): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO activity_logs (user_id, role, action, ip_address) VALUES (:uid, :role, :action, :ip)'
    );
    $stmt->execute([
        ':uid'    => $userId,
        ':role'   => $role,
        ':action' => $action,
        ':ip'     => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ]);
}