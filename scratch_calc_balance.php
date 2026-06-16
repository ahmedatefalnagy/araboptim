<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$lines = DB::table('journal_entry_lines')
    ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
    ->where('account_id', 8)
    ->where('journal_entries.status', 'posted')
    ->orderBy('journal_entries.entry_date')
    ->orderBy('journal_entry_lines.id')
    ->get(['journal_entries.entry_date', 'journal_entry_lines.debit', 'journal_entry_lines.credit', 'journal_entries.id as entry_id']);

$balance = 0;
foreach($lines as $l) {
    $balance += (float)$l->debit;
    $balance -= (float)$l->credit;
    if($l->entry_date <= '2025-09-30') {
        $lastBalance = $balance;
    }
}

echo "Current Total Balance: " . $balance . "\n";
echo "Balance as of 2025-09-30: " . $lastBalance . "\n";
echo "Discrepancy: " . ($lastBalance - 4260.77) . "\n";
