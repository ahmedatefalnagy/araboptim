<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('month'); // e.g. "2026-04"
            $table->date('payment_date');
            $table->decimal('basic_salary', 15, 2);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->decimal('overtime_amount', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->comment('الإجمالي قبل الخصومات');
            $table->decimal('gosi_employee', 15, 2)->default(0)->comment('حصة الموظف في التأمينات');
            $table->decimal('gosi_employer', 15, 2)->default(0)->comment('حصة صاحب العمل في التأمينات');
            $table->decimal('advance_deduction', 15, 2)->default(0)->comment('خصم السلفة');
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->comment('صافي الراتب');
            $table->enum('status', ['draft', 'approved', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
