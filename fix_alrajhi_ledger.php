<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // 1. Fix JV-001306 (ID 1320) - Flip Debit/Credit
    $je1320 = JournalEntry::find(1320);
    if ($je1320) {
        $lines = $je1320->lines;
        foreach($lines as $line) {
            $oldDebit = $line->debit;
            $line->update([
                'debit' => $line->credit,
                'credit' => $oldDebit
            ]);
        }
        echo "Fixed JV-001306 (Flipped Debit/Credit)\n";
    }

    // 2. Fix Entry 38 (Date mismatch?)
    $je38 = JournalEntry::find(38);
    if ($je38 && $je38->entry_date->format('Y-m-d') == '2025-01-16') {
        $je38->update(['entry_date' => '2025-05-11']);
        echo "Updated JV-000038 date to 2025-05-11\n";
    }

    // 3. Restore missing entries
    $missing = [
        ['no' => 'JV-000508', 'amount' => 629.36],
        ['no' => 'JV-000509', 'amount' => 435.95],
    ];

    foreach($missing as $m) {
        // Create the entry
        $je = JournalEntry::create([
            'entry_no' => $m['no'] . '-FIX',
            'entry_date' => '2025-05-11',
            'description' => 'إثبات تحويل من الراجحي الرئيسي الي الراجحي الادارة',
            'fiscal_year_id' => 1, // Assuming 1 is 2025
            'status' => 'posted',
            'created_by' => 1,
        ]);

        // Debit Al-Rajhi Management (18)
        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => 18,
            'debit' => $m['amount'],
            'credit' => 0,
            'line_order' => 1
        ]);

        // Credit Al-Rajhi Main (8)
        JournalEntryLine::create([
            'journal_entry_id' => $je->id,
            'account_id' => 8,
            'debit' => 0,
            'credit' => $m['amount'],
            'line_order' => 2
        ]);

        echo "Restored missing entry {$m['no']} (Amount: {$m['amount']})\n";
    }

    DB::commit();
    echo "\nLedger correction complete!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error during correction: " . $e->getMessage() . "\n";
}
