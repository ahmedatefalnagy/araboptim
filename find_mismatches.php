<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

$data = Excel::toArray([], 'c:/xampp/htdocs/simple-accounting/الراجحي الرئيسي.xlsx')[0];
$header = array_shift($data);

$excelEntries = [];
foreach($data as $row) {
    if(empty($row[0])) continue;
    $dateValue = $row[0];
    $date = is_numeric($dateValue) ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d') : trim((string)$dateValue);
    if($date > '2025-09-30') continue;
    
    $excelEntries[] = [
        'date' => $date,
        'debit' => round((float)($row[3] ?? 0), 2),
        'credit' => round((float)($row[4] ?? 0), 2),
        'desc' => trim($row[2] ?? '')
    ];
}

$dbEntries = DB::table('journal_entry_lines')
    ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
    ->where('account_id', 8)
    ->where('journal_entries.entry_date', '<=', '2025-09-30')
    ->where('journal_entries.status', 'posted')
    ->get(['journal_entries.entry_date', 'journal_entry_lines.debit', 'journal_entry_lines.credit', 'journal_entries.description']);

$dbEntriesList = [];
foreach($dbEntries as $db) {
    $dbEntriesList[] = [
        'date' => $db->entry_date,
        'debit' => round((float)$db->debit, 2),
        'credit' => round((float)$db->credit, 2),
        'desc' => trim($db->description)
    ];
}

// Find entries in DB but not in Excel
$onlyInDB = [];
$matchedExcelIndices = [];

foreach($dbEntriesList as $db) {
    $found = false;
    foreach($excelEntries as $idx => $ex) {
        if(!isset($matchedExcelIndices[$idx]) && $ex['date'] == $db['date'] && $ex['debit'] == $db['debit'] && $ex['credit'] == $db['credit']) {
            $matchedExcelIndices[$idx] = true;
            $found = true;
            break;
        }
    }
    if(!$found) {
        $onlyInDB[] = $db;
    }
}

// Find entries in Excel but not in DB
$onlyInExcel = [];
foreach($excelEntries as $idx => $ex) {
    if(!isset($matchedExcelIndices[$idx])) {
        $onlyInExcel[] = $ex;
    }
}

echo "Entries ONLY in DB (" . count($onlyInDB) . "):\n";
foreach($onlyInDB as $o) {
    echo "Date: {$o['date']}, Debit: {$o['debit']}, Credit: {$o['credit']}, Desc: {$o['desc']}\n";
}

echo "\nEntries ONLY in Excel (" . count($onlyInExcel) . "):\n";
foreach($onlyInExcel as $o) {
    echo "Date: {$o['date']}, Debit: {$o['debit']}, Credit: {$o['credit']}, Desc: {$o['desc']}\n";
}

$sumDiffDB = array_sum(array_column($onlyInDB, 'debit')) - array_sum(array_column($onlyInDB, 'credit'));
$sumDiffExcel = array_sum(array_column($onlyInExcel, 'debit')) - array_sum(array_column($onlyInExcel, 'credit'));

echo "\nNet Difference of mismatches: " . ($sumDiffDB - $sumDiffExcel) . "\n";
