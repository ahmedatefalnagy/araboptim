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

$spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي المشتريات.xlsx');
$rows = $spreadsheet->getActiveSheet()->toArray();

$accPurchBank = Account::where('code', '1122')->first(); // Al-Rajhi Purchases
$accMainBank = Account::where('code', '1121')->first();  // Al-Rajhi Main
$accAdminBank = Account::where('code', '1123')->first(); // Al-Rajhi Admin
$accOwner = Account::where('code', '3200')->first();      // Owner Current
$accCharges = Account::where('code', '5420')->first();   // Bank Charges (assuming)

if (!$accCharges) {
    $accCharges = Account::where('name', 'like', '%بنكي%')->orWhere('name', 'like', '%رسوم%')->first();
}

$fiscalYearId = App\Models\FiscalYear::where('is_closed', false)->first()?->id;

DB::beginTransaction();
try {
    $count = 0;
    foreach ($rows as $i => $row) {
        if ($i < 3) continue;
        $rawDate = $row[0];
        if (empty($rawDate)) continue;

        try {
            $date = Carbon::parse($rawDate);
            if ($date->lessThan('2025-10-01')) continue; // Only Q4
        } catch (\Exception $e) {
            continue;
        }

        $desc = trim($row[2]);
        $debit = (float)str_replace(',', '', $row[3] ?? 0);
        $credit = (float)str_replace(',', '', $row[4] ?? 0);

        if ($debit == 0 && $credit == 0) continue;

        // Determine contra account
        $contraAcc = null;
        if (strpos($desc, 'الرئيسى') !== false) $contraAcc = $accMainBank;
        elseif (strpos($desc, 'الادارة') !== false) $contraAcc = $accAdminBank;
        elseif (strpos($desc, 'المدير العام') !== false) $contraAcc = $accOwner;
        elseif (strpos($desc, 'رسوم') !== false) $contraAcc = $accCharges;
        
        if (!$contraAcc) {
            $contraAcc = $accOwner; // Default to Owner if unknown, since most are manager related
        }

        if (!$accPurchBank || !$contraAcc) continue;

        $entry = JournalEntry::create([
            'entry_no' => getNextEntryNo(),
            'entry_date' => $date->format('Y-m-d'),
            'description' => $desc,
            'fiscal_year_id' => $fiscalYearId,
            'status' => 'posted',
            'created_by' => 1,
        ]);

        // Bank Side (ID 9)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $accPurchBank->id,
            'description' => $desc,
            'debit' => $debit,
            'credit' => $credit,
        ]);

        // Contra Side
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $contraAcc->id,
            'description' => $desc,
            'debit' => $credit, // Opposite
            'credit' => $debit,
        ]);

        $count++;
    }
    DB::commit();
    echo "Successfully imported $count Q4 movements for Al-Rajhi Purchases.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

function getNextEntryNo() {
    $last = JournalEntry::orderBy('id', 'desc')->first();
    $nextId = $last ? $last->id + 1 : 1;
    return 'JV-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
}
