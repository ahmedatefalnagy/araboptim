<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->string('waybill_no')->nullable()->after('trip_no');
            $table->decimal('total_trip_budget', 12, 2)->default(0)->after('driver_commission');
            $table->decimal('initial_diesel_amount', 10, 2)->default(0)->after('total_trip_budget');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['waybill_no', 'total_trip_budget', 'initial_diesel_amount']);
        });
    }
};
