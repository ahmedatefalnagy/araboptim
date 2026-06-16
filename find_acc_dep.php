<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

foreach ($data as $row) {
    if (isset($row['B']) && (str_contains($row['B'], 'مجمع') || str_contains($row['B'], 'إهلاك'))) {
        echo ($row['A'] ?? 'N/A') . " | " . $row['B'] . " | Closing: " . ($row['G'] ?? '0') . " (Dr) / " . ($row['H'] ?? '0') . " (Cr)\n";
    }
}
