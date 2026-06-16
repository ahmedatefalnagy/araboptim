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
        // Enhance Vehicles with Alert logic
        Schema::table('vehicles', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicles', 'oil_change_interval_km')) {
                $table->integer('oil_change_interval_km')->default(10000)->after('odometer');
            }
            if (!Schema::hasColumn('vehicles', 'tire_change_interval_km')) {
                $table->integer('tire_change_interval_km')->default(50000)->after('oil_change_interval_km');
            }
            if (!Schema::hasColumn('vehicles', 'last_tire_change_km')) {
                $table->integer('last_tire_change_km')->default(0)->after('next_oil_change_km');
            }
        });

        // Enhance Trips with full tracking details
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'actual_loading_start')) {
                $table->dateTime('actual_loading_start')->nullable()->after('actual_arrival');
            }
            if (!Schema::hasColumn('trips', 'actual_loading_end')) {
                $table->dateTime('actual_loading_end')->nullable()->after('actual_loading_start');
            }
            if (!Schema::hasColumn('trips', 'eta_unloading')) {
                $table->dateTime('eta_unloading')->nullable()->after('eta');
            }
            if (!Schema::hasColumn('trips', 'actual_unloading_start')) {
                $table->dateTime('actual_unloading_start')->nullable()->after('actual_loading_end');
            }
            if (!Schema::hasColumn('trips', 'actual_unloading_end')) {
                $table->dateTime('actual_unloading_end')->nullable()->after('actual_unloading_start');
            }
            if (!Schema::hasColumn('trips', 'diesel_liters')) {
                $table->decimal('diesel_liters', 10, 2)->nullable()->after('fuel_amount');
            }
            if (!Schema::hasColumn('trips', 'stop_count')) {
                $table->integer('stop_count')->default(0)->after('notes');
            }
        });

        // Create Trip Stops for detailed monitoring
        if (!Schema::hasTable('trip_stops')) {
            Schema::create('trip_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('trip_id')->constrained()->onDelete('cascade');
                $table->string('location')->nullable();
                $table->string('reason')->comment('rest, saher, breakdown, fuel, other');
                $table->dateTime('start_time');
                $table->dateTime('end_time')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // Create Maintenance Orders (Workshop flow)
        if (!Schema::hasTable('maintenance_orders')) {
            Schema::create('maintenance_orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_no')->unique();
                $table->foreignId('vehicle_id')->constrained();
                $table->foreignId('driver_id')->nullable()->constrained('employees')->onDelete('set null');
                $table->enum('status', ['draft', 'pending_parts', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->enum('type', ['routine', 'emergency', 'preventive'])->default('routine');
                $table->integer('current_odometer');
                $table->text('issue_description')->nullable();
                $table->decimal('total_parts_cost', 12, 2)->default(0);
                $table->decimal('labor_cost', 12, 2)->default(0);
                $table->timestamps();
            });
        }

        // Maintenance Order Items (Linked to Warehouse/Inventory)
        if (!Schema::hasTable('maintenance_order_items')) {
            Schema::create('maintenance_order_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('maintenance_order_id')->constrained()->onDelete('cascade');
                $table->foreignId('item_id')->constrained(); // Links to inventory items
                $table->decimal('quantity', 10, 2);
                $table->decimal('unit_price', 12, 2);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_order_items');
        Schema::dropIfExists('maintenance_orders');
        Schema::dropIfExists('trip_stops');
    }
};
