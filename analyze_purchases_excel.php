<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي المشتريات.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

echo "Rows: " . count($rows) . "\n";
echo "Header: " . implode(' | ', $rows[0]) . "\n";
echo "First row: " . implode(' | ', $rows[1]) . "\n";
echo "Last row: " . implode(' | ', end($rows)) . "\n";

foreach($rows as $i => $row) {
    if($i < 3) continue;
    if(strpos($row[0], '10/') !== false || strpos($row[0], '11/') !== false || strpos($row[0], '12/') !== false) {
        echo "Q4 Row $i: " . implode(' | ', $row) . "\n";
    }
}
