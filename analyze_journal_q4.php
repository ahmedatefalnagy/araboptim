<?php
require 'vendor/autoload.php';
$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('journal.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

$totalDebit = 0;
$totalCredit = 0;
foreach($rows as $i => $row) {
    if($i == 0) continue;
    if(trim($row[3]) == '1121') {
        $totalDebit += (float)$row[4];
        $totalCredit += (float)$row[5];
    }
}
echo "journal.xlsx Q4 (1121) -> Debit: $totalDebit, Credit: $totalCredit, Net: " . ($totalDebit - $totalCredit) . "\n";
