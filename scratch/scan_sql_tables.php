<?php

$sqlFiles = [
    'c:/xampp/htdocs/simple-accounting/simple_accounting (1).sql',
    'c:/xampp/htdocs/simple-accounting/simple_accounting (5).sql',
    'c:/xampp/htdocs/logistc-accounting/logistc-accounting.sql'
];

foreach ($sqlFiles as $file) {
    if (!file_exists($file)) continue;
    echo "=== Scanning $file ===\n";
    $content = file_get_contents($file);
    
    // Search for insert into statements that contain vehicle or truck or table names
    if (preg_match_all('/INSERT INTO `([^`]+)`/i', $content, $matches)) {
        $uniqueTables = array_unique($matches[1]);
        echo "Found tables in INSERT statements: " . implode(', ', $uniqueTables) . "\n";
        foreach ($uniqueTables as $tbl) {
            if (preg_match('/vehicle|truck|car|fleet|driver|trip/i', $tbl)) {
                // Find all matching inserts
                preg_match_all('/INSERT INTO `' . $tbl . '`[^;]+;/', $content, $inserts);
                echo "  -> Table '$tbl' has " . count($inserts[0]) . " INSERT statement(s).\n";
                // Let's see count of rows in the first insert
                if (count($inserts[0]) > 0) {
                    $rowMatches = preg_match_all('/\([^)]+\)/', $inserts[0][0], $rows);
                    echo "     Approximate rows: " . count($rows[0]) . "\n";
                }
            }
        }
    }
}
