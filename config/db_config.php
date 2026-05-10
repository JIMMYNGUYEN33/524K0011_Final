<?php
$isLocal = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true);

if ($isLocal) {
    $host = 'localhost';
    $dbname = 'e_wallet';
    $username = 'root';
    $password = '';
} else {
    $host = 'sql102.infinityfree.com';
    $dbname = 'if0_41871398_e_wallet';
    $username = 'if0_41871398';
    $password = 'BeePay2026';
}

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
