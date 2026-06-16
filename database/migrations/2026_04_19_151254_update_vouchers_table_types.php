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
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('type', ['expense', 'advance', 'petty_cash_issue', 'petty_cash_receipt', 'receipt', 'payment'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('vouchers', function (Blueprint $table) {
            $table->enum('type', ['expense', 'advance', 'petty_cash_issue', 'petty_cash_receipt'])->change();
        });
    }
};
