<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Total accounts count: " . App\Models\Account::count() . "\n";
foreach (App\Models\Account::orderBy('id')->get() as $acc) {
    echo "ID: {$acc->id}, Code: {$acc->code}, Name: {$acc->name}, Parent: {$acc->parent_id}\n";
}
