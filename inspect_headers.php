<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

// Print the first 15 rows to see headers
foreach (array_slice($data, 0, 15) as $idx => $row) {
    echo "Row $idx: ";
    foreach ($row as $col => $val) {
        if ($val) echo "[$col] => $val | ";
    }
    echo "\n";
}
