<?php
require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Account;

// Find all Rajhi accounts
$accs = Account::where('name', 'like', '%راجحي%')->get();
echo "=== حسابات الراجحي ===\n";
foreach ($accs as $a) {
    echo "ID: {$a->id} | Code: {$a->code} | Name: {$a->name} | Type: " . ($a->type->code ?? '-') . " | Normal: " . ($a->type->normal_balance ?? '-') . "\n";
}

// Find the main Rajhi account
$mainAcc = Account::where('name', 'like', '%راجحي%')->where('name', 'like', '%رئيسي%')->first();
if (!$mainAcc) {
    $mainAcc = Account::where('name', 'like', '%راجحي%')->where('is_postable', true)->first();
}

if (!$mainAcc) {
    echo "\nNo main Rajhi account found.\n";
    exit;
}

echo "\n=== الحساب الرئيسي ===\n";
echo "ID: {$mainAcc->id} | Code: {$mainAcc->code} | Name: {$mainAcc->name}\n";
echo "Type: " . ($mainAcc->type->code ?? '-') . " | Normal Balance: " . ($mainAcc->type->normal_balance ?? '-') . "\n";

// Total summary - ALL time 
$total = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', $mainAcc->id)
    ->where('je.status', 'posted')
    ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c, COUNT(*) as cnt')
    ->first();

echo "\n=== إجمالي الحركات (الكل) ===\n";
echo "Total Debit: {$total->d}\n";
echo "Total Credit: {$total->c}\n";
echo "Lines Count: {$total->cnt}\n";
$balance = $total->d - $total->c;
echo "Balance (Debit - Credit): {$balance}\n";

// Monthly breakdown
echo "\n=== تفصيل شهري ===\n";
$monthly = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', $mainAcc->id)
    ->where('je.status', 'posted')
    ->selectRaw("DATE_FORMAT(je.entry_date, '%Y-%m') as month, SUM(jel.debit) as d, SUM(jel.credit) as c, COUNT(*) as cnt")
    ->groupByRaw("DATE_FORMAT(je.entry_date, '%Y-%m')")
    ->orderBy('month')
    ->get();

$runBal = 0;
foreach ($monthly as $m) {
    $net = $m->d - $m->c;
    $runBal += $net;
    echo "Month: {$m->month} | Debit: {$m->d} | Credit: {$m->c} | Net: {$net} | Running Balance: {$runBal} | Lines: {$m->cnt}\n";
}

// Check the opening balance entry specifically
echo "\n=== قيد رصيد أول المدة (208799.34) ===\n";
$openingEntries = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', $mainAcc->id)
    ->where('je.status', 'posted')
    ->where(function($q) {
        $q->where('jel.debit', 208799.34)
          ->orWhere('jel.credit', 208799.34);
    })
    ->select('je.entry_no', 'je.entry_date', 'je.description as je_desc', 'jel.debit', 'jel.credit', 'jel.description')
    ->get();

foreach ($openingEntries as $e) {
    echo "Entry: {$e->entry_no} | Date: {$e->entry_date} | D: {$e->debit} | C: {$e->credit} | Desc: {$e->description}\n";
}

// Check balance up to end of March
echo "\n=== رصيد حتى نهاية مارس 2025 ===\n";
$marchBalance = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', $mainAcc->id)
    ->where('je.status', 'posted')
    ->where('je.entry_date', '<=', '2025-03-31')
    ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c')
    ->first();

echo "Debit until March: {$marchBalance->d}\n";
echo "Credit until March: {$marchBalance->c}\n";
echo "Balance until March (D-C): " . ($marchBalance->d - $marchBalance->c) . "\n";

// Check balance April onwards
echo "\n=== حركات أبريل فقط ===\n";
$aprilBalance = DB::table('journal_entry_lines as jel')
    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
    ->where('jel.account_id', $mainAcc->id)
    ->where('je.status', 'posted')
    ->whereBetween('je.entry_date', ['2025-04-01', '2025-04-30'])
    ->selectRaw('SUM(jel.debit) as d, SUM(jel.credit) as c, COUNT(*) as cnt')
    ->first();

echo "April Debit: {$aprilBalance->d}\n";
echo "April Credit: {$aprilBalance->c}\n";
echo "April Net: " . ($aprilBalance->d - $aprilBalance->c) . "\n";
echo "April Lines: {$aprilBalance->cnt}\n";
