<?php
declare(strict_types=1);

/**
 * CERTICHAIN - Database Connection
 * Plain PHP + PDO, prepared statements only.
 */

$DB_HOST = 'localhost';
$DB_NAME = 'certichain';
$DB_USER = 'root';       // change for your environment
$DB_PASS = '';           // change for your environment
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    // Never leak DB details to the client
    error_log('DB Connection Error: ' . $e->getMessage());
    http_response_code(500);
    die('A system error occurred. Please try again later.');
}
