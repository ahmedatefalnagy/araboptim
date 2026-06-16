<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

$balance = JournalEntryLine::where('account_id', 8)
    ->whereHas('journalEntry', function($q) {
        $q->where('entry_date', '<=', '2025-12-31');
    })
    ->sum(DB::raw('debit - credit'));

echo "Current Al-Rajhi Main Balance: $balance\n";
