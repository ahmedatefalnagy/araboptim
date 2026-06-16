<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$a = App\Models\Account::find(27);
echo "Name: " . $a->name . "\n";
echo "Code: " . $a->code . "\n";
