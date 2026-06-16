<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;

$parent = Account::where('name', 'like', '%إدارية%')->first();
if ($parent) {
    $account = Account::create([
        'name' => 'محروقات',
        'code' => '5210007',
        'parent_id' => $parent->id,
        'account_type_id' => $parent->account_type_id,
        'level' => $parent->level + 1,
        'is_postable' => 1,
        'is_active' => 1
    ]);
    echo "Account 'محروقات' created successfully with code 5210007.\n";
} else {
    echo "Parent account not found.\n";
}
