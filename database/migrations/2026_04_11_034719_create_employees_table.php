<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_no')->unique();
            $table->string('name');
            $table->string('name_en')->nullable();
            $table->enum('nationality', ['saudi', 'non_saudi'])->default('non_saudi');
            $table->string('iqama_no')->nullable()->comment('رقم الإقامة');
            $table->date('iqama_expiry')->nullable();
            $table->string('national_id')->nullable();
            $table->string('passport_no')->nullable();
            $table->date('passport_expiry')->nullable();
            $table->string('job_title');
            $table->string('department')->nullable();
            $table->date('hire_date');
            $table->date('end_date')->nullable();
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('iban')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'suspended', 'terminated'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
