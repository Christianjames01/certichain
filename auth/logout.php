<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php'; // $pdo

// Revoke the remember-me token, if any, so a stale cookie can't
// silently log the user back in after they've explicitly logged out.
if (!empty($_COOKIE['remember_me'])) {
    $parts = explode(':', $_COOKIE['remember_me'], 2);
    if (count($parts) === 2) {
        [$selector] = $parts;
        $pdo->prepare('DELETE FROM remember_tokens WHERE selector = :selector')
            ->execute([':selector' => $selector]);
    }
    setcookie('remember_me', '', ['expires' => time() - 3600, 'path' => '/']);
}

$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();

header('Location: ../index.php');
exit;