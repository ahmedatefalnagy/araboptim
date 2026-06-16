<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntryLine;

$descs = JournalEntryLine::whereHas('journalEntry', function($q) {
    $q->where('entry_no', 'like', 'RA-%');
})->take(50)->pluck('description')->toArray();

print_r($descs);
