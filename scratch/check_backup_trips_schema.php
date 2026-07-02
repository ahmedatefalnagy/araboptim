<?php

$sqlFile = 'c:/xampp/htdocs/simple-accounting/_backups/2026-06-15_215440/simple_accounting_db.sql';
$content = file_get_contents($sqlFile);

if (preg_match('/CREATE TABLE `trips` \((.*?)\) ENGINE/s', $content, $matches)) {
    echo $matches[0] . "\n";
} else {
    echo "Not found\n";
}
