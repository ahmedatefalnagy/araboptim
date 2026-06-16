<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $contacts = DB::table('contacts')->whereNotNull('account_id')->get();

        foreach ($contacts as $contact) {
            $oldAccountId = $contact->account_id;
            
            // Determine the correct Control Account
            $controlAccountId = null;
            if ($contact->is_customer || $contact->type === 'customer') {
                $controlAccountId = $contact->receivable_account_id ?? DB::table('accounts')->where('code', '1130')->value('id');
            } elseif ($contact->is_supplier || $contact->type === 'supplier') {
                $controlAccountId = $contact->payable_account_id ?? DB::table('accounts')->where('code', '2110')->value('id');
            } elseif ($contact->is_related_party || $contact->type === 'partner') {
                $controlAccountId = $contact->receivable_account_id ?? DB::table('accounts')->where('code', '1155')->value('id');
            }

            if ($controlAccountId && $oldAccountId != $controlAccountId) {
                // 1. Update all journal entry lines
                DB::table('journal_entry_lines')
                    ->where('account_id', $oldAccountId)
                    ->update([
                        'account_id' => $controlAccountId,
                        'contact_id' => $contact->id
                    ]);

                // 2. Update other potential references (Invoices, Vouchers, etc.)
                DB::table('invoices')->where('base_account_id', $oldAccountId)->update(['base_account_id' => $controlAccountId]);
                DB::table('vouchers')->where('debit_account_id', $oldAccountId)->update(['debit_account_id' => $controlAccountId]);
                DB::table('vouchers')->where('credit_account_id', $oldAccountId)->update(['credit_account_id' => $controlAccountId]);
                
                // 3. Mark the contact's old account for deletion (null out the reference)
                DB::table('contacts')->where('id', $contact->id)->update(['account_id' => null]);
                
                // 4. Delete the old account if it has no more movements (it shouldn't now)
                $hasMovements = DB::table('journal_entry_lines')->where('account_id', $oldAccountId)->exists();
                if (!$hasMovements) {
                    DB::table('accounts')->where('id', $oldAccountId)->delete();
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data migration is generally hard to reverse perfectly without a log
    }
};
