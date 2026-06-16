<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $col) {
            $col->boolean('is_driver')->default(false)->after('job_title');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $col) {
            $col->dropColumn('is_driver');
        });
    }
};
