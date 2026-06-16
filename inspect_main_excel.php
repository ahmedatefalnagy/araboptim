<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

echo "Row 0: " . implode(' | ', $rows[0]) . "\n";
echo "Row 1: " . implode(' | ', $rows[1]) . "\n";
echo "Row 2: " . implode(' | ', $rows[2]) . "\n";
echo "Row 3: " . implode(' | ', $rows[3]) . "\n";
echo "Row 4: " . implode(' | ', $rows[4]) . "\n";
