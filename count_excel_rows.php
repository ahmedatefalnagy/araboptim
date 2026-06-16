<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('journal.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();
echo "Total Rows in Excel: " . count($rows) . "\n";
echo "Header: " . implode(', ', $rows[0]) . "\n";

$importedLines = 0;
// Simulate grouping to see what fails
$entries = [];
for($i=1; $i<count($rows); $i++) {
    if(empty($rows[$i][0])) continue;
    $importedLines++;
}
echo "Data Rows (excluding header/empty): $importedLines\n";
