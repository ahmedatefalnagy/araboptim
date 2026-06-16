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
       Schema::create('opening_balances', function (Blueprint $table) {
        $table->id();
        $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
        $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
        $table->decimal('debit', 18, 2)->default(0);
        $table->decimal('credit', 18, 2)->default(0);
        $table->timestamps();

        $table->unique(['fiscal_year_id', 'account_id']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opening_balances');
    }
};
