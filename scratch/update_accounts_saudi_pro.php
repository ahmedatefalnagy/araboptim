<?php

use App\Models\Account;
use App\Models\AccountType;

$expenseType = AccountType::where('code', 'expense')->first()->id;

$structure = [
    // 1. Salaries Detailed
    ['parent_code' => '5210', 'code' => '5211', 'name' => 'أجور ورواتب السعوديين', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5210', 'code' => '5212', 'name' => 'أجور ورواتب الأجانب', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5210', 'code' => '5213', 'name' => 'مصروف تأمينات اجتماعية (GOSI)', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5210', 'code' => '5214', 'name' => 'مصروف مستحقات نهاية الخدمة', 'type' => $expenseType, 'postable' => true],
    
    // 2. Government & Permits
    ['parent_code' => '5200', 'code' => '5270', 'name' => 'إقامات ورخص مكتب العمل', 'type' => $expenseType, 'postable' => true],
    
    // 3. Utilities & Communication
    ['parent_code' => '5230', 'code' => '5231', 'name' => 'مصروف هاتف وإنترنت', 'type' => $expenseType, 'postable' => true],
    
    // 4. Rent
    ['parent_code' => '5240', 'code' => '5241', 'name' => 'مصروف إيجارات', 'type' => $expenseType, 'postable' => true],
    
    // 5. Detailed Depreciation Expenses
    ['parent_code' => '5250', 'code' => '5251', 'name' => 'إهلاك آلات ومعدات', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5250', 'code' => '5252', 'name' => 'إهلاك سيارات', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5250', 'code' => '5253', 'name' => 'إهلاك أثاث ومفروشات', 'type' => $expenseType, 'postable' => true],
    
    // 6. Other Operating/G&A
    ['parent_code' => '5200', 'code' => '5280', 'name' => 'مصروف إعاشة وضيافة', 'type' => $expenseType, 'postable' => true],
    ['parent_code' => '5200', 'code' => '5290', 'name' => 'مصروف محروقات (بنزين/ديزل)', 'type' => $expenseType, 'postable' => true],
    
    // 7. Finance
    ['parent_code' => '5400', 'code' => '5410', 'name' => 'مصروف رسوم بنكية', 'type' => $expenseType, 'postable' => true],

    // 8. Operating Purchases (New Category for Trading)
    ['parent_code' => '5000', 'code' => '5500', 'name' => 'مشتريات تشغيلية (Operating Purchases)', 'type' => $expenseType, 'postable' => true],
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

echo "Detailed Saudi Accounts (Salaries, Government, Depreciation, etc.) updated successfully.";
