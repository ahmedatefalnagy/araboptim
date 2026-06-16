<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$ids = DB::table('journal_entry_lines')->pluck('account_id')->unique();
$missing = [];
foreach($ids as $id) {
    if(!DB::table('accounts')->where('id', $id)->exists()) {
        $missing[] = $id;
    }
}

echo "Orphaned Account IDs in Journal Lines: " . json_encode($missing) . "\n";

$orphanLines = DB::table('journal_entry_lines')
    ->whereIn('account_id', $missing)
    ->get();

echo "Number of orphaned lines: " . $orphanLines->count() . "\n";
foreach($orphanLines as $line) {
    echo "Line ID: {$line->id}, Entry ID: {$line->journal_entry_id}, Debit: {$line->debit}, Credit: {$line->credit}\n";
}
