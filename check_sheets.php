<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
echo "Sheets: " . implode(', ', $spreadsheet->getSheetNames()) . "\n";
foreach($spreadsheet->getSheetNames() as $name) {
    $sheet = $spreadsheet->getSheetByName($name);
    echo "Sheet '$name' has " . $sheet->getHighestRow() . " rows.\n";
}
