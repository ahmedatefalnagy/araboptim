<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$je = DB::table('journal_entries')->where('entry_no', 'JV-000508')->first();
if($je) {
    echo "Entry JV-000508 exists (ID: {$je->id}, Date: {$je->entry_date}, Desc: {$je->description})\n";
    $lines = DB::table('journal_entry_lines')->where('journal_entry_id', $je->id)->get();
    echo "Lines: " . $lines->count() . "\n";
    foreach($lines as $l) {
        $acc = DB::table('accounts')->find($l->account_id);
        echo "  Account: {$acc->name} (ID: {$l->account_id}), Debit: {$l->debit}, Credit: {$l->credit}\n";
    }
} else {
    echo "Entry JV-000508 NOT found.\n";
}
