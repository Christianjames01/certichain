<?php
declare(strict_types=1);

/**
 * CERTICHAIN - Authentication & Authorization Helpers
 */

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict',
]);

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
