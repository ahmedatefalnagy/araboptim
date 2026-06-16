<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Find the wrong-date entry
echo "=== القيد الخاطئ التاريخ (2005) ===\n";
$wrongEntry = DB::table('journal_entries')
    ->where('entry_date', '<', '2024-01-01')
    ->select('*')
    ->get();

foreach ($wrongEntry as $e) {
    echo "ID: {$e->id} | Entry No: {$e->entry_no} | Date: {$e->entry_date} | Status: {$e->status} | Desc: {$e->description}\n";
    
    $lines = DB::table('journal_entry_lines as jel')
        ->join('accounts as a', 'a.id', '=', 'jel.account_id')
        ->where('jel.journal_entry_id', $e->id)
        ->select('a.code', 'a.name', 'jel.debit', 'jel.credit', 'jel.description')
        ->get();
    
    foreach ($lines as $l) {
        echo "  -> Account: {$l->code} - {$l->name} | D: {$l->debit} | C: {$l->credit} | {$l->description}\n";
    }
}

// Also check: what the ledger shows as opening balance when start=2025-01-01
echo "\n=== Opening balance calculation for Rajhi (before 2025-01-01) ===\n";
$ob = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', 8)
    ->where('je.status', 'posted')
    ->where('je.entry_date', '<', '2025-01-01')
    ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c')
    ->first();

echo "Debit before 2025: {$ob->d}\n";
echo "Credit before 2025: {$ob->c}\n";
echo "Opening Balance (D-C): " . ($ob->d - $ob->c) . "\n";

// Opening balance for ledger from 2025-03-01
echo "\n=== Opening balance for March view (before 2025-03-01) ===\n";
$obMarch = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', 8)
    ->where('je.status', 'posted')
    ->where('je.entry_date', '<', '2025-03-01')
    ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c')
    ->first();

echo "Debit before March: {$obMarch->d}\n";
echo "Credit before March: {$obMarch->c}\n";
echo "Opening Balance (D-C): " . ($obMarch->d - $obMarch->c) . "\n";
