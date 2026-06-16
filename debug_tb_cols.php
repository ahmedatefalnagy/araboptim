<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

// Print the first row with data (usually headers or first account)
foreach ($data as $index => $row) {
    if ($index > 5 && array_filter($row)) {
        print_r($row);
        break;
    }
}
