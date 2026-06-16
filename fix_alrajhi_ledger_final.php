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
    // 1. Fix Entry 38 - Change date BACK to 2025-01-16
    $je38 = JournalEntry::find(38);
    if ($je38) {
        $je38->update(['entry_date' => '2025-01-16']);
        echo "Reset JV-000038 date to 2025-01-16\n";
    }

    // 2. Add the 2025-05-11 entry of 267
    $missing = [
        ['no' => 'JV-000510', 'amount' => 267.00], // Using a new number
    ];

    foreach($missing as $m) {
        $existing = JournalEntry::where('entry_no', $m['no'] . '-FIX')->first();
        if(!$existing) {
            $je = JournalEntry::create([
                'entry_no' => $m['no'] . '-FIX',
                'entry_date' => '2025-05-11',
                'description' => 'إثبات تحويل من الراجحي الرئيسي الي الراجحي الادارة (2)',
                'fiscal_year_id' => 1,
                'status' => 'posted',
                'created_by' => 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => 18,
                'debit' => $m['amount'],
                'credit' => 0,
                'line_order' => 1
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => 8,
                'debit' => 0,
                'credit' => $m['amount'],
                'line_order' => 2
            ]);

            echo "Restored missing 2025-05-11 entry of 267.00\n";
        }
    }

    DB::commit();
    echo "\nLedger correction complete!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error during correction: " . $e->getMessage() . "\n";
}
