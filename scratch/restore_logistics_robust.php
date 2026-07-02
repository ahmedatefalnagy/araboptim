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

$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

// 1. Truncate & Restore vehicles
$pdo->exec("TRUNCATE TABLE `vehicles`");
if (preg_match_all('/INSERT INTO `vehicles`[^;]+;/', $content, $matches)) {
    foreach ($matches[0] as $query) {
        $pdo->exec($query);
        echo "Restored vehicles.\n";
    }
}

// 2. Parse & Restore trips mapping columns manually
$pdo->exec("TRUNCATE TABLE `trips`");

$backupColumns = [
    'id', 'trip_no', 'route_id', 'waybill_no', 'vehicle_id', 'driver_id', 'broker_id',
    'end_customer_name', 'cargo_type', 'weight', 'container_no', 'origin', 'destination',
    'loading_site', 'discharge_site', 'doc_no', 'status', 'etd', 'eta', 'eta_unloading',
    'actual_arrival', 'actual_loading_start', 'actual_loading_end', 'actual_unloading_start',
    'actual_unloading_end', 'start_km', 'end_km', 'fuel_amount', 'diesel_liters', 'fuel_cost',
    'broker_price', 'driver_commission', 'loading_invoice_path', 'delivery_invoice_path',
    'is_commission_paid', 'total_trip_budget', 'initial_diesel_amount', 'invoice_id', 'notes',
    'stop_count', 'created_at', 'updated_at'
];

if (preg_match('/INSERT INTO `trips` VALUES\s*\((.*?)\);/s', $content, $matches)) {
    // Parse the values safely using str_getcsv or manual parsing since values might have quotes
    // But since it's a single SQL line, let's write a simple SQL runner by preparing a statement
    $valString = $matches[1];
    
    // We can also extract the values by executing a temp query or parsing
    // Let's parse the values. In simple_accounting_db.sql, we have:
    // (1,'TRIP-999',NULL,'WB-888',1,17,1,'مصنع الخليج للخرسانة',NULL,NULL,NULL,'الدمام - ميناء الملك عبد العزيز','الدمام','رصيف رقم 5','موقع الإنشاء الرئيسي',NULL,'completed',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0.00,0.00,NULL,0.00,0.00,250.00,NULL,NULL,0,1500.00,0.00,NULL,'يرجى تحميل شحنة الحديد وتأكيد التحميل برفع الفاتورة.',0,'2026-06-12 13:27:42','2026-06-15 08:56:06')
    // We can replace NULL with null, and then decode/parse it.
    // Or we can construct the exact SQL statement by replacing the table name and specifying the columns:
    $columnsJoined = '`' . implode('`, `', $backupColumns) . '`';
    $customQuery = "INSERT INTO `trips` ($columnsJoined) VALUES ($valString);";
    
    try {
        $pdo->exec($customQuery);
        echo "Restored trips successfully using column mapping.\n";
    } catch (Exception $ex) {
        echo "Error restoring trips: " . $ex->getMessage() . "\n";
    }
} else {
    echo "No trips found in backup.\n";
}

// 3. Truncate & Restore trip_diesels
$pdo->exec("TRUNCATE TABLE `trip_diesels`");
if (preg_match_all('/INSERT INTO `trip_diesels`[^;]+;/', $content, $matches)) {
    foreach ($matches[0] as $query) {
        $pdo->exec($query);
        echo "Restored trip_diesels.\n";
    }
}

// 4. Truncate & Restore trip_routes if found in logistics sql
$logisticSqlFile = 'c:/xampp/htdocs/logistc-accounting/logistc-accounting.sql';
if (file_exists($logisticSqlFile)) {
    $logContent = file_get_contents($logisticSqlFile);
    if (preg_match_all('/INSERT INTO `trip_routes`[^;]+;/', $logContent, $matches)) {
        $pdo->exec("TRUNCATE TABLE `trip_routes`");
        foreach ($matches[0] as $query) {
            $pdo->exec($query);
            echo "Restored trip_routes from logistics backup.\n";
        }
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
echo "Done!\n";
