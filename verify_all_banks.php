<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

foreach(['1121', '1122', '1123'] as $code) {
    $acc = Account::where('code', $code)->first();
    $bal = JournalEntryLine::where('account_id', $acc->id)
        ->whereHas('journalEntry', function($q) {
            $q->where('entry_date', '<=', '2025-12-31');
        })
        ->sum(DB::raw('debit - credit'));
    echo "$code (" . $acc->name . "): $bal\n";
}
