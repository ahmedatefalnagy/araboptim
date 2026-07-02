<?php

$sqlFile = 'c:/xampp/htdocs/logistc-accounting/logistc-accounting.sql';
if (!file_exists($sqlFile)) {
    die("SQL file does not exist at $sqlFile\n");
}

$content = file_get_contents($sqlFile);

$tables = ['vehicles', 'trips', 'trip_routes', 'trip_diesels', 'trip_stops', 'trip_events', 'trip_sub_clients'];

foreach ($tables as $table) {
    if (preg_match_all('/INSERT INTO `' . $table . '`[^;]+;/', $content, $matches)) {
        echo "Table: $table - Found " . count($matches[0]) . " INSERT statement(s).\n";
        // Show first 500 chars of first match
        echo "Sample: " . substr($matches[0][0], 0, 500) . "...\n\n";
    } else {
        echo "Table: $table - NOT found in SQL dump.\n";
    }
}
