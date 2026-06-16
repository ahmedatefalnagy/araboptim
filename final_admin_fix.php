<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use Illuminate\Support\Facades\DB;

$acc = Account::where('code', '1123')->first();
$accOwner = Account::where('code', '3200')->first();

// 1. Remove any previous opening adjustment
JournalEntryLine::where('account_id', $acc->id)->where('description', 'like', '%رصيد افتتاحي%')->delete();

// 2. Add the correct adjustment to make the balance 14.59
// Current balance is 0.00, so we just add 14.59 debit.
$entry = JournalEntry::create([
    'entry_no' => 'AD-OPEN-FIX',
    'entry_date' => '2025-01-01',
    'description' => 'ضبط الرصيد الافتتاحي للإدارة',
    'fiscal_year_id' => 1,
    'status' => 'posted',
    'created_by' => 1
]);

JournalEntryLine::create([
    'journal_entry_id' => $entry->id,
    'account_id' => $acc->id,
    'description' => 'رصيد افتتاحي معدل',
    'debit' => 14.59,
    'credit' => 0
]);

JournalEntryLine::create([
    'journal_entry_id' => $entry->id,
    'account_id' => $accOwner->id,
    'description' => 'مقابل رصيد افتتاحي معدل',
    'debit' => 0,
    'credit' => 14.59
]);

echo "Administration account adjusted to 14.59.\n";
