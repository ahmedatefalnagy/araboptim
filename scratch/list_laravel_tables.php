<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=laravel;charset=utf8mb4", $user, $pass);
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "=== Tables in laravel database ===\n";
    print_r($tables);
} catch (Exception $e) {
    echo "Error connecting to laravel: " . $e->getMessage() . "\n";
}
