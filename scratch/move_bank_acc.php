<?php

use App\Models\Account;

$bankAcc = Account::where('code', '5400')->first();
$gaAcc = Account::where('code', '5200')->first();

if ($bankAcc && $gaAcc) {
    $bankAcc->update([
        'parent_id' => $gaAcc->id,
        'level' => $gaAcc->level + 1
    ]);
    
    // Also update children levels
    Account::where('parent_id', $bankAcc->id)->update([
        'level' => $gaAcc->level + 2
    ]);
    
    echo "Moved Bank Expenses under Administrative Expenses successfully.";
} else {
    echo "Error: Could not find Bank or G&A accounts.";
}
