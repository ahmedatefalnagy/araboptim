<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

$unbalanced = [];
// Check ALL entries in the DB using raw SQL to be sure
$results = DB::table('journal_entries as je')
    ->leftJoin('journal_entry_lines as jel', 'je.id', '=', 'jel.journal_entry_id')
    ->select('je.id', 'je.entry_no', DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
    ->groupBy('je.id', 'je.entry_no')
    ->get();

foreach($results as $r) {
    if(round((float)$r->total_debit, 2) != round((float)$r->total_credit, 2)) {
        $unbalanced[] = $r;
    }
}

echo "Found " . count($unbalanced) . " unbalanced entries.\n";
foreach($unbalanced as $u) {
    echo "ID: {$u->id}, No: {$u->entry_no}, Debit: {$u->total_debit}, Credit: {$u->total_credit}\n";
}
