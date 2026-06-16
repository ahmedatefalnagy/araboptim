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
        Schema::create('journal_entries', function (Blueprint $table) {
        $table->id();
        $table->string('entry_no')->unique();
        $table->date('entry_date');
        $table->text('description')->nullable();
        $table->foreignId('fiscal_year_id')->constrained('fiscal_years')->cascadeOnDelete();
        $table->enum('status', ['draft', 'posted'])->default('draft');
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('posted_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamp('posted_at')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
