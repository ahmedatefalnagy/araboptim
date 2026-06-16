<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();
echo "Rows: " . count($rows) . "\n";
echo "Header: " . implode(' | ', $rows[0]) . "\n";
echo "First row: " . implode(' | ', $rows[1]) . "\n";
echo "Last row: " . implode(' | ', end($rows)) . "\n";

$totalDebit = 0;
$totalCredit = 0;
foreach($rows as $i => $row) {
    if($i == 0) continue;
    $totalDebit += (float)$row[3];
    $totalCredit += (float)$row[4];
}
echo "Excel Balance: " . ($totalDebit - $totalCredit) . "\n";
