<?php
/**
 * WorkPulse Pro — Database Configuration
 * Handles PDO connection + session bootstrap.
 */

declare(strict_types=1);

define('DB_HOST', 'localhost');
define('DB_NAME', 'workpulse_pro');
define('DB_USER', 'root');         // Change for production
define('DB_PASS', '');             // Change for production
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'WorkPulse Pro');
define('CURRENCY', '₹');

/* ── Session ─────────────────────────────────────────── */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => false,   // Set true when behind HTTPS
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ── PDO Connection ──────────────────────────────────── */
function getPDO(): PDO
{
    static $pdo = null;
    if ($pdo !== null) {
        return $pdo;
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        DB_HOST, DB_NAME, DB_CHARSET
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // Never expose real errors in production
        error_log('DB Connection failed: ' . $e->getMessage());
        http_response_code(500);
        die('<p style="font-family:sans-serif;color:#c0392b;padding:2rem;">
             Database connection error. Please contact your administrator.</p>');
    }

    return $pdo;
}
