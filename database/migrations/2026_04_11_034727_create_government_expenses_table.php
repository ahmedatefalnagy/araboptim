<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('government_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('type', ['iqama_renewal', 'work_permit', 'insurance', 'exit_reentry', 'other'])
                  ->comment('نوع المصروف: تجديد إقامة، تصريح عمل، تأمين، تأشيرة خروج وعودة، أخرى');
            $table->string('reference_no')->nullable();
            $table->date('expense_date');
            $table->date('expiry_date')->nullable()->comment('تاريخ انتهاء الصلاحية');
            $table->decimal('amount', 15, 2);
            $table->string('provider')->nullable()->comment('الجهة / الحكومة / شركة التأمين');
            $table->enum('status', ['pending', 'paid'])->default('paid');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('government_expenses');
    }
};
