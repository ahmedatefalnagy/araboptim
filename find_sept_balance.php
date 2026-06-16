<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

foreach($rows as $i => $row) {
    if(strpos($row[0], '09/30') !== false || strpos($row[0], '2025-09-30') !== false) {
        echo "Row $i: Date: {$row[0]}, Desc: {$row[2]}, Balance: {$row[5]}\n";
    }
}
