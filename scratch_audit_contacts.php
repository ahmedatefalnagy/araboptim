<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$names = ['شركة انجز تك للتقنية المعلومات', 'شركة التفاؤل العربية للخدمات اللوجستية', 'مؤسسة بريق النجمة للمقاولات', 'مؤسسة بنية الريادة للمقاولات'];
$contacts = DB::table('contacts')->whereIn('name', $names)->get();

echo "Found Contacts: " . $contacts->count() . "\n";
foreach($contacts as $c) {
    echo "ID: {$c->id}, Name: {$c->name}, Account ID: {$c->account_id}\n";
    $acc = DB::table('accounts')->where('id', $c->account_id)->first();
    if($acc) {
        echo "  Linked Account: {$acc->code} - {$acc->name}\n";
    } else {
        echo "  Linked Account: MISSING (ID: {$c->account_id})\n";
    }
}
