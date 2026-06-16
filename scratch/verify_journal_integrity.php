<?php

use App\Services\JournalEntryService;
use App\Models\FiscalYear;
use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock Auth
$user = User::first();
Auth::login($user);

$service = new JournalEntryService();
$fiscalYear = FiscalYear::first();
$account1 = Account::where('is_postable', true)->first();
$account2 = Account::where('is_postable', true)->where('id', '!=', $account1->id)->first();

echo "Testing Journal Entry Integrity...\n\n";

// 1. Test Unbalanced Entry
try {
    echo "1. Attempting unbalanced entry (Debit 100, Credit 50)...\n";
    $service->create([
        'entry_date' => now()->format('Y-m-d'),
        'fiscal_year_id' => $fiscalYear->id,
        'lines' => [
            ['account_id' => $account1->id, 'debit' => 100, 'credit' => 0],
            ['account_id' => $account2->id, 'debit' => 0, 'credit' => 50],
        ]
    ]);
    echo "FAILED: Unbalanced entry was saved.\n";
} catch (\RuntimeException $e) {
    echo "SUCCESS: Caught expected error: " . $e->getMessage() . "\n";
}

// 2. Test Both Debit and Credit on same line
try {
    echo "\n2. Attempting entry with both debit and credit on same line...\n";
    $service->create([
        'entry_date' => now()->format('Y-m-d'),
        'fiscal_year_id' => $fiscalYear->id,
        'lines' => [
            ['account_id' => $account1->id, 'debit' => 100, 'credit' => 100],
            ['account_id' => $account2->id, 'debit' => 0, 'credit' => 0],
        ]
    ]);
    echo "FAILED: Mutual exclusivity error not caught.\n";
} catch (\RuntimeException $e) {
    echo "SUCCESS: Caught expected error: " . $e->getMessage() . "\n";
}

// 3. Test Valid Entry
try {
    echo "\n3. Attempting valid balanced entry with transaction type...\n";
    $entry = $service->create([
        'entry_date' => now()->format('Y-m-d'),
        'fiscal_year_id' => $fiscalYear->id,
        'transaction_type' => 'salary',
        'reference_id' => 1,
        'lines' => [
            ['account_id' => $account1->id, 'debit' => 200, 'credit' => 0],
            ['account_id' => $account2->id, 'debit' => 0, 'credit' => 200],
        ]
    ]);
    echo "SUCCESS: Entry saved. ID: {$entry->id}, Type: {$entry->transaction_type}\n";
} catch (\Exception $e) {
    echo "FAILED: Valid entry could not be saved: " . $e->getMessage() . "\n";
}
