<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('government_expenses', function (Blueprint $table) {
            $table->foreignId('payment_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('government_expenses', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropForeign(['expense_account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['payment_account_id', 'expense_account_id', 'journal_entry_id']);
        });
    }
};
