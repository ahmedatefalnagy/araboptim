<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

$fiscalYearId = 1; // 2025

$depreciations = [
    ['exp' => '5251', 'acc' => '1244', 'amount' => 24835.35], // آلات ومعدات
    ['exp' => '5252', 'acc' => '1242', 'amount' => 39129.15], // سيارات
    ['exp' => '5253', 'acc' => '1243', 'amount' => 6886.60],  // أثاث ومفروشات
    ['exp' => '5254', 'acc' => '1245', 'amount' => 16520.00], // أجهزة كهربائية وحاسب آلي
];

DB::beginTransaction();
try {
    $entry = JournalEntry::create([
        'entry_no' => 'DEP-2025-YEARLY',
        'entry_date' => '2025-12-31',
        'description' => 'إثبات مصروفات الإهلاك السنوية التقديرية لعام 2025',
        'fiscal_year_id' => $fiscalYearId,
        'status' => 'posted',
        'created_by' => 1,
    ]);

    foreach ($depreciations as $dep) {
        $expAcc = Account::where('code', $dep['exp'])->first();
        $accAcc = Account::where('code', $dep['acc'])->first();

        // Expense (Debit)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $expAcc->id,
            'description' => 'مصروف إهلاك 2025 - ' . $expAcc->name,
            'debit' => $dep['amount'],
            'credit' => 0,
        ]);

        // Accumulated Depreciation (Credit)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $accAcc->id,
            'description' => 'إهلاك سنة 2025 - ' . $accAcc->name,
            'debit' => 0,
            'credit' => $dep['amount'],
        ]);
    }

    DB::commit();
    echo "Yearly Depreciations for 2025 successfully registered. Total: " . array_sum(array_column($depreciations, 'amount')) . "\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
