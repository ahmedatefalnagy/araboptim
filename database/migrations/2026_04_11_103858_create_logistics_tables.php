<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vehicles (Trucks/Trailers)
        Schema::create('vehicles', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('plate_no')->unique();
            $blueprint->string('model')->nullable(); // e.g., Actros 2024
            $blueprint->string('type')->default('trailer'); // trailer, tanker, etc.
            $blueprint->foreignId('driver_id')->nullable()->constrained('employees');
            $blueprint->string('status')->default('available'); // available, in_trip, maintenance, breakdown
            $blueprint->decimal('odometer', 15, 2)->default(0);
            $blueprint->date('insurance_expiry')->nullable();
            $blueprint->date('registration_expiry')->nullable(); // استمارة
            $blueprint->boolean('is_active')->default(true);
            $blueprint->timestamps();
        });

        // 2. Trips
        Schema::create('trips', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('trip_no')->unique();
            $blueprint->foreignId('vehicle_id')->constrained('vehicles');
            $blueprint->foreignId('driver_id')->constrained('employees');
            $blueprint->foreignId('broker_id')->constrained('contacts'); // The intermediary company (Accounting Client)
            $blueprint->string('end_customer_name')->nullable(); // Client of the broker
            
            $blueprint->string('origin'); // e.g., Dammam
            $blueprint->string('destination'); // e.g., Riyadh
            $blueprint->string('loading_site')->nullable();
            $blueprint->string('discharge_site')->nullable();
            
            $blueprint->string('doc_no')->nullable(); // Waybill / Bayan No
            $blueprint->string('status')->default('planned'); // planned, loading, transit, completed, cancelled
            
            $blueprint->dateTime('etd')->nullable(); // Estimated Departure
            $blueprint->dateTime('eta')->nullable(); // Estimated Arrival
            $blueprint->dateTime('actual_arrival')->nullable();
            
            $blueprint->decimal('start_km', 15, 2)->nullable();
            $blueprint->decimal('end_km', 15, 2)->nullable();
            $blueprint->decimal('fuel_amount', 10, 2)->default(0); // Liters
            $blueprint->decimal('fuel_cost', 15, 2)->default(0);
            
            $blueprint->decimal('broker_price', 15, 2)->default(0); // Price to charge the broker
            $blueprint->foreignId('invoice_id')->nullable()->constrained('invoices'); // Linked after completion
            
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });

        // 3. Trip Events (Stops, Delays)
        Schema::create('trip_events', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('trip_id')->constrained('trips')->onDelete('cascade');
            $blueprint->string('event_type'); // stop, delay, breakdown, route_change
            $blueprint->string('reason')->nullable();
            $blueprint->dateTime('event_time');
            $blueprint->string('location')->nullable();
            $blueprint->text('notes')->nullable();
            $blueprint->timestamps();
        });

        // 4. Maintenance Logs
        Schema::create('vehicle_maintenance_logs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->foreignId('vehicle_id')->constrained('vehicles');
            $blueprint->string('maintenance_type'); // periodic, emergency, tire_change, oil_change
            $blueprint->date('maintenance_date');
            $blueprint->decimal('odometer_reading', 15, 2)->nullable();
            $blueprint->text('description')->nullable();
            $blueprint->decimal('cost', 15, 2)->default(0);
            $blueprint->foreignId('voucher_id')->nullable()->constrained('vouchers'); // Linked to payment voucher
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_logs');
        Schema::dropIfExists('trip_events');
        Schema::dropIfExists('trips');
        Schema::dropIfExists('vehicles');
    }
};
