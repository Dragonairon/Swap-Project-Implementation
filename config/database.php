<?php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'project_database';
$DB_USER = 'root';
$DB_PASS = '';
$DB_PORT = 3306;

try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;port=$DB_PORT;dbname=$DB_NAME;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // ✅ Force MySQL session timezone to Singapore (UTC+8)
    $pdo->exec("SET time_zone = '+08:00'");

} catch (Exception $e) {
    http_response_code(500);
    echo "Database connection failed.";
    exit;
}
