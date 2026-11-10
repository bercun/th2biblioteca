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

    // Settings: environment variables > config/db.local.php (git-ignored) > defaults
    $local = is_file(__DIR__ . '/db.local.php') ? require __DIR__ . '/db.local.php' : [];

    $host = getenv('DB_HOST') ?: ($local['host'] ?? 'localhost');
    $port = getenv('DB_PORT') ?: ($local['port'] ?? '3306');
    $dbname = getenv('DB_NAME') ?: ($local['dbname'] ?? 'biblioteca');
    $username = getenv('DB_USER') ?: ($local['username'] ?? 'root');
    $password = getenv('DB_PASS');
    if ($password === false) {
        $password = $local['password'] ?? '';
    }

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4",
            $username,
            $password
        );

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Database connection error'
        ]);
        exit;
    }

    return $pdo;
}
