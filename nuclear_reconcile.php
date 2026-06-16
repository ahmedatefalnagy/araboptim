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
$bankCodes = ['1121', '1122', '1123'];
$files = [
    '1121' => 'الراجحي الرئيسي.xlsx',
    '1122' => 'الراجحي المشتريات.xlsx',
    '1123' => 'الراجحي الادارة.xlsx'
];

DB::beginTransaction();
try {
    // 1. Raw delete all lines for these accounts
    $ids = Account::whereIn('code', $bankCodes)->pluck('id')->toArray();
    $entriesToDelete = JournalEntry::whereHas('lines', function($q) use ($ids) {
        $q->whereIn('account_id', $ids);
    })->get();
    
    foreach($entriesToDelete as $e) {
        $e->delete();
    }
    echo "Deleted " . $entriesToDelete->count() . " journal entries touching banks " . implode(',', $bankCodes) . "\n";

    // 3. Re-import from Excel
    foreach ($files as $code => $fileName) {
        $account = Account::where('code', $code)->first();
        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        // Account 1123 needs opening balance adjustment based on our previous calculation
        $openingBalance = 0;
        if ($code == '1123') $openingBalance = -14.59;

        if ($openingBalance != 0) {
            $entry = JournalEntry::create([
                'entry_no' => 'OPN-' . $code,
                'entry_date' => '2025-01-01',
                'description' => 'رصيد افتتاحي 2025',
                'fiscal_year_id' => $fiscalYearId,
                'status' => 'posted',
                'created_by' => 1,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id, 'account_id' => $account->id, 'description' => 'رصيد افتتاحي', 'debit' => $openingBalance > 0 ? $openingBalance : 0, 'credit' => $openingBalance < 0 ? abs($openingBalance) : 0,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id, 'account_id' => $accOwner->id, 'description' => 'مقابل رصيد افتتاحي', 'debit' => $openingBalance < 0 ? abs($openingBalance) : 0, 'credit' => $openingBalance > 0 ? $openingBalance : 0,
            ]);
        }

        $count = 0;
        foreach ($rows as $i => $row) {
            if ($i < 3) continue;
            $rawDate = $row[0];
            if (empty($rawDate)) continue;
        if (trim($row[2] ?? '') == 'رصيد الراجحي الرئيسي' && $debit == 0 && $credit == 0) continue;

            try {
                $date = Carbon::parse($rawDate);
                if ($date->year != 2025) continue;
            } catch (\Exception $e) { continue; }

            $desc = trim($row[2] ?? '');
            $debit = (float)str_replace(',', '', $row[3] ?? 0);
            $credit = (float)str_replace(',', '', $row[4] ?? 0);

            if ($debit == 0 && $credit == 0) continue;

            $entry = JournalEntry::create([
                'entry_no' => 'FIXED-' . $code . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'entry_date' => $date->format('Y-m-d'),
                'description' => $desc,
                'fiscal_year_id' => $fiscalYearId,
                'status' => 'posted',
                'created_by' => 1,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id, 'account_id' => $account->id, 'description' => $desc, 'debit' => $debit, 'credit' => $credit,
            ]);

            JournalEntryLine::create([
                'journal_entry_id' => $entry->id, 'account_id' => $accOwner->id, 'description' => $desc . ' (مطابقة بنكية)', 'debit' => $credit, 'credit' => $debit,
            ]);

            $count++;
        }
        echo "Imported $count entries for $code.\n";
    }

    DB::commit();
    echo "NUCLEAR RECONCILE COMPLETE.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
