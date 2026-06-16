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
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->string('debit_description')->after('purpose')->nullable();
            $table->string('credit_description')->after('debit_description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_advances', function (Blueprint $table) {
            $table->dropColumn(['debit_description', 'credit_description']);
        });
    }
};
