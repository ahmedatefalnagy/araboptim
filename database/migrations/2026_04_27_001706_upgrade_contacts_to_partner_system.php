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
        Schema::table('contacts', function (Blueprint $table) {
            $table->foreignId('receivable_account_id')->nullable()->after('account_id')->constrained('accounts')->nullOnDelete();
            $table->foreignId('payable_account_id')->nullable()->after('receivable_account_id')->constrained('accounts')->nullOnDelete();
            $table->boolean('is_customer')->default(false)->after('type');
            $table->boolean('is_supplier')->default(false)->after('is_customer');
            $table->boolean('is_related_party')->default(false)->after('is_supplier');
        });

        // Migrate existing data based on the 'type' column
        DB::table('contacts')->where('type', 'customer')->update(['is_customer' => true]);
        DB::table('contacts')->where('type', 'supplier')->update(['is_supplier' => true]);
        DB::table('contacts')->where('type', 'partner')->update(['is_related_party' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropForeign(['receivable_account_id']);
            $table->dropForeign(['payable_account_id']);
            $table->dropColumn(['receivable_account_id', 'payable_account_id', 'is_customer', 'is_supplier', 'is_related_party']);
        });
    }
};
