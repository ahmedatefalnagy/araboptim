<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

// Delete all JournalEntries that have a line for Account 8 and were created recently
$entries = JournalEntry::whereHas('lines', function($q) {
    $q->where('account_id', 8);
})->where('created_at', '>', \Carbon\Carbon::now()->subHours(24))->get();

foreach($entries as $e) {
    $e->delete();
}

echo "Deleted " . $entries->count() . " entries touching account 8.\n";

// Check balance
$balance = JournalEntryLine::where('account_id', 8)
    ->whereHas('journalEntry', function($q) {
        $q->where('entry_date', '<=', '2025-12-31');
    })
    ->sum(DB::raw('debit - credit'));

echo "Final Balance for Account 8: $balance\n";
