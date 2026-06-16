<?php

use App\Models\Account;
use App\Models\AccountType;

// Fetch types
$assetType = AccountType::where('code', 'asset')->first()->id;
$liabilityType = AccountType::where('code', 'liability')->first()->id;
$equityType = AccountType::where('code', 'equity')->first()->id;
$revenueType = AccountType::where('code', 'revenue')->first()->id;
$expenseType = AccountType::where('code', 'expense')->first()->id;

$structure = [
    // 1. Fixed Assets (1200)
    ['parent_code' => '1200', 'code' => '1210', 'name' => 'الأراضي والمباني', 'type' => $assetType, 'postable' => true],
    ['parent_code' => '1200', 'code' => '1220', 'name' => 'الشاحنات والمعدات', 'type' => $assetType, 'postable' => true],
    ['parent_code' => '1200', 'code' => '1230', 'name' => 'الأثاث والأجهزة المكتبية', 'type' => $assetType, 'postable' => true],
    ['parent_code' => '1200', 'code' => '1240', 'name' => 'مجمع استهلاك الأصول الثابتة (دائن)', 'type' => $assetType, 'postable' => true],
    
    // 2. Equity (3000)
    ['parent_code' => '3000', 'code' => '3100', 'name' => 'رأس المال المسموح به', 'type' => $equityType, 'postable' => true],
    ['parent_code' => '3000', 'code' => '3200', 'name' => 'جاري صاحب المؤسسة / الشركاء', 'type' => $equityType, 'postable' => true],
    ['parent_code' => '3000', 'code' => '3300', 'name' => 'الأرباح والخسائر المبقاة', 'type' => $equityType, 'postable' => true],
    
    // 3. Revenues (4000)
    ['parent_code' => '4000', 'code' => '4100', 'name' => 'مبيعات البضائع', 'type' => $revenueType, 'postable' => true],
    ['parent_code' => '4000', 'code' => '4200', 'name' => 'إيرادات خدمات النقل واللوجستيك', 'type' => $revenueType, 'postable' => true],
    ['parent_code' => '4000', 'code' => '4300', 'name' => 'إيرادات أخرى', 'type' => $revenueType, 'postable' => true],
    
    // 4. Expenses Expansion (5000)
    ['parent_code' => '5000', 'code' => '5210', 'name' => 'رواتب وأجور الموظفين', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5000', 'code' => '5220', 'name' => 'بدلات سكن وانتقال', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5000', 'code' => '5310', 'name' => 'مصروفات ديزل ومحروقات', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5000', 'code' => '5320', 'name' => 'مصروفات صيانة وقطع غيار', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5000', 'code' => '5330', 'name' => 'رسوم حكومية وكفرات', 'type' => $expenseType, 'postable' => true],
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

echo "Full Saudi Pro Chart of Accounts (Assets, Equity, Revenue, Expenses) updated successfully.";
