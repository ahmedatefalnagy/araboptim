<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->enum('type', ['sale', 'sale_return', 'purchase', 'purchase_return']);
            
            // The Contact involved (Customer or Supplier)
            $table->foreignId('contact_id')->constrained('contacts')->onDelete('cascade');
            
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            
            // Financial Amounts
            $table->decimal('total_base', 12, 2)->default(0); // Before Tax
            $table->decimal('total_tax', 12, 2)->default(0);  // Tax Amount
            $table->decimal('total_amount', 12, 2)->default(0); // Net Total
            
            // The Revenue/Expense account that the base amount affects
            $table->foreignId('base_account_id')->constrained('accounts')->restrictOnDelete();
            
            // The VAT account
            $table->foreignId('tax_account_id')->nullable()->constrained('accounts')->restrictOnDelete();
            
            // Link to the generated overarching Journal Entry
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->nullOnDelete();
            
            $table->text('notes')->nullable();
            
            // Created by user
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
