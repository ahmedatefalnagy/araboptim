<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$results = DB::table('journal_entries')
    ->whereNotExists(function($query) {
        $query->select(DB::raw(1))
            ->from('journal_entry_lines')
            ->whereRaw('journal_entry_lines.journal_entry_id = journal_entries.id');
    })
    ->get(['id', 'entry_no', 'description', 'entry_date']);

echo "Found " . count($results) . " entries with 0 lines.\n";
foreach($results as $r) {
    echo "ID: {$r->id}, No: {$r->entry_no}, Date: {$r->entry_date}, Desc: {$r->description}\n";
}
