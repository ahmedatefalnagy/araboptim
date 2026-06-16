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
        Schema::table('trip_routes', function (Blueprint $table) {
            $table->integer('distance_km')->nullable()->after('destination');
            $table->decimal('standard_diesel_budget', 12, 2)->nullable()->after('standard_budget');
            $table->decimal('standard_driver_commission', 12, 2)->nullable()->after('standard_diesel_budget');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trip_routes', function (Blueprint $table) {
            $table->dropColumn(['distance_km', 'standard_diesel_budget', 'standard_driver_commission']);
        });
    }
};
