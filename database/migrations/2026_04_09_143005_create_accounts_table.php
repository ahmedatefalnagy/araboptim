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
        Schema::create('accounts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
        $table->string('code')->unique();
        $table->string('name');
        $table->foreignId('account_type_id')->constrained('account_types')->cascadeOnDelete();
        $table->unsignedTinyInteger('level')->default(1);
        $table->boolean('is_postable')->default(true);
        $table->boolean('is_active')->default(true);
        $table->string('report_group')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
