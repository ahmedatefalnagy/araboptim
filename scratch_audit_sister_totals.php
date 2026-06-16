<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$ids = [218, 219, 220, 221];
$res = DB::table('journal_entry_lines')
    ->whereIn('account_id', $ids)
    ->select(DB::raw('SUM(debit) as d, SUM(credit) as c'))
    ->first();

echo "Restored Sister Companies:\n";
echo "Total Debit: " . $res->d . "\n";
echo "Total Credit: " . $res->c . "\n";
echo "Net Balance: " . ($res->d - $res->c) . "\n";
