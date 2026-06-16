<?php

use App\Models\Account;
use App\Models\AccountType;

$liabilityType = AccountType::where('code', 'liability')->first()->id;

$structure = [
    // Long-term Liabilities (2200)
    ['parent_code' => '2200', 'code' => '2210', 'name' => 'قروض بنكية طويلة الأجل', 'type' => $liabilityType, 'postable' => true],
    ['parent_code' => '2200', 'code' => '2220', 'name' => 'مخصص مستحقات نهاية الخدمة', 'type' => $liabilityType, 'postable' => true],
    ['parent_code' => '2200', 'code' => '2230', 'name' => 'ذمم دائنة لجهات شقيقة', 'type' => $liabilityType, 'postable' => true],
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

echo "Long-term Liabilities updated successfully.";
