<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

$fiscalYearId = App\Models\FiscalYear::where('is_closed', false)->first()?->id;
$accOwner = Account::where('code', '3200')->first();

$assets = [
    ['code' => '1220', 'cost' => 236525, 'acc_dep' => 70956],  // آلات ومعدات
    ['code' => '1250', 'cost' => 301570, 'acc_dep' => 40709],  // سيارات
    ['code' => '1230', 'cost' => 40850,  'acc_dep' => 6417],   // الأثاث والمفروشات
    ['code' => '1260', 'cost' => 103250, 'acc_dep' => 20650],  // أجهزة كهربائية وحاسب آلي
];

// Mapping Depreciation Accounts
$depAccs = [
    '1220' => '1244',
    '1250' => '1242',
    '1230' => '1243',
    '1260' => '1245'
];

DB::beginTransaction();
try {
    $entry = JournalEntry::create([
        'entry_no' => 'ASSET-OPEN-2025',
        'entry_date' => '2025-01-01',
        'description' => 'إثبات الأرصدة الافتتاحية للأصول الثابتة لعام 2025',
        'fiscal_year_id' => $fiscalYearId,
        'status' => 'posted',
        'created_by' => 1,
    ]);

    $netTotal = 0;
    foreach ($assets as $asset) {
        $costAcc = Account::where('code', $asset['code'])->first();
        $depAcc = Account::where('code', $depAccs[$asset['code']])->first();

        // Asset Cost (Debit)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $costAcc->id,
            'description' => 'رصيد افتتاحي - تكلفة الأصل (' . $costAcc->name . ')',
            'debit' => $asset['cost'],
            'credit' => 0,
        ]);

        // Accumulated Depreciation (Credit)
        JournalEntryLine::create([
            'journal_entry_id' => $entry->id,
            'account_id' => $depAcc->id,
            'description' => 'رصيد افتتاحي - مجمع إهلاك (' . $depAcc->name . ')',
            'debit' => 0,
            'credit' => $asset['acc_dep'],
        ]);

        $netTotal += ($asset['cost'] - $asset['acc_dep']);
    }

    // Contra Side (Owner Current)
    JournalEntryLine::create([
        'journal_entry_id' => $entry->id,
        'account_id' => $accOwner->id,
        'description' => 'مقابل الأرصدة الافتتاحية للأصول الثابتة 2025',
        'debit' => 0,
        'credit' => $netTotal,
    ]);

    DB::commit();
    echo "Fixed Assets opening balances successfully registered for 2025. Net Total: $netTotal\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
