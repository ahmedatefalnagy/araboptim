<?php

// Check databases: simple_accounting vs arab_db
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    $pdo_simple = new PDO("mysql:host=$host;dbname=simple_accounting;charset=utf8mb4", $user, $pass);
    $pdo_simple->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to simple_accounting successfully.\n";
} catch (Exception $e) {
    echo "Failed to connect to simple_accounting: " . $e->getMessage() . "\n";
    $pdo_simple = null;
}

try {
    $pdo_arab = new PDO("mysql:host=$host;dbname=arab_db;charset=utf8mb4", $user, $pass);
    $pdo_arab->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to arab_db successfully.\n";
} catch (Exception $e) {
    echo "Failed to connect to arab_db: " . $e->getMessage() . "\n";
    $pdo_arab = null;
}

function showTableStats($pdo, $dbName) {
    if (!$pdo) return;
    echo "\n--- Tables in $dbName ---\n";
    try {
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            if (preg_match('/employee|contact/i', $table)) {
                $countStmt = $pdo->query("SELECT COUNT(*) FROM `$table`");
                $count = $countStmt->fetchColumn();
                echo "Table: $table - Rows: $count\n";
            }
        }
    } catch (Exception $e) {
        echo "Error in $dbName: " . $e->getMessage() . "\n";
    }
}

showTableStats($pdo_simple, 'simple_accounting');
showTableStats($pdo_arab, 'arab_db');
