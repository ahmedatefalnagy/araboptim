<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Account;
use Illuminate\Support\Facades\DB;

DB::transaction(function () {
    // Temporarily rename all codes to prevent unique constraint conflicts
    $accounts = Account::all();
    foreach ($accounts as $account) {
        $account->code = 'TEMP_' . $account->id . '_' . $account->code;
        $account->save();
    }
    
    function updateCodes($parent_id = null, $parent_code = "") {
        // We order by original code name to preserve the logical sequence
        $accounts = Account::where('parent_id', $parent_id)
            ->get()
            ->sortBy(function($account) {
                // Extract original code from the temp name to sort properly
                $parts = explode('_', $account->code);
                return end($parts);
            });
            
        $index = 1;
        foreach ($accounts as $account) {
            if ($parent_id === null) {
                $orig = explode('_', $account->code);
                $orig_code = end($orig);
                $new_code = substr($orig_code, 0, 1);
                if (!in_array($new_code, ['1', '2', '3', '4', '5'])) {
                    $new_code = (string)$index;
                }
            } else {
                $parent_len = strlen($parent_code);
                if ($parent_len === 1) {
                    // Level 2 (e.g., 11, 12)
                    $new_code = $parent_code . $index;
                } else {
                    // Level 3+ (e.g., 1101, 110101)
                    $new_code = $parent_code . str_pad($index, 2, '0', STR_PAD_LEFT);
                }
            }
            
            $account->code = $new_code;
            // Recalculate level field
            $account->level = strlen($new_code) <= 2 ? strlen($new_code) : (strlen($new_code) / 2) + 1;
            $account->save();
            
            updateCodes($account->id, $new_code);
            $index++;
        }
    }
    
    updateCodes(null);
});

echo "Re-coding completed successfully!\n";
