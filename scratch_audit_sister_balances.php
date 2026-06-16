<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$ids = [218, 219, 220, 221];
$bal = DB::table('journal_entry_lines')
    ->whereIn('account_id', $ids)
    ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
    ->first()->balance;

echo "Total Balance of Restored Sister Companies: " . $bal . "\n";

foreach($ids as $id) {
    $acc = DB::table('accounts')->find($id);
    $aBal = DB::table('journal_entry_lines')
        ->where('account_id', $id)
        ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
        ->first()->balance;
    echo "Account: {$acc->name}, Balance: {$aBal}\n";
}
