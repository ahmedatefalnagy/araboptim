<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$accounts = App\Models\Account::where('name', 'like', '%مورد%')
    ->orWhere('name', 'like', '%المورد%')
    ->get();

foreach ($accounts as $a) {
    echo "ID: {$a->id}, Code: {$a->code}, Name: {$a->name}, Parent ID: {$a->parent_id}\n";
}
