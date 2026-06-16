<?php

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Payroll;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use App\Services\JournalEntryService;

$journalService = app(JournalEntryService::class);
$fiscalYear = FiscalYear::where('is_closed', false)->first();

if (!$fiscalYear) {
    die("Error: No open fiscal year found.\n");
}

echo "Fixing orphan HR transactions (missing Journal Entries)...\n\n";

// 1. Check Advances / Custodies
$advances = EmployeeAdvance::all();
foreach ($advances as $advance) {
    $exists = JournalEntry::where('transaction_type', $advance->type)
        ->where('reference_id', $advance->id)
        ->exists();

    if (!$exists) {
        $employee = Employee::find($advance->employee_id);
        if ($employee && $employee->account_id) {
            echo "Generating JV for {$advance->type} ID: {$advance->id} (Employee: {$employee->name})...\n";
            $journalService->create([
                'entry_date' => $advance->date,
                'description' => "صرف {$advance->type} للموظف: {$employee->name} - غرض: {$advance->purpose}",
                'fiscal_year_id' => $fiscalYear->id,
                'transaction_type' => $advance->type,
                'reference_id' => $advance->id,
                'lines' => [
                    [
                        'account_id' => $employee->account_id,
                        'debit' => $advance->amount,
                        'credit' => 0,
                        'description' => "إثبات {$advance->type} - مرجع: {$advance->reference_no}"
                    ],
                    [
                        'account_id' => $advance->payment_account_id ?? 8, // Fallback to provided payment account or default
                        'debit' => 0,
                        'credit' => $advance->amount,
                        'description' => "صرف من الحساب"
                    ]
                ]
            ]);
            echo "DONE.\n";
        } else {
            echo "SKIPPED: Employee or Account ID missing for Advance ID: {$advance->id}\n";
        }
    }
}

// 2. Check Payrolls
$payrolls = Payroll::all();
foreach ($payrolls as $payroll) {
    $exists = JournalEntry::where('transaction_type', 'payroll')
        ->where('reference_id', $payroll->id)
        ->exists();

    if (!$exists) {
        $employee = Employee::find($payroll->employee_id);
        if ($employee && $employee->account_id) {
            echo "Generating JV for Payroll ID: {$payroll->id} (Employee: {$employee->name})...\n";
            $lines = [
                ['account_id' => 46, 'debit' => $payroll->basic_salary, 'credit' => 0, 'description' => "راتب أساسي {$payroll->month}"],
                ['account_id' => 47, 'debit' => ($payroll->gross_salary - $payroll->basic_salary), 'credit' => 0, 'description' => "بدلات {$payroll->month}"],
                ['account_id' => $employee->account_id, 'debit' => 0, 'credit' => $payroll->advance_deduction, 'description' => "خصم سلفة {$payroll->month}"],
                ['account_id' => $payroll->payment_account_id ?? 8, 'debit' => 0, 'credit' => $payroll->net_salary, 'description' => "صافي راتب {$payroll->month}"]
            ];
            // Filter zero lines
            $lines = array_filter($lines, fn($l) => ($l['debit'] > 0 || $l['credit'] > 0));

            $journalService->create([
                'entry_date' => $payroll->payment_date,
                'description' => "راتب شهر {$payroll->month} للموظف: {$employee->name}",
                'fiscal_year_id' => $fiscalYear->id,
                'transaction_type' => 'payroll',
                'reference_id' => $payroll->id,
                'lines' => array_values($lines)
            ]);
            echo "DONE.\n";
        }
    }
}

echo "\nFix complete.\n";
