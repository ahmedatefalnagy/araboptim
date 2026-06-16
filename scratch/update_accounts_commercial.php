<?php

use App\Models\Account;
use App\Models\AccountType;

$assetType = AccountType::where('code', 'asset')->first()->id;
$expenseType = AccountType::where('code', 'expense')->first()->id;

// 1. Refine Accumulated Depreciation (1240)
$parentAccDep = Account::where('code', '1240')->first();
if ($parentAccDep) {
    Account::updateOrCreate(['code' => '1241'], ['parent_id' => $parentAccDep->id, 'name' => 'مجمع إهلاك مباني والإنشاءات', 'account_type_id' => $assetType, 'is_postable' => true, 'level' => 3]);
    Account::updateOrCreate(['code' => '1242'], ['parent_id' => $parentAccDep->id, 'name' => 'مجمع إهلاك السيارات والمعدات', 'account_type_id' => $assetType, 'is_postable' => true, 'level' => 3]);
    Account::updateOrCreate(['code' => '1243'], ['parent_id' => $parentAccDep->id, 'name' => 'مجمع إهلاك الأثاث والأجهزة', 'account_type_id' => $assetType, 'is_postable' => true, 'level' => 3]);
}

// 2. Overhaul Expenses to be strictly Commercial (Trading)
// Removing/Cleaning logistics-specific from 53xx if any
$structure = [
    // General & Admin (5200)
    ['parent_code' => '5200', 'code' => '5210', 'name' => 'مصروف الرواتب والأجور', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5200', 'code' => '5220', 'name' => 'مصروف بدلات الموظفين', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5200', 'code' => '5230', 'name' => 'مصروف كهرباء ومياه وهاتف', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5200', 'code' => '5240', 'name' => 'مصروف إيجار مكاتب ومعارض', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5200', 'code' => '5250', 'name' => 'مصروف الإهلاك (Depreciation Expense)', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5200', 'code' => '5260', 'name' => 'مصروف البريد والأدوات المكتبية', 'type' => $expenseType, 'postable' => true],
    
    // Selling & Marketing (5300)
    ['parent_code' => '5300', 'code' => '5310', 'name' => 'مصروف دعاية وإعلان', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5300', 'code' => '5320', 'name' => 'عمولات مناديب مبيعات', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5300', 'code' => '5330', 'name' => 'مصاريف النقل والتحميل (للبيع)', 'type' => $expenseType, 'postable' => true],
];

foreach ($structure as $item) {
    if (isset($item['parent_code'])) {
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
}

echo "Commercial Trading Expenses and Depreciation accounts updated successfully.";
