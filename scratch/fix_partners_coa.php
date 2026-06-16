<?php

use App\Models\Account;
use App\Models\AccountType;

$assetType = AccountType::where('code', 'asset')->first()->id;
$liabilityType = AccountType::where('code', 'liability')->first()->id;

// Ensure Parents exist
$parent1100 = Account::where('code', '1100')->first(); // Current Assets
$parent2100 = Account::where('code', '2100')->first(); // Current Liabilities

if (!$parent1100 || !$parent2100) {
    echo "Error: Base folders 1100 or 2100 not found.";
    exit;
}

// 1. Assets Side (Madin)
Account::updateOrCreate(
    ['code' => '1155'],
    [
        'parent_id' => $parent1100->id,
        'name' => 'أطراف ذات علاقة - شركات شقيقة (مدين)',
        'account_type_id' => $assetType,
        'level' => 3,
        'is_postable' => false,
        'is_active' => true
    ]
);

// 2. Liabilities Side (Dain)
Account::updateOrCreate(
    ['code' => '2160'],
    [
        'parent_id' => $parent2100->id,
        'name' => 'أطراف ذات علاقة - شركات شقيقة (دائن)',
        'account_type_id' => $liabilityType,
        'level' => 3,
        'is_postable' => false,
        'is_active' => true
    ]
);

echo "Accounts 1155 (Madin) and 2160 (Dain) verified and fixed under their correct parents.";
