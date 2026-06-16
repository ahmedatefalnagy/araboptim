<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('advance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advance_id')->constrained('advances')->onDelete('cascade');
            $table->enum('type', ['expense', 'voucher', 'invoice', 'recharge', 'adjustment']);
            $table->decimal('amount', 14, 2);
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->boolean('is_taxable')->default(false);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 14, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advance_transactions');
    }
};