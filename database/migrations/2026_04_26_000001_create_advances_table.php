<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('contacts')->onDelete('cascade');
            $table->decimal('amount', 14, 2)->default(0);
            $table->decimal('spent', 14, 2)->default(0);
            $table->decimal('remaining', 14, 2)->default(0);
            $table->enum('status', ['active', 'settled', 'closed'])->default('active');
            $table->date('issue_date');
            $table->date('settlement_date')->nullable();
            $table->foreignId('settled_by')->nullable()->constrained('contacts')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advances');
    }
};