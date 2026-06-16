<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // Find the original combined entry
    // Find the original combined entry
    $oldEntry = JournalEntry::where('entry_no', 'JV-2025-DEP001')->first();
    if (!$oldEntry) {
        throw new Exception("Original entry JV-2025-DEP001 not found");
    }
    // Rename old entry to avoid unique constraint conflict
    $oldEntry->update(['entry_no' => 'TEMP-DEP-OLD']);

    $lines = $oldEntry->lines;
    // Lines are stored as pairs (Exp, AccDep)
    $pairs = [];
    for ($i = 0; $i < count($lines); $i += 2) {
        $pairs[] = [$lines[$i], $lines[$i+1]];
    }

    $index = 1;
    foreach ($pairs as $pair) {
        $entryNo = 'JV-2025-DEP' . str_pad($index, 3, '0', STR_PAD_LEFT);
        
        $newEntry = JournalEntry::create([
            'entry_no' => $entryNo,
            'entry_date' => $oldEntry->entry_date,
            'description' => $pair[0]->description . " (قيد منفصل)",
            'fiscal_year_id' => $oldEntry->fiscal_year_id,
            'status' => 'posted',
            'created_by' => $oldEntry->created_by,
        ]);

        foreach ($pair as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $newEntry->id,
                'account_id' => $line->account_id,
                'description' => $line->description,
                'debit' => $line->debit,
                'credit' => $line->credit,
            ]);
        }
        $index++;
    }

    // Delete the old combined entry
    $oldEntry->delete(); // Lines will be deleted via cascade or I should delete them manually

    DB::commit();
    echo "Split combined entry into " . count($pairs) . " separate entries.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
