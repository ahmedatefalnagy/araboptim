<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Voucher;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\DB;

$vouchers = Voucher::whereNotNull('journal_entry_id')->get();
$missing = [];
foreach($vouchers as $v) {
    if(!JournalEntry::find($v->journal_entry_id)) {
        $missing[] = [
            'voucher_id' => $v->id,
            'voucher_no' => $v->voucher_no,
            'je_id' => $v->journal_entry_id,
            'amount' => $v->amount,
            'contact_id' => $v->contact_id,
        ];
    }
}

echo "Found " . count($missing) . " vouchers with missing journal entries.\n";
foreach($missing as $m) {
    echo "Voucher ID: {$m['voucher_id']}, No: {$m['voucher_no']}, Amount: {$m['amount']}, Linked JE ID: {$m['je_id']}\n";
}
