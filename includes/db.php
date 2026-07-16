<?php
$configFile = __DIR__ . '/db.config.php';
if (!file_exists($configFile)) {
    die('Konfigurasi database tidak ditemukan. Salin includes/db.config.example.php menjadi includes/db.config.php lalu isi kredensialnya.');
}
require $configFile;

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
if (DB_PORT !== '') {
    $dsn .= ';port=' . DB_PORT;
}

try {
    $pdo = new PDO(
        $dsn,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    $pdo = null;
}