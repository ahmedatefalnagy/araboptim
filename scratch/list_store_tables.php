<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=store_db;charset=utf8mb4", $user, $pass);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "=== Tables in store_db database ===\n";
    print_r($tables);
} catch (Exception $e) {
    echo "Error connecting to store_db: " . $e->getMessage() . "\n";
}
