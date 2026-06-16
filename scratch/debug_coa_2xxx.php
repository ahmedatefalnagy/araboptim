<?php

use App\Models\Account;

echo "Listing branch 2xxx with details:\n";
$accounts = Account::where('code', 'like', '2%')->get();
foreach ($accounts as $a) {
    echo "ID: {$a->id} | Code: {$a->code} | Name: {$a->name} | Parent: {$a->parent_id} | Type: {$a->account_type_id}\n";
}

$parentOf2160 = Account::where('code', '2160')->first()?->parent_id;
$parent2100 = Account::where('code', '2100')->first()?->id;

echo "\nVerification:\n";
echo "Parent of 2160: $parentOf2160\n";
echo "ID of 2100: $parent2100\n";

if ($parentOf2160 == $parent2100) {
    echo "Logic Correct: 2160 is a child of 2100.\n";
} else {
    echo "Logic ERROR: 2160 is NOT a child of 2100.\n";
}
