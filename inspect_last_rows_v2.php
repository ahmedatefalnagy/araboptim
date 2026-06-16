<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$highestRow = $sheet->getHighestRow();
echo "Highest Row: $highestRow\n";

for($i=$highestRow-10; $i<=$highestRow; $i++) {
    $row = $sheet->rangeToArray('A'.$i.':F'.$i)[0];
    echo "$i: " . implode(' | ', $row) . "\n";
}
