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

DB::beginTransaction();
try {
    // 1. Repair Missing Entry: Sales Invoice 1021/1022
    $contact = Contact::where('name', 'like', '%انماط المستقبل%')->first();
    $accRajhi = Account::where('code', '1121')->first();
    $accSales = Account::where('code', '4100')->first();
    $accTax = Account::where('code', '2120')->first();
    $fiscalYearId = App\Models\FiscalYear::where('is_closed', false)->first()?->id;

    if ($accRajhi && $accSales && $accTax) {
        $entry = JournalEntry::create([
            'entry_no' => getNextEntryNo(),
            'entry_date' => '2025-12-01',
            'description' => 'إثبات فاتورة مبيعات رقم 1021 و 1022 - مؤسسة أنماط المستقبل',
            'fiscal_year_id' => $fiscalYearId,
            'status' => 'posted', // Create directly as posted
            'created_by' => 1,
        ]);

        JournalEntryLine::create([
            'journal_entry_id' => $entry->id, 'account_id' => $accRajhi->id, 'contact_id' => $contact?->id,
            'description' => 'قيمة الفاتورة 1021/1022', 'debit' => 200617.5, 'credit' => 0
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id, 'account_id' => $accSales->id, 'contact_id' => null,
            'description' => 'مبيعات', 'debit' => 0, 'credit' => 174450
        ]);
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id, 'account_id' => $accTax->id, 'contact_id' => null,
            'description' => 'ضريبة القيمة المضافة', 'debit' => 0, 'credit' => 26167.5
        ]);
        echo "Repaired and Posted Sales Entry.\n";
    }

    // 2. Repair Missing Entry: Collection 12/08
    if ($accRajhi && $contact) {
        $accContact = $contact->receivableAccount ?? $contact->payableAccount ?? $contact->account;
        if ($accContact) {
            $entry2 = JournalEntry::create([
                'entry_no' => getNextEntryNo(),
                'entry_date' => '2025-12-08',
                'description' => 'تحصيل جزء من مستحقات مؤسسة أنماط المستقبل',
                'fiscal_year_id' => $fiscalYearId,
                'status' => 'posted',
                'created_by' => 1,
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry2->id, 'account_id' => $accRajhi->id, 'contact_id' => $contact->id,
                'description' => 'تحصيل', 'debit' => 45000, 'credit' => 0
            ]);
            JournalEntryLine::create([
                'journal_entry_id' => $entry2->id, 'account_id' => $accContact->id, 'contact_id' => $contact->id,
                'description' => 'تحصيل', 'debit' => 0, 'credit' => 45000
            ]);
            echo "Repaired and Posted Collection Entry.\n";
        }
    }

    // 3. Post ALL remaining Draft entries from today
    $drafts = JournalEntry::where('status', 'draft')->whereDate('created_at', Carbon\Carbon::today())->get();
    foreach ($drafts as $draft) {
        $draft->update(['status' => 'posted']);
    }
    echo "Successfully approved (posted) " . $drafts->count() . " entries.\n";

    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

function getNextEntryNo() {
    $last = JournalEntry::orderBy('id', 'desc')->first();
    $nextId = $last ? $last->id + 1 : 1;
    return 'JV-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
}
