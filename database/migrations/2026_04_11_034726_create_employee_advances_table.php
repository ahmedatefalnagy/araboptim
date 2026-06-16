<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['advance', 'custody'])->default('advance')->comment('سلفة أو عهدة');
            $table->string('reference_no');
            $table->date('date');
            $table->decimal('amount', 15, 2);
            $table->decimal('deducted_amount', 15, 2)->default(0)->comment('المبلغ المسترد / المخصوم');
            $table->decimal('remaining_amount', 15, 2)->storedAs('amount - deducted_amount');
            $table->enum('status', ['open', 'partially_settled', 'settled'])->default('open');
            $table->string('purpose')->nullable()->comment('غرض السلفة أو العهدة');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_advances');
    }
};
