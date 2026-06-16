<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

function getExcelStats($fileName) {
    $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
    $rows = $spreadsheet->getActiveSheet()->toArray();
    $totalDebit = 0;
    $totalCredit = 0;
    $finalBalanceExcel = 0;
    
    foreach ($rows as $i => $row) {
        if ($i < 3) continue;
        if (empty($row[0])) continue;
        
        $debit = (float)str_replace(',', '', $row[3] ?? 0);
        $credit = (float)str_replace(',', '', $row[4] ?? 0);
        $totalDebit += $debit;
        $totalCredit += $credit;
        $finalBalanceExcel = (float)str_replace(',', '', $row[5] ?? 0);
    }
    
    return [
        'sum_diff' => $totalDebit - $totalCredit,
        'final_excel' => $finalBalanceExcel
    ];
}

$banks = [
    '1121' => 'الراجحي الرئيسي.xlsx',
    '1122' => 'الراجحي المشتريات.xlsx',
    '1123' => 'الراجحي الادارة.xlsx'
];

foreach ($banks as $code => $file) {
    $stats = getExcelStats($file);
    $openingNeeded = $stats['final_excel'] - $stats['sum_diff'];
    echo "Account $code: Excel Sum Diff = {$stats['sum_diff']}, Final Excel = {$stats['final_excel']}, Opening Balance Needed = $openingNeeded\n";
}
