<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

echo "Row 0-10:\n";
for($i=0; $i<10; $i++) {
    echo "$i: " . implode(' | ', $rows[$i]) . "\n";
}
