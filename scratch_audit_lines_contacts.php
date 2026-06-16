<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$ids = [6, 8, 10, 12];
$count = DB::table('journal_entry_lines')->whereIn('contact_id', $ids)->count();

echo "Journal Entry Lines with Sister Company Contact IDs: " . $count . "\n";

foreach($ids as $id) {
    $c = DB::table('contacts')->find($id);
    $cCount = DB::table('journal_entry_lines')->where('contact_id', $id)->count();
    echo "Contact: {$c->name} (ID: {$id}), Lines: {$cCount}\n";
}
