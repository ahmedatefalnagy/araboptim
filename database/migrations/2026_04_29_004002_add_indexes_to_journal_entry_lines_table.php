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
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index('entry_date');
            $table->index('status');
        });

        Schema::table('journal_entry_lines', function (Blueprint $table) {
            // These are often used for filtering in ledger reports
            // Although account_id and contact_id might already be foreign keys, 
            // adding explicit indexes can sometimes help, but entry_date is the most critical.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['entry_date']);
            $table->dropIndex(['status']);
        });
    }
};
