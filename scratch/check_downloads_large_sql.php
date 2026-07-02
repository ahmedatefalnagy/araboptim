<?php

$sqlFile = 'C:/Users/AHMED/Downloads/simple_accounting.sql';
if (!file_exists($sqlFile)) {
    die("File not found: $sqlFile\n");
}
$content = file_get_contents($sqlFile);

$tables = ['vehicles', 'trips', 'trip_diesels', 'trip_routes', 'trip_stops', 'trip_events', 'trip_sub_clients'];

foreach ($tables as $table) {
    if (preg_match_all('/INSERT INTO `' . $table . '`[^;]+;/', $content, $matches)) {
        echo "Table: $table - Found " . count($matches[0]) . " INSERT statement(s).\n";
        // Show approximate row count
        $rowCount = preg_match_all('/\([^)]+\)/', $matches[0][0], $dummy);
        echo "Estimated rows: $rowCount\n";
    } else {
        echo "Table: $table - NOT found.\n";
    }
}
