<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

echo "Last 10 Rows:\n";
$count = count($rows);
for($i=$count-10; $i<$count; $i++) {
    echo "$i: " . implode(' | ', $rows[$i]) . "\n";
}
