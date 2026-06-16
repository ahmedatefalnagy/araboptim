<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

$data = Excel::toArray([], 'c:/xampp/htdocs/simple-accounting/الراجحي الرئيسي.xlsx')[0];

echo "Excel Structure (First 5 rows):\n";
foreach(array_slice($data, 0, 5) as $row) {
    echo json_encode($row) . "\n";
}

$excelEntries = [];
$header = array_shift($data); // Skip header

foreach($data as $row) {
    if(empty($row[0])) continue; // Skip empty date
    
    $dateValue = $row[0];
    $date = is_numeric($dateValue) 
        ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d')
        : trim((string)$dateValue);
    
    if($date > '2025-09-30') continue;

    $debit = (float)($row[3] ?? 0);
    $credit = (float)($row[4] ?? 0);
    $desc = $row[2] ?? '';

    $excelEntries[] = [
        'date' => $date,
        'debit' => $debit,
        'credit' => $credit,
        'desc' => $desc
    ];
}

echo "\nTotal Excel Entries (up to 2025-09-30): " . count($excelEntries) . "\n";

// DB Entries
$dbEntries = DB::table('journal_entry_lines')
    ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
    ->where('account_id', 8)
    ->where('journal_entries.entry_date', '<=', '2025-09-30')
    ->where('journal_entries.status', 'posted')
    ->get(['journal_entries.entry_date', 'journal_entry_lines.debit', 'journal_entry_lines.credit', 'journal_entries.description']);

echo "Total DB Entries (up to 2025-09-30): " . $dbEntries->count() . "\n";

$excelTotalDebit = array_sum(array_column($excelEntries, 'debit'));
$excelTotalCredit = array_sum(array_column($excelEntries, 'credit'));
$dbTotalDebit = $dbEntries->sum('debit');
$dbTotalCredit = $dbEntries->sum('credit');

echo "\nSummary Comparison:\n";
echo "Excel: Debit $excelTotalDebit, Credit $excelTotalCredit, Balance " . ($excelTotalDebit - $excelTotalCredit) . "\n";
echo "DB:    Debit $dbTotalDebit, Credit $dbTotalCredit, Balance " . ($dbTotalDebit - $dbTotalCredit) . "\n";
