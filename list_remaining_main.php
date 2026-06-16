<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;

$entries = JournalEntry::whereHas('lines', function($q) {
    $q->where('account_id', 8);
})->orderBy('created_at', 'desc')->take(20)->get(['id', 'created_at', 'description']);

foreach($entries as $e) {
    echo "ID: {$e->id}, Created: {$e->created_at}, Desc: {$e->description}\n";
}
