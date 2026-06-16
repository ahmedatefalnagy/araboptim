<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

echo "Code | Name | A | B | C | D | E | F\n";
echo "------------------------------------------\n";
foreach ($data as $row) {
    $code = trim($row['H'] ?? '');
    $name = $row['G'] ?? '';
    
    // Check if any value column has data
    if (floatval(str_replace(',', '', $row['A'] ?? '0')) != 0 || 
        floatval(str_replace(',', '', $row['B'] ?? '0')) != 0 ||
        floatval(str_replace(',', '', $row['C'] ?? '0')) != 0 ||
        floatval(str_replace(',', '', $row['D'] ?? '0')) != 0) {
        
        echo "$code | $name | " . ($row['A'] ?? '0') . " | " . ($row['B'] ?? '0') . " | " . ($row['C'] ?? '0') . " | " . ($row['D'] ?? '0') . " | " . ($row['E'] ?? '0') . " | " . ($row['F'] ?? '0') . "\n";
    }
}
