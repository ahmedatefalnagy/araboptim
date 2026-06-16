<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

echo "Code | Name | Amount (B) | Col A | Col C | Col D\n";
echo "--------------------------------------------------\n";
foreach ($data as $row) {
    $code = trim($row['H'] ?? '');
    $name = $row['G'] ?? '';
    $amount = $row['B'] ?? '0';
    
    if (str_starts_with($code, '1') || str_starts_with($code, '2') || str_starts_with($code, '3')) {
        if (floatval(str_replace(',', '', $amount)) != 0) {
            echo "$code | $name | $amount | " . ($row['A'] ?? '0') . " | " . ($row['C'] ?? '0') . " | " . ($row['D'] ?? '0') . "\n";
        }
    }
}
