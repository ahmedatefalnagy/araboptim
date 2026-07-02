<?php

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass);
$dbs = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($dbs as $db) {
    if (in_array($db, ['information_schema', 'mysql', 'performance_schema', 'phpmyadmin'])) {
        continue;
    }
    try {
        $pdo->exec("USE `$db`");
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            if (preg_match('/vehicle|trip|route/i', $table)) {
                $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
                echo "DB: $db - Table: $table - Rows: $count\n";
            }
        }
    } catch (Exception $e) {
        echo "Error in $db: " . $e->getMessage() . "\n";
    }
}
