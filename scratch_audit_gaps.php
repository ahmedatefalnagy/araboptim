<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$nos = DB::table('journal_entries')->where('entry_no', 'like', 'JV-%')->pluck('entry_no')
    ->map(function($n) { return (int)str_replace('JV-', '', $n); })
    ->sort()->values()->toArray();

$gaps = [];
for($i=0; $i < count($nos)-1; $i++) {
    if($nos[$i+1] - $nos[$i] > 1) {
        $gaps[] = ['from' => $nos[$i], 'to' => $nos[$i+1], 'missing' => $nos[$i+1] - $nos[$i] - 1];
    }
}

echo "Found " . count($gaps) . " gaps in JV numbers.\n";
foreach($gaps as $g) {
    echo "Gap between JV-" . str_pad($g['from'], 6, '0', STR_PAD_LEFT) . " and JV-" . str_pad($g['to'], 6, '0', STR_PAD_LEFT) . " ({$g['missing']} entries missing)\n";
}
