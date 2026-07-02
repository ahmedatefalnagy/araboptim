<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo_simple = new PDO("mysql:host=$host;dbname=simple_accounting;charset=utf8mb4", $user, $pass);
$pdo_arab = new PDO("mysql:host=$host;dbname=arab_db;charset=utf8mb4", $user, $pass);

function getTableStructure($pdo, $table) {
    $stmt = $pdo->query("SHOW CREATE TABLE `$table`");
    return $stmt->fetch(PDO::FETCH_ASSOC)['Create Table'];
}

echo "=== employees table in arab_db ===\n";
echo getTableStructure($pdo_arab, 'employees') . "\n\n";

echo "=== employees table in simple_accounting ===\n";
echo getTableStructure($pdo_simple, 'employees') . "\n\n";

echo "=== contacts table in arab_db ===\n";
echo getTableStructure($pdo_arab, 'contacts') . "\n\n";

echo "=== contacts table in simple_accounting ===\n";
echo getTableStructure($pdo_simple, 'contacts') . "\n\n";
