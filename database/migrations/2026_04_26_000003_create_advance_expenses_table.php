<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('employee_advances')->onDelete('cascade');
            $table->string('invoice_no')->nullable();
            $table->date('expense_date');
            $table->string('description');
            $table->decimal('amount', 14, 2);
            $table->boolean('is_taxable')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->decimal('total_amount', 14, 2);
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->foreignId('tax_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->enum('type', ['purchase', 'expense', 'voucher']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_expenses');
    }
};