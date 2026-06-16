<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('journal.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

echo "Problematic Rows Analysis:\n";
foreach($rows as $i => $row) {
    if(strpos($row[2], 'أنماط المستقبل') !== false || strpos($row[2], '1021') !== false || strpos($row[2], '1022') !== false) {
        echo "Row $i: " . implode(' | ', $row) . "\n";
    }
}
