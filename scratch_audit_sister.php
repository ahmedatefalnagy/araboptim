<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$names = ['شركة انجز تك للتقنية المعلومات', 'شركة التفاؤل العربية للخدمات اللوجستية', 'مؤسسة بريق النجمة للمقاولات', 'مؤسسة بنية الريادة للمقاولات'];
$accounts = DB::table('accounts')->whereIn('name', $names)->get();

echo "Found Accounts: " . $accounts->count() . "\n";
foreach($accounts as $acc) {
    $count = DB::table('journal_entry_lines')->where('account_id', $acc->id)->count();
    echo "ID: {$acc->id}, Code: {$acc->code}, Name: {$acc->name}, Entries: {$count}\n";
}

$others = DB::table('accounts')->where('name', 'like', '%شركة%')->orWhere('name', 'like', '%شقيقة%')->get();
echo "\nOther related accounts:\n";
foreach($others as $acc) {
    $count = DB::table('journal_entry_lines')->where('account_id', $acc->id)->count();
    echo "ID: {$acc->id}, Code: {$acc->code}, Name: {$acc->name}, Entries: {$count}\n";
}
