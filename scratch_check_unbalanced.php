<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\JournalEntry;

$unbalanced = [];
$entries = JournalEntry::with('lines')->get();
foreach($entries as $je) {
    if(!$je->is_balanced) {
        $unbalanced[] = [
            'id' => $je->id,
            'entry_no' => $je->entry_no,
            'date' => $je->entry_date->format('Y-m-d'),
            'desc' => $je->description,
            'debit' => $je->total_debit,
            'credit' => $je->total_credit,
            'diff' => $je->total_debit - $je->total_credit
        ];
    }
}

echo "Found " . count($unbalanced) . " unbalanced entries.\n";
foreach($unbalanced as $u) {
    echo "ID: {$u['id']}, No: {$u['entry_no']}, Date: {$u['date']}, Debit: {$u['debit']}, Credit: {$u['credit']}, Diff: {$u['diff']}\n";
}
