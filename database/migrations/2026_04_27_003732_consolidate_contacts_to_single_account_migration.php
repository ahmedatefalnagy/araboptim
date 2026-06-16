<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Account;
use App\Models\JournalEntryLine;
use App\Models\Contact;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Account::count() === 0) {
            return;
        }

        DB::transaction(function () {
            // 1. Ensure the 'Contacts' account exists. We will use 1130 as the base.
            $contactsAccount = Account::where('code', '1130')->first();
            
            if (!$contactsAccount) {
                // If 1130 doesn't exist for some reason, create it under Current Assets (assuming ID 6 is Current Assets)
                $contactsAccount = Account::create([
                    'parent_id' => 6,
                    'code' => '1130',
                    'name' => 'جهات الاتصال',
                    'account_type_id' => 1, // Asset
                    'is_postable' => true,
                    'is_active' => true
                ]);
            } else {
                $contactsAccount->update(['name' => 'جهات الاتصال']);
            }

            // 2. Identify target accounts to be merged
            $suppliersAccount = Account::where('code', '2110')->first();
            $relatedPartiesAccount = Account::where('code', '1155')->first();

            // 3. Move transactions from Suppliers to Contacts
            if ($suppliersAccount) {
                JournalEntryLine::where('account_id', $suppliersAccount->id)
                    ->update(['account_id' => $contactsAccount->id]);
            }

            // 4. Move transactions from Related Parties to Contacts
            if ($relatedPartiesAccount) {
                JournalEntryLine::where('account_id', $relatedPartiesAccount->id)
                    ->update(['account_id' => $contactsAccount->id]);
            }

            // 5. Update all Contacts to point to this single account for both Receivable and Payable
            Contact::query()->update([
                'receivable_account_id' => $contactsAccount->id,
                'payable_account_id' => $contactsAccount->id
            ]);

            // 6. Delete redundant accounts
            if ($suppliersAccount) {
                // Ensure no children exist before deleting, or re-parent them if any
                Account::where('parent_id', $suppliersAccount->id)->update(['parent_id' => $contactsAccount->id]);
                $suppliersAccount->delete();
            }

            if ($relatedPartiesAccount) {
                Account::where('parent_id', $relatedPartiesAccount->id)->update(['parent_id' => $contactsAccount->id]);
                $relatedPartiesAccount->delete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reversal is complex as we are merging data. 
    }
};
