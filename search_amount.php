<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

foreach($rows as $i => $row) {
    if(strpos(str_replace(',', '', $row[3]), '200617.5') !== false || strpos(str_replace(',', '', $row[4]), '200617.5') !== false) {
        echo "Found at Row $i: " . implode(' | ', $row) . "\n";
    }
}
