<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

foreach ($data as $row) {
    if (isset($row['A']) && str_starts_with($row['A'], '12')) {
        echo $row['A'] . " | " . $row['B'] . " | Closing: " . ($row['G'] ?? '0') . " (Dr) / " . ($row['H'] ?? '0') . " (Cr)\n";
    }
}
