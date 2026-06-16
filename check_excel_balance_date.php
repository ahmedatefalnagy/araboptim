<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

$totalDebit = 0;
$totalCredit = 0;
$targetDate = '2025-09-30';
$found = false;

foreach($rows as $i => $row) {
    if($i < 3) continue;
    $date = $row[0];
    if(empty($date)) continue;
    
    // Check if we passed the date
    if($date > $targetDate) {
        break;
    }

    $totalDebit += (float)str_replace(',', '', $row[3]);
    $totalCredit += (float)str_replace(',', '', $row[4]);
    
    if($date == $targetDate) $found = true;
}

echo "Excel Balance up to $targetDate: " . ($totalDebit - $totalCredit) . "\n";
if(!$found) echo "Target date $targetDate not found exactly, showing up to last available before it.\n";
