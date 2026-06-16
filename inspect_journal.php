<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$spreadsheet = IOFactory::load('journal.xlsx');
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

echo "Header:\n";
print_r($rows[0]);
echo "Row 1:\n";
print_r($rows[1]);
echo "Row 2:\n";
print_r($rows[2]);
