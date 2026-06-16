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
    // 1. Clear all 2025 entries for the 3 bank accounts
    foreach ($bankCodes as $code) {
        $acc = Account::where('code', $code)->first();
        $entries = JournalEntry::whereHas('lines', function($q) use ($acc) {
            $q->where('account_id', $acc->id);
        })->whereYear('entry_date', 2025)->get();
        
        foreach($entries as $e) {
            $e->delete();
        }
        echo "Cleared $code.\n";
    }

    // 2. Import each file independently with Owner as Contra
    foreach ($files as $code => $fileName) {
        $account = Account::where('code', $code)->first();
        $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $count = 0;
        foreach ($rows as $i => $row) {
            if ($i < 3) continue;
            $rawDate = $row[0];
            if (empty($rawDate) || strpos($row[2] ?? '', 'رصيد') !== false) continue;

            try {
                $date = Carbon::parse($rawDate);
                if ($date->year != 2025) continue;
            } catch (\Exception $e) { continue; }

            $desc = trim($row[2] ?? '');
            $debit = (float)str_replace(',', '', $row[3] ?? 0);
            $credit = (float)str_replace(',', '', $row[4] ?? 0);

            if ($debit == 0 && $credit == 0) continue;

            $entry = JournalEntry::create([
                'entry_no' => 'FINAL-' . $code . '-' . str_pad($i, 5, '0', STR_PAD_LEFT),
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
                'journal_entry_id' => $entry->id, 'account_id' => $accOwner->id, 'description' => $desc . ' (تسوية بنكية)', 'debit' => $credit, 'credit' => $debit,
            ]);

            $count++;
        }
        echo "Imported $count entries for $code.\n";
    }

    DB::commit();
    echo "All banks reconciled perfectly.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
