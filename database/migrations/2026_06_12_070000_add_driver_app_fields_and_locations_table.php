<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add fields to trips table
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'loading_invoice_path')) {
                $table->string('loading_invoice_path')->nullable()->after('driver_commission');
            }
            if (!Schema::hasColumn('trips', 'delivery_invoice_path')) {
                $table->string('delivery_invoice_path')->nullable()->after('loading_invoice_path');
            }
            if (!Schema::hasColumn('trips', 'is_commission_paid')) {
                $table->boolean('is_commission_paid')->default(false)->after('delivery_invoice_path');
            }
        });

        // 2. Create driver_locations table
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $table->double('latitude');
            $table->double('longitude');
            $table->double('speed')->default(0); // km/h
            $table->timestamp('recorded_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver_locations');

        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn(['loading_invoice_path', 'delivery_invoice_path', 'is_commission_paid']);
        });
    }
};
