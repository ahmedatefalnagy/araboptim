<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

$totalDebit = 0;
$totalCredit = 0;
foreach($rows as $i => $row) {
    if($i < 3) continue;
    $rawDate = $row[0];
    if(empty($rawDate)) continue;
    
    try {
        $date = \Carbon\Carbon::parse($rawDate);
        if($date->greaterThanOrEqualTo('2025-10-01')) {
            $totalDebit += (float)str_replace(',', '', $row[3]);
            $totalCredit += (float)str_replace(',', '', $row[4]);
        }
    } catch (\Exception $e) {}
}
echo "Excel Q4 (الراجحي الرئيسي) -> Debit: $totalDebit, Credit: $totalCredit, Net: " . ($totalDebit - $totalCredit) . "\n";
