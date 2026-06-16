<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('employee_advances')->onDelete('cascade');
            $table->string('settlement_no')->unique();
            $table->date('settlement_date');
            $table->enum('status', ['draft', 'approved'])->default('draft');
            $table->decimal('total_expenses', 15, 2)->default(0)->comment('إجمالي المصروفات قبل الضريبة');
            $table->decimal('total_tax', 15, 2)->default(0)->comment('إجمالي الضريبة');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('الإجمالي شامل الضريبة');
            $table->decimal('refund_amount', 15, 2)->default(0)->comment('المبلغ المرتجع للشركة');
            $table->decimal('additional_amount', 15, 2)->default(0)->comment('المبلغ المطلوب دفعه إضافياً للموظف');
            $table->foreignId('journal_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('advance_settlement_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('settlement_id')->constrained('advance_settlements')->onDelete('cascade');
            $table->enum('type', ['expense', 'purchase'])->comment('مصروفات أو مشتريات');
            $table->string('invoice_no')->nullable()->comment('رقم الفاتورة');
            $table->date('invoice_date');
            $table->string('vendor_name')->nullable()->comment('اسم المورد');
            $table->string('description');
            $table->decimal('amount', 15, 2)->comment('المبلغ قبل الضريبة');
            $table->boolean('is_taxable')->default(false)->comment('هل خاضعة للضريبة');
            $table->decimal('tax_rate', 5, 2)->default(0)->comment('نسبة الضريبة');
            $table->decimal('tax_amount', 15, 2)->default(0)->comment('مبلغ الضريبة');
            $table->decimal('total_amount', 15, 2)->comment('المبلغ شامل الضريبة');
            $table->foreignId('expense_account_id')->nullable()->constrained('accounts')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_settlement_lines');
        Schema::dropIfExists('advance_settlements');
    }
};
