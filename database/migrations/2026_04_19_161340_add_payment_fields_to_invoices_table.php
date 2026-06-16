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
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('payment_mode')->default('credit')->after('contact_id'); // cash, credit
            $table->unsignedBigInteger('payment_account_id')->nullable()->after('payment_mode'); // for cash sales/purchases
            $table->foreign('payment_account_id')->references('id')->on('accounts');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['payment_account_id']);
            $table->dropColumn(['payment_mode', 'payment_account_id']);
        });
    }
};
