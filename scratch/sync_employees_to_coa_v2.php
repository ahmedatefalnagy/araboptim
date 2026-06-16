<?php

use App\Models\Employee;
use App\Models\Account;

echo "Cleaning up partial accounts and resyncing employees with safer coding...\n\n";

// Delete the accounts created for 101 and 102 previously to avoid mess
Account::where('code', '1153')->delete();
Account::where('code', '1154')->delete();
Employee::query()->update(['account_id' => null]);

$parentAccount = Account::where('code', '1150')->first();

if (!$parentAccount) {
    die("Error: Parent account 1150 not found.\n");
}

$employees = Employee::all();

foreach ($employees as $employee) {
    echo "Processing [{$employee->employee_no}] {$employee->name}...\n";

    // Use a safer range: parent_code + 001, 002...
    // e.g., 1150001
    $lastSubAccount = Account::where('parent_id', $parentAccount->id)
        ->where('code', 'like', $parentAccount->code . '%')
        ->whereRaw('LENGTH(code) > 4')
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
