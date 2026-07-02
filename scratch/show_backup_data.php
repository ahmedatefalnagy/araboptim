<?php

$sqlFile = 'c:/xampp/htdocs/simple-accounting/_backups/2026-06-15_215440/simple_accounting_db.sql';
$content = file_get_contents($sqlFile);

$tables = ['vehicles', 'trips', 'trip_diesels', 'trip_routes'];

foreach ($tables as $table) {
    if (preg_match_all('/INSERT INTO `' . $table . '`[^;]+;/', $content, $matches)) {
        echo "=== Data for $table ===\n";
        echo $matches[0][0] . "\n\n";
    } else {
        echo "=== No data for $table ===\n\n";
    }
}
