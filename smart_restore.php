<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;

$sourceDb = 'backup_restore';
$targetDb = config('database.connections.mysql.database');

echo "Starting restoration from $sourceDb to $targetDb...\n";

// 1. Identify entries in backup that are missing in current
$oldEntries = DB::connection('mysql')->table($sourceDb . '.journal_entries')->get();
$currentEntries = JournalEntry::all();

$missingEntriesCount = 0;
foreach ($oldEntries as $old) {
    // Match by entry_no or (date and description)
    $exists = $currentEntries->contains(function($e) use ($old) {
        return $e->entry_no === $old->entry_no || 
               ($e->entry_date->format('Y-m-d') === date('Y-m-d', strtotime($old->entry_date)) && $e->description === $old->description);
    });

    if (!$exists) {
        // Restore this entry
        DB::beginTransaction();
        try {
            $newEntryId = DB::table('journal_entries')->insertGetId([
                'entry_no' => $old->entry_no,
                'entry_date' => $old->entry_date,
                'description' => $old->description,
                'fiscal_year_id' => $old->fiscal_year_id,
                'status' => $old->status,
                'transaction_type' => $old->transaction_type,
                'reference_id' => $old->reference_id,
                'created_by' => $old->created_by,
                'posted_by' => $old->posted_by,
                'posted_at' => $old->posted_at,
                'created_at' => $old->created_at,
                'updated_at' => $old->updated_at,
            ]);

            // Restore lines
            $oldLines = DB::connection('mysql')->table($sourceDb . '.journal_entry_lines')->where('journal_entry_id', $old->id)->get();
            foreach ($oldLines as $line) {
                DB::table('journal_entry_lines')->insert([
                    'journal_entry_id' => $newEntryId,
                    'account_id' => $line->account_id,
                    'contact_id' => $line->contact_id,
                    'description' => $line->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                    'created_at' => $line->created_at,
                    'updated_at' => $line->updated_at,
                ]);
            }
            DB::commit();
            $missingEntriesCount++;
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error restoring entry {$old->entry_no}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Successfully restored $missingEntriesCount missing entries.\n";

// 2. Fix the "Owner Current" mappings for bank entries I created today
$accOwner = DB::table('accounts')->where('code', '3200')->first();
$bankAccounts = DB::table('accounts')->whereIn('code', ['1121', '1122', '1123'])->pluck('id')->toArray();

$todayEntries = JournalEntry::where('entry_no', 'like', 'MAIN-%')
    ->orWhere('entry_no', 'like', 'PUR-%')
    ->orWhere('entry_no', 'like', 'ADM-%')
    ->get();

$fixedMappingsCount = 0;
foreach ($todayEntries as $entry) {
    // Find the line that is in Owner Current
    $ownerLine = $entry->lines()->where('account_id', $accOwner->id)->first();
    if (!$ownerLine) continue;

    $amount = (float)($ownerLine->debit + $ownerLine->credit);
    $date = $entry->entry_date->format('Y-m-d');

    // Look for a matching entry in the backup that has a line with this amount AND is NOT a bank account
    $match = DB::connection('mysql')->table($sourceDb . '.journal_entry_lines as jel')
        ->join($sourceDb . '.journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
        ->where('je.entry_date', 'like', $date . '%')
        ->where(function($q) use ($amount) {
            $q->where('jel.debit', $amount)->orWhere('jel.credit', $amount);
        })
        ->whereNotIn('jel.account_id', $bankAccounts)
        ->select('jel.account_id', 'jel.description', 'je.description as entry_desc')
        ->first();

    if ($match) {
        $ownerLine->update([
            'account_id' => $match->account_id,
            'description' => $match->description ?: $match->entry_desc
        ]);
        $fixedMappingsCount++;
    }
}

echo "Successfully fixed $fixedMappingsCount bank transaction mappings.\n";
