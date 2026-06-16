<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$fiscalYearId = App\Models\FiscalYear::where('is_closed', false)->first()?->id;
$accOwner = Account::where('code', '3200')->first();

function fixAccount($accCode, $fileName, $fiscalYearId, $accOwner) {
    $account = Account::where('code', $accCode)->first();
    if (!$account) return "Account $accCode not found.\n";

    DB::beginTransaction();
    try {
        // 1. Delete 2025 entries for this account
        $entriesToDelete = JournalEntry::whereHas('lines', function($q) use ($account) {
            $q->where('account_id', $account->id);
        })->whereYear('entry_date', 2025)->get();
        
        foreach($entriesToDelete as $e) {
            $e->delete();
        }
        echo "Deleted " . $entriesToDelete->count() . " entries for account $accCode.\n";

        // 2. Import from Excel
        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $count = 0;
        foreach ($rows as $i => $row) {
            if ($i < 3) continue;
            $rawDate = $row[0];
            if (empty($rawDate) || strpos($row[2], 'رصيد') !== false) continue;

            try {
                $date = Carbon::parse($rawDate);
                if ($date->year != 2025) continue;
            } catch (\Exception $e) { continue; }

            $desc = trim($row[2]);
            $debit = (float)str_replace(',', '', $row[3] ?? 0);
            $credit = (float)str_replace(',', '', $row[4] ?? 0);

            if ($debit == 0 && $credit == 0) continue;

            $entry = JournalEntry::create([
                'entry_no' => 'FIX-' . $accCode . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'entry_date' => $date->format('Y-m-d'),
                'description' => $desc,
                'fiscal_year_id' => $fiscalYearId,
                'status' => 'posted',
                'created_by' => 1,
            ]);

            // Targeted Bank Side
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id, 'account_id' => $account->id, 'description' => $desc, 'debit' => $debit, 'credit' => $credit,
            ]);

            // Contra Side (Owner Current) - SAFE: NEVER TOUCHES MAIN BANK
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id, 'account_id' => $accOwner->id, 'description' => $desc . ' (قيد مطابقة)', 'debit' => $credit, 'credit' => $debit,
            ]);

            $count++;
        }
        DB::commit();
        echo "Successfully reconstructed $count entries for $accCode ($fileName).\n";
    } catch (\Exception $e) {
        DB::rollBack();
        echo "Error fixing $accCode: " . $e->getMessage() . "\n";
    }
}

// Fix Purchases
fixAccount('1122', 'الراجحي المشتريات.xlsx', $fiscalYearId, $accOwner);

// Fix Admin
fixAccount('1123', 'الراجحي الادارة.xlsx', $fiscalYearId, $accOwner);
