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

$backupFile = 'c:/xampp/htdocs/simple-accounting/_backups/2026-06-15_215440/simple_accounting_db.sql';
if (!file_exists($backupFile)) {
    die("Backup file not found.\n");
}

$content = file_get_contents($backupFile);

$tablesToRestore = ['vehicles', 'trips', 'trip_diesels'];

// Temporarily disable foreign keys
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

foreach ($tablesToRestore as $table) {
    // Truncate first to prevent duplicate key errors or clean up
    $pdo->exec("TRUNCATE TABLE `$table`");
    echo "Truncated table `$table`.\n";

    if (preg_match_all('/INSERT INTO `' . $table . '`[^;]+;/', $content, $matches)) {
        foreach ($matches[0] as $insertQuery) {
            $pdo->exec($insertQuery);
            echo "Executed INSERT for `$table`.\n";
        }
    } else {
        echo "No INSERT statements found for `$table` in backup.\n";
    }
}

// Let's also restore trip_routes if they were lost
$logisticSqlFile = 'c:/xampp/htdocs/logistc-accounting/logistc-accounting.sql';
if (file_exists($logisticSqlFile)) {
    $logContent = file_get_contents($logisticSqlFile);
    if (preg_match_all('/INSERT INTO `trip_routes`[^;]+;/', $logContent, $matches)) {
        $pdo->exec("TRUNCATE TABLE `trip_routes`");
        echo "Truncated table `trip_routes`.\n";
        foreach ($matches[0] as $insertQuery) {
            $pdo->exec($insertQuery);
            echo "Executed INSERT for `trip_routes` from logistics sql dump.\n";
        }
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "Restoration complete!\n";
