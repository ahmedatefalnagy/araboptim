<?php

use App\Models\Employee;
use App\Models\Account;
use App\Models\AccountType;

echo "Syncing existing employees to Chart of Accounts...\n\n";

// Ensure we have the parent account 1150
$parentAccount = Account::where('code', '1150')->first();

if (!$parentAccount) {
    die("Error: Parent account 1150 (Advances) not found.\n");
}

$employees = Employee::whereNull('account_id')->get();

foreach ($employees as $employee) {
    echo "Processing [{$employee->employee_no}] {$employee->name}...\n";

    // Generate unique code under 1150
    $lastSubAccount = Account::where('parent_id', $parentAccount->id)
        ->orderBy('code', 'desc')
        ->first();

    if ($lastSubAccount) {
        $lastCode = (int) $lastSubAccount->code;
        $newCode = strval($lastCode + 1);
    } else {
        $newCode = $parentAccount->code . '001';
    }

    $account = Account::create([
        'code' => $newCode,
        'name' => "[{$employee->employee_no}] {$employee->name}",
        'account_type_id' => $parentAccount->account_type_id,
        'parent_id' => $parentAccount->id,
        'is_postable' => true,
    ]);

    $employee->update(['account_id' => $account->id]);
    echo "SUCCESS: Created account {$newCode} for {$employee->name}\n";
}

echo "\nSync complete.\n";
