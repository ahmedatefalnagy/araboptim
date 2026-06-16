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
        Schema::table('trips', function (Blueprint $blueprint) {
            $blueprint->string('cargo_type')->nullable()->after('end_customer_name');
            $blueprint->decimal('weight', 10, 2)->nullable()->after('cargo_type');
            $blueprint->string('container_no')->nullable()->after('weight');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trips', function (Blueprint $blueprint) {
            $blueprint->dropColumn(['cargo_type', 'weight', 'container_no']);
        });
    }
};
