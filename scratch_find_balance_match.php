<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Account;
use Illuminate\Support\Facades\DB;

$accounts = Account::all();
foreach($accounts as $acc) {
    $bal = DB::table('journal_entry_lines')
        ->where('account_id', $acc->id)
        ->select(DB::raw('SUM(debit) - SUM(credit) as balance'))
        ->first()->balance;
    
    if(round((float)$bal, 2) == 4260.77) {
        echo "Found Match! Account ID: {$acc->id}, Code: {$acc->code}, Name: {$acc->name}, Balance: {$bal}\n";
    }
}
echo "Search Complete.\n";
