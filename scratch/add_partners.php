<?php

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

// 1. Update DB to allow 'partner' type (using string instead of enum for flexibility)
Schema::table('contacts', function (Blueprint $table) {
    $table->string('type')->change();
});

// 2. Add Related Parties Accounts in CoA
$assetType = AccountType::where('code', 'asset')->first()->id;
$liabilityType = AccountType::where('code', 'liability')->first()->id;

$parentCurrentAssets = Account::where('code', '1100')->first();
$parentCurrentLiabilities = Account::where('code', '2100')->first();

if ($parentCurrentAssets) {
    Account::updateOrCreate(
        ['code' => '1155'], // New code for Related Parties Debit
        [
            'parent_id' => $parentCurrentAssets->id,
            'name' => 'أطراف ذات علاقة - شركات شقيقة (مدين)',
            'account_type_id' => $assetType,
            'level' => 3,
            'is_postable' => false,
            'is_active' => true
        ]
    );
}

if ($parentCurrentLiabilities) {
    Account::updateOrCreate(
        ['code' => '2160'], // New code for Related Parties Credit
        [
            'parent_id' => $parentCurrentLiabilities->id,
            'name' => 'أطراف ذات علاقة - شركات شقيقة (دائن)',
            'account_type_id' => $liabilityType,
            'level' => 3,
            'is_postable' => false,
            'is_active' => true
        ]
    );
}

echo "Database updated and Related Parties accounts created successfully.";
