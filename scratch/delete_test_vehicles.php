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

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

// IDs to delete based on the plate numbers:
// 1 -> 'أ ب ج 1234'
// 2 -> '1234 ABC'
// 3 -> 'ا ق ب 1111'
$targetIds = [1, 2, 3];

// Let's delete any trips referencing these test vehicles
$stmtTrips = $pdo->prepare("DELETE FROM `trips` WHERE `vehicle_id` IN (?, ?, ?)");
$stmtTrips->execute($targetIds);
echo "Deleted test trips referencing these vehicles.\n";

// Let's delete any trip diesels or other child references
$stmtDiesels = $pdo->prepare("DELETE FROM `trip_diesels` WHERE `trip_id` NOT IN (SELECT id FROM trips)");
$stmtDiesels->execute();
echo "Cleaned up orphaned trip diesels.\n";

// Now delete the vehicles
$stmtVehicles = $pdo->prepare("DELETE FROM `vehicles` WHERE `id` IN (?, ?, ?)");
$stmtVehicles->execute($targetIds);
echo "Deleted test vehicles from database.\n";

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

// Verify remaining vehicles
$stmt = $pdo->query("SELECT id, plate_no, model FROM `vehicles`");
$remaining = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\nRemaining Vehicles count: " . count($remaining) . "\n";
foreach ($remaining as $v) {
    echo "ID: {$v['id']} - Plate: {$v['plate_no']} - Model: {$v['model']}\n";
}
