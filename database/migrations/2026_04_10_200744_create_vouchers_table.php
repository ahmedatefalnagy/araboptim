<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_no')->unique();
            $table->enum('type', ['expense', 'advance', 'petty_cash_issue', 'petty_cash_receipt']);
            
            // Employee or Supplier? Or just optional Contact
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            
            $table->date('date');
            
            // Financial Amount
            $table->decimal('amount', 12, 2)->default(0);
            
            // E.g. Expense Account, or Petty Cash Account, or Advances Account
            $table->foreignId('debit_account_id')->constrained('accounts')->restrictOnDelete();
            
            // E.g. Bank or Cash Account
            $table->foreignId('credit_account_id')->constrained('accounts')->restrictOnDelete();
            
            // Link to the generated Journal Entry
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            $table->text('description')->nullable();
            
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
