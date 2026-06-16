<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

echo "Code | Name | Closing Dr | Closing Cr\n";
echo "--------------------------------------\n";
foreach ($data as $row) {
    $code = trim($row['H'] ?? '');
    $name = $row['G'] ?? '';
    
    // Search for Fixed Assets (1201) and Accumulated Depr (maybe 1202 or containing "مجمع")
    if (str_starts_with($code, '12') || str_contains($name, 'مجمع') || str_contains($name, 'إهلاك')) {
        echo "$code | $name | " . ($row['E'] ?? '0') . " | " . ($row['F'] ?? '0') . "\n";
    }
}
