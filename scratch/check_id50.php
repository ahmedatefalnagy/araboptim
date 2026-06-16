<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$a = App\Models\Account::find(50);
echo "Name: " . $a->name . "\n";
echo "Parent ID: " . $a->parent_id . "\n";
echo "Parent Name: " . ($a->parent ? $a->parent->name : 'none') . "\n";
