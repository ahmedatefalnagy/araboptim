<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=simple_accounting;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to simple_accounting.\n";
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage() . "\n");
}

$sqlFile = 'C:/Users/AHMED/Downloads/simple_accounting (1).sql';
if (!file_exists($sqlFile)) {
    die("Downloads SQL file not found.\n");
}

$content = file_get_contents($sqlFile);

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

// Truncate vehicles table
$pdo->exec("TRUNCATE TABLE `vehicles`");
echo "Truncated `vehicles` table.\n";

// Find vehicles INSERT statements
if (preg_match_all('/INSERT INTO `vehicles`[^;]+;/', $content, $matches)) {
    foreach ($matches[0] as $query) {
        $pdo->exec($query);
        echo "Executed vehicles INSERT query successfully.\n";
    }
} else {
    echo "No vehicles INSERT statements found in Downloads SQL file.\n";
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// Check current rows
$count = $pdo->query("SELECT COUNT(*) FROM `vehicles`")->fetchColumn();
echo "Currently there are $count vehicles in the database simple_accounting.\n";
