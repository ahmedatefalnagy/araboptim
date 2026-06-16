<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->enum('type', ['in', 'out', 'transfer']);
            $table->decimal('quantity', 15, 2);
            $table->string('reference_type')->nullable(); // Model name e.g., Invoice, PurchaseOrder
            $table->unsignedBigInteger('reference_id')->nullable(); // ID of the model
            $table->date('movement_date');
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
