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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->enum('category', ['oil_filter', 'tires', 'mechanical', 'electrical', 'others']);
            $table->text('issue_description');
            $table->enum('status', ['pending', 'approved', 'in_progress', 'completed', 'rejected'])->default('pending');
            $table->enum('stock_status', ['available', 'needed', 'ordered', 'issued'])->default('available');
            $table->decimal('estimated_cost', 15, 2)->default(0);
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('vehicle_tires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('position'); // e.g., Front-Left, Rear-Right-Inner
            $table->enum('unit_type', ['head', 'trailer']); // الرأس أم السطحة
            $table->string('serial_no')->unique();
            $table->string('brand')->nullable();
            $table->date('purchase_date')->nullable();
            $table->integer('warranty_months')->nullable();
            $table->integer('expected_life_km')->nullable();
            $table->integer('installation_km')->nullable();
            $table->enum('status', ['active', 'replaced', 'scrap'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_tires');
        Schema::dropIfExists('maintenance_requests');
    }
};
