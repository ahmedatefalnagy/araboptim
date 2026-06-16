<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Account;

$customer = Account::where('code', '1130')->first();
if ($customer) {
    $customer->update(['name' => 'العملاء']);
    echo "Renamed 1130 to العملاء\n";
}

$parent2100 = Account::where('code', '2100')->first();
$supplier = Account::updateOrCreate(['code' => '2110'], [
    'name' => 'الموردون',
    'parent_id' => $parent2100 ? $parent2100->id : null,
    'account_type_id' => 2,
    'is_postable' => 0,
    'is_active' => 1,
    'level' => 3
]);
echo "Created/Updated 2110 (الموردون)\n";

$related = Account::updateOrCreate(['code' => '1155'], [
    'name' => 'أطراف ذات علاقة - شركات شقيقة',
    'parent_id' => 6,
    'account_type_id' => 1,
    'is_postable' => 0,
    'is_active' => 1,
    'level' => 3
]);
echo "Created/Updated 1155 (أطراف ذات علاقة)\n";
