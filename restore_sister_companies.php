<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Models\Account;
use App\Models\Contact;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
try {
    // 1. Ensure Parent Account 1155 exists and is named correctly
    $parent = Account::where('code', '1155')->first();
    if (!$parent) {
        // Find Asset root (usually code 1)
        $assetRoot = Account::where('code', '1')->first();
        $parent = Account::create([
            'parent_id' => $assetRoot ? $assetRoot->id : null,
            'code' => '1155',
            'name' => 'جاري شركات شقيقة',
            'account_type_id' => 1,
            'level' => 3,
            'is_postable' => 0,
            'is_active' => 1,
        ]);
    } else {
        $parent->update(['name' => 'جاري شركات شقيقة', 'is_postable' => 0]);
    }

    $companies = [
        6 => ['name' => 'شركة انجز تك للتقنية المعلومات', 'code_suffix' => '001'],
        8 => ['name' => 'شركة التفاؤل العربية للخدمات اللوجستية', 'code_suffix' => '002'],
        10 => ['name' => 'مؤسسة بريق النجمة للمقاولات', 'code_suffix' => '003'],
        12 => ['name' => 'مؤسسة بنية الريادة للمقاولات', 'code_suffix' => '004'],
    ];

    foreach ($companies as $contactId => $info) {
        $contact = Contact::find($contactId);
        if (!$contact) {
            echo "Warning: Contact ID $contactId not found.\n";
            continue;
        }

        // Create the account
        $newCode = $parent->code . '-' . $info['code_suffix'];
        $account = Account::where('code', $newCode)->first();
        if (!$account) {
            $account = Account::create([
                'parent_id' => $parent->id,
                'code' => $newCode,
                'name' => $info['name'],
                'account_type_id' => 1, // Asset type (Debit side)
                'level' => 4,
                'is_postable' => 1,
                'is_active' => 1,
            ]);
        }

        // Link contact to account
        $contact->update(['account_id' => $account->id]);

        // Move journal lines back!
        $updatedCount = JournalEntryLine::where('contact_id', $contactId)
            ->update(['account_id' => $account->id]);

        echo "Restored: {$info['name']} (Code: {$newCode}), Updated Lines: {$updatedCount}\n";
    }

    DB::commit();
    echo "\nRestoration successful!\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Error during restoration: " . $e->getMessage() . "\n";
}
