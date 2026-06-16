<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

$accounts = Account::where('code', 'like', '12%')->get();
foreach($accounts as $acc) {
    $bal = JournalEntryLine::where('account_id', $acc->id)->sum(DB::raw('debit - credit'));
    echo "{$acc->code} ({$acc->name}): $bal\n";
}
