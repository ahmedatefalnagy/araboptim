<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

$balance = JournalEntryLine::where('account_id', 8)
    ->whereHas('journalEntry', function($q) {
        $q->where('entry_date', '<=', '2025-09-30');
    })
    ->sum(DB::raw('debit - credit'));

echo "DB Balance up to 2025-09-30: " . $balance . "\n";
