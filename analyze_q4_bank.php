<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

$totalDebit = 0;
$totalCredit = 0;
foreach($rows as $i => $row) {
    if($i < 3) continue;
    $date = $row[0];
    if(empty($date)) continue;
    if($date >= '2025-10-01') {
        $totalDebit += (float)str_replace(',', '', $row[3]);
        $totalCredit += (float)str_replace(',', '', $row[4]);
    }
}
echo "Excel Q4 (الراجحي الرئيسي) -> Debit: $totalDebit, Credit: $totalCredit, Net: " . ($totalDebit - $totalCredit) . "\n";
