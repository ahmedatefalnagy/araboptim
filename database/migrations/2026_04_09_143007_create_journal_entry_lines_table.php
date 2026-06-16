<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('journal_entry_lines', function (Blueprint $table) {
        $table->id();
        $table->foreignId('journal_entry_id')->constrained('journal_entries')->cascadeOnDelete();
        $table->foreignId('account_id')->constrained('accounts')->cascadeOnDelete();
        $table->string('description')->nullable();
        $table->decimal('debit', 18, 2)->default(0);
        $table->decimal('credit', 18, 2)->default(0);
        $table->unsignedInteger('line_order')->default(1);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entry_lines');
    }
};
