<?php

use App\Models\Account;
use App\Models\Contact;
use App\Models\AccountType;

// 1. Force delete the children and contacts first
$idsToDelete = Account::where('code', '1155')->orWhere('parent_id', function($q) {
    $q->select('id')->from('accounts')->where('code', '1155');
})->pluck('id');

Contact::whereIn('account_id', $idsToDelete)->delete();
Account::whereIn('id', $idsToDelete)->delete();

// Similarly for 2160
$idsToDelete2 = Account::where('code', '2160')->orWhere('parent_id', function($q) {
    $q->select('id')->from('accounts')->where('code', '2160');
})->pluck('id');

Contact::whereIn('account_id', $idsToDelete2)->delete();
Account::whereIn('id', $idsToDelete2)->delete();

// 2. Re-create clean folders
$assetType = AccountType::where('code', 'asset')->first()->id;
$liabilityType = AccountType::where('code', 'liability')->first()->id;

$parent1100 = Account::where('code', '1100')->first(); // Assets
$parent2100 = Account::where('code', '2100')->first(); // Liabilities

if ($parent1100) {
    Account::create([
        'code' => '1155',
        'name' => 'أطراف ذات علاقة - شركات شقيقة (مدين)',
        'parent_id' => $parent1100->id,
        'account_type_id' => $assetType,
        'level' => 3,
        'is_postable' => false,
        'is_active' => true
    ]);
}

if ($parent2100) {
    Account::create([
        'code' => '2160',
        'name' => 'أطراف ذات علاقة - شركات شقيقة (دائن)',
        'parent_id' => $parent2100->id,
        'account_type_id' => $liabilityType,
        'level' => 3,
        'is_postable' => false,
        'is_active' => true
    ]);
}

echo "Cleaned and Re-created Related Parties accounts successfully.";
