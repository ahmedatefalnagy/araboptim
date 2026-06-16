<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

$debits = 0;
$credits = 0;
$opening_balances = [];

foreach ($data as $row) {
    $code = trim($row['H'] ?? '');
    $name = trim($row['G'] ?? '');
    $dr = floatval(str_replace(',', '', $row['B'] ?? '0'));
    $cr = floatval(str_replace(',', '', $row['A'] ?? '0'));

    // We only care about accounts starting with 1, 2, 3 (Balance Sheet)
    // AND accounts starting with 4 (Income Statement - to calculate profit)
    if (is_numeric($code) && strlen($code) > 1) {
        if (str_starts_with($code, '1') || str_starts_with($code, '2') || str_starts_with($code, '3')) {
            // Leaf accounts (not total accounts) - usually 4+ digits
            if (strlen($code) >= 4) {
                if ($dr != 0 || $cr != 0) {
                    $opening_balances[] = ['code' => $code, 'name' => $name, 'dr' => $dr, 'cr' => $cr];
                    $debits += $dr;
                    $credits += $cr;
                }
            }
        }
    }
}

echo "Total Dr: " . number_format($debits, 2) . "\n";
echo "Total Cr: " . number_format($credits, 2) . "\n";
echo "Difference: " . number_format($debits - $credits, 2) . "\n";
