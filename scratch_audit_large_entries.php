<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$lines = DB::table('journal_entry_lines')
    ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
    ->where('account_id', 8)
    ->where(function($q) {
        $q->where('debit', '>', 5000)->orWhere('credit', '>', 5000);
    })
    ->get(['journal_entries.id', 'journal_entries.entry_no', 'journal_entries.entry_date', 'journal_entry_lines.debit', 'journal_entry_lines.credit', 'journal_entries.description']);

echo "Found " . count($lines) . " large transactions in Al-Rajhi Main.\n";
foreach($lines as $l) {
    echo "ID: {$l->id}, No: {$l->entry_no}, Date: {$l->entry_date}, Debit: {$l->debit}, Credit: {$l->credit}, Desc: {$l->description}\n";
}
