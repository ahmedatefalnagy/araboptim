<?php

use App\Models\Account;

// Find all account IDs that are parents of other accounts
$parentIds = Account::whereNotNull('parent_id')->distinct()->pluck('parent_id');

// Update these accounts to be non-postable (Folders)
$updatedCount = Account::whereIn('id', $parentIds)
    ->where('is_postable', true)
    ->update(['is_postable' => false]);

echo "Updated $updatedCount parent accounts to be non-postable (Folders).";
