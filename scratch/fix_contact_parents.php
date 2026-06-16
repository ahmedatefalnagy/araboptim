<?php

use App\Models\Account;
use App\Models\AccountType;

$parents = [
    'customer' => ['code' => '1130', 'name' => 'العملاء', 'type' => 'asset'],
    'supplier' => ['code' => '2110', 'name' => 'الموردون', 'type' => 'liability'],
    'employee' => ['code' => '1150', 'name' => 'عهود وسلف موظفين', 'type' => 'asset'],
    'partner_debit' => ['code' => '1155', 'name' => 'أطراف ذات علاقة - شركات شقيقة (مدين)', 'type' => 'asset'],
    'partner_credit' => ['code' => '2160', 'name' => 'أطراف ذات علاقة - شركات شقيقة (دائن)', 'type' => 'liability']
];

foreach ($parents as $p) {
    $accType = AccountType::where('code', $p['type'])->first();
    $acc = Account::where('code', $p['code'])->first();
    
    if ($acc) {
        $acc->update([
            'name' => $p['name'],
            'is_postable' => false, // Ensure they are folders
            'is_active' => true,
            'account_type_id' => $accType->id
        ]);
        echo "Updated Folder: {$p['code']} - {$p['name']}\n";
    } else {
        Account::create([
            'code' => $p['code'],
            'name' => $p['name'],
            'is_postable' => false,
            'is_active' => true,
            'account_type_id' => $accType->id,
            'level' => 3
        ]);
        echo "Created Folder: {$p['code']} - {$p['name']}\n";
    }
}

echo "\nFolders Sanity Check Complete.";
