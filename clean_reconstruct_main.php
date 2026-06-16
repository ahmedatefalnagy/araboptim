<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

DB::beginTransaction();
try {
    // 1. Clear existing 2025 entries for Account 8
    $entriesToDelete = JournalEntry::whereHas('lines', function($q) {
        $q->where('account_id', 8);
    })->whereYear('entry_date', 2025)->get();
    
    foreach($entriesToDelete as $e) {
        $e->delete();
    }
    echo "Deleted " . $entriesToDelete->count() . " existing entries for account 8.\n";

    // 2. Re-import from Excel
    $spreadsheet = PhpOffice\PhpSpreadsheet\IOFactory::load('الراجحي الرئيسي.xlsx');
    $rows = $spreadsheet->getActiveSheet()->toArray();

    $accMainBank = Account::where('code', '1121')->first();
    $accPurchBank = Account::where('code', '1122')->first();
    $accAdminBank = Account::where('code', '1123')->first();
    $accCustomers = Account::where('code', '1130')->first();
    $accSuppliers = Account::where('code', '2110')->first();
    $accOwner = Account::where('code', '3200')->first();
    $accBankCharges = Account::where('code', '5420')->first() ?: Account::where('name', 'like', '%رسوم%')->first();
    $fiscalYearId = App\Models\FiscalYear::where('is_closed', false)->first()?->id;

    $count = 0;
    foreach ($rows as $i => $row) {
        if ($i < 3) continue;
        $rawDate = $row[0];
        if (empty($rawDate) || trim($row[2]) == 'رصيد الراجحي الرئيسي') continue;

        try {
            $date = Carbon::parse($rawDate);
            if ($date->year != 2025) continue;
        } catch (\Exception $e) { continue; }

        $desc = trim($row[2]);
        $debit = (float)str_replace(',', '', $row[3] ?? 0);
        $credit = (float)str_replace(',', '', $row[4] ?? 0);

        if ($debit == 0 && $credit == 0) continue;

        $contraAcc = $accOwner;
        if (strpos($desc, 'تحصيل') !== false || strpos($desc, 'مبيعات') !== false) $contraAcc = $accCustomers;
        elseif (strpos($desc, 'مشتريات') !== false || strpos($desc, 'سداد') !== false) $contraAcc = $accSuppliers;
        elseif (strpos($desc, 'رسوم') !== false) $contraAcc = $accBankCharges;
        elseif (strpos($desc, 'المشتريات') !== false) $contraAcc = $accPurchBank;
        elseif (strpos($desc, 'الادارة') !== false || strpos($desc, 'إدارة') !== false) $contraAcc = $accAdminBank;

        $contact = null;
        if (strpos($desc, 'انماط المستقبل') !== false) $contact = Contact::where('name', 'like', '%انماط المستقبل%')->first();

        $entry = JournalEntry::create([
            'entry_no' => 'MAIN-' . str_pad($i, 5, '0', STR_PAD_LEFT),
            'entry_date' => $date->format('Y-m-d'),
            'description' => $desc,
            'fiscal_year_id' => $fiscalYearId,
            'status' => 'posted',
            'created_by' => 1,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id, 'account_id' => $accMainBank->id, 'description' => $desc, 'debit' => $debit, 'credit' => $credit,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id, 'account_id' => $contraAcc->id, 'contact_id' => $contact?->id, 'description' => $desc, 'debit' => $credit, 'credit' => $debit,
        ]);

        $count++;
    }
    DB::commit();
    echo "Successfully reconstructed $count entries for Al-Rajhi Main Bank.\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
