<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

$q4Rows = [];
foreach($rows as $i => $row) {
    if($i < 3) continue;
    $rawDate = $row[0];
    if(empty($rawDate)) continue;
    try {
        $date = \Carbon\Carbon::parse($rawDate);
        if($date->greaterThanOrEqualTo('2025-10-01')) {
            $q4Rows[] = $row;
        }
    } catch (\Exception $e) {}
}

echo "Found " . count($q4Rows) . " rows in Q4 for Main Bank.\n";
file_put_contents('q4_main_bank_data.json', json_encode($q4Rows));
