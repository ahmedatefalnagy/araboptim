<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo_simple = new PDO("mysql:host=$host;dbname=simple_accounting;charset=utf8mb4", $user, $pass);
$pdo_arab = new PDO("mysql:host=$host;dbname=arab_db;charset=utf8mb4", $user, $pass);

function checkLogisticsTables($pdo, $dbName) {
    echo "\n=== Tables in $dbName ===\n";
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $table) {
        if (preg_match('/vehicle|trip|route|driver|logistics|truck/i', $table)) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
            echo "Table: $table - Rows: $count\n";
        }
    }
}

checkLogisticsTables($pdo_simple, 'simple_accounting');
checkLogisticsTables($pdo_arab, 'arab_db');
