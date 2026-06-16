<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'journal_entries_2026-05-11.xlsx';
$spreadsheet = IOFactory::load($file);
$sheets = $spreadsheet->getSheetNames();
print_r($sheets);

$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);
print_r(array_slice($data, 0, 10));
