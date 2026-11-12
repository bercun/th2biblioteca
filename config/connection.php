<?php

/**
 * Database connection (PDO singleton).
 */
function getConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    // Primary settings: environment variables > config/db.local.php (git-ignored) > defaults
    $local = is_file(__DIR__ . '/db.local.php') ? require __DIR__ . '/db.local.php' : [];

    $primary = [
        'host' => getenv('DB_HOST') ?: ($local['host'] ?? 'localhost'),
        'port' => getenv('DB_PORT') ?: ($local['port'] ?? '3306'),
        'dbname' => getenv('DB_NAME') ?: ($local['dbname'] ?? 'biblioteca'),
        'username' => getenv('DB_USER') ?: ($local['username'] ?? 'root'),
        'password' => getenv('DB_PASS') !== false ? getenv('DB_PASS') : ($local['password'] ?? ''),
    ];

    // Fallback for local development: default XAMPP MySQL (root, no password).
    // Used only when the primary settings cannot connect.
    $fallback = [
        'host' => 'localhost',
        'port' => '3306',
        'dbname' => $primary['dbname'],
        'username' => 'root',
        'password' => '',
    ];

    $candidates = [$primary];
    if ($fallback !== $primary) {
        $candidates[] = $fallback;
    }

    foreach ($candidates as $config) {
        try {
            $pdo = new PDO(
                "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4",
                $config['username'],
                $config['password']
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            return $pdo;
        } catch (PDOException $e) {
            error_log('DB connection failed for user ' . $config['username'] . ': ' . $e->getMessage());
        }
    }

    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}
