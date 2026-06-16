<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

// Accounts for Depreciation Expense
$expAccs = [
    '1220' => '5251', // آلات ومعدات
    '1250' => '5252', // سيارات
    '1230' => '5253', // أثاث ومفروشات
    '1260' => '5254', // أجهزة كهربائية وحاسب آلي
];

// Values from 2024 Table (إهلاك السنة)
$depValues = [
    '5251' => 35478,
    '5252' => 23909,
    '5253' => 3917,
    '5254' => 11800
];

$accRetained = Account::where('code', '3300')->first(); // Retained Earnings for closing

DB::beginTransaction();
try {
    $entry = JournalEntry::create([
        'entry_no' => 'DEP-EXP-2024',
        'entry_date' => '2024-12-31',
        'description' => 'إثبات مصروفات الإهلاك السنوية لعام 2024',
        'fiscal_year_id' => 1, // Assuming 2024 is available or generic
        'status' => 'posted',
        'created_by' => 1,
    ]);

    $totalDep = 0;
    foreach ($depValues as $code => $value) {
        $acc = Account::where('code', $code)->first();
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $acc->id,
            'description' => 'مصروف إهلاك عام 2024 - ' . $acc->name,
            'debit' => $value,
            'credit' => 0,
        ]);
        $totalDep += $value;
    }

    // Contra Side (Closing to Retained Earnings)
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $accRetained->id,
        'description' => 'إقفال مصروفات إهلاك 2024 في الأرباح المبقاة',
        'debit' => 0,
        'credit' => $totalDep,
    ]);

    DB::commit();
    echo "Depreciation Expenses for 2024 successfully registered. Total: $totalDep\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
