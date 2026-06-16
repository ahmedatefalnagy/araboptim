<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

echo "Code | Name | Opening Debit (2025) | Opening Credit (2025)\n";
echo "------------------------------------------------------------\n";

foreach ($data as $row) {
    $code = trim($row['H'] ?? '');
    $name = trim($row['G'] ?? '');
    $debit = floatval(str_replace(',', '', $row['B'] ?? '0'));
    $credit = floatval(str_replace(',', '', $row['A'] ?? '0'));

    // Only include leaf accounts (is_postable) or specific levels if possible
    // For now, we include all accounts with balances
    if ($debit != 0 || $credit != 0) {
        // Skip header rows or summary rows if they are not actual accounts
        if (is_numeric($code) && strlen($code) > 1) {
            echo "$code | $name | " . number_format($debit, 2) . " | " . number_format($credit, 2) . "\n";
        }
    }
}
