<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntryLine;

$descriptions = JournalEntryLine::where('account_id', 8)
    ->whereHas('journalEntry', function($q) {
        $q->where('entry_date', '>=', '2025-10-01');
    })
    ->pluck('description')
    ->toArray();

echo "Existing Descriptions in 1121 for Q4:\n";
print_r($descriptions);
