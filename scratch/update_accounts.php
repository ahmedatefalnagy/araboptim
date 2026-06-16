<?php

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Support\Facades\DB;

// Fetch types
$assetType = AccountType::where('code', 'asset')->first()->id;
$liabilityType = AccountType::where('code', 'liability')->first()->id;
$equityType = AccountType::where('code', 'equity')->first()->id;
$revenueType = AccountType::where('code', 'revenue')->first()->id;
$expenseType = AccountType::where('code', 'expense')->first()->id;

// 1. Correct the "Hafar Al-Batin" account code and parent
$hafar = Account::where('name', 'like', '%حفر الباطن%')->first();
if ($hafar) {
    $parentBank = Account::where('code', '1120')->first();
    $hafar->update([
        'code' => '1127',
        'parent_id' => $parentBank ? $parentBank->id : $hafar->parent_id
    ]);
}

// 2. Add Missing Essential Trading Accounts
$structure = [
    // Current Assets Additions
    ['parent_code' => '1100', 'code' => '1170', 'name' => 'ضريبة القيمة المضافة - مدخلات', 'type' => $assetType, 'postable' => true],
    ['parent_code' => '1100', 'code' => '1180', 'name' => 'مصاريف مدفوعة مقدماً', 'type' => $assetType, 'postable' => true],
    
    // Current Liabilities Additions
    ['parent_code' => '2100', 'code' => '2110', 'name' => 'ذمم دائنة (الموردين)', 'type' => $liabilityType, 'postable' => true],
    ['parent_code' => '2100', 'code' => '2120', 'name' => 'ضريبة القيمة المضافة - مخرجات', 'type' => $liabilityType, 'postable' => true],
    ['parent_code' => '2100', 'code' => '2130', 'name' => 'مستحقات ورواتب موظفين', 'type' => $liabilityType, 'postable' => true],
    ['parent_code' => '2100', 'code' => '2140', 'name' => 'مخصص الزكاة الشرعية', 'type' => $liabilityType, 'postable' => true],
    
    // Cost of Goods Sold (Vital for trading)
    ['parent_code' => '5000', 'code' => '5100', 'name' => 'تكلفة المبيعات', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5000', 'code' => '5400', 'name' => 'مصروفات بنكية وعمولات', 'type' => $expenseType, 'postable' => true],
];

foreach ($structure as $item) {
    $parent = Account::where('code', $item['parent_code'])->first();
    if ($parent) {
        Account::updateOrCreate(
            ['code' => $item['code']],
            [
                'parent_id' => $parent->id,
                'name' => $item['name'],
                'account_type_id' => $item['type'],
                'level' => $parent->level + 1,
                'is_postable' => $item['postable'],
                'is_active' => true
            ]
        );
    }
}

echo "Standard Saudi Trading Chart of Accounts updated successfully.";
