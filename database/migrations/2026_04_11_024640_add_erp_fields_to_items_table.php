<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('barcode')->nullable()->unique()->after('sku');
            $table->decimal('cost_price', 15, 2)->default(0)->after('price');
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete()->after('id');
            $table->foreignId('category_id')->nullable()->constrained('item_categories')->nullOnDelete()->after('unit_id');
            $table->boolean('track_inventory')->default(true)->after('is_active');
            $table->decimal('alert_quantity', 15, 2)->default(0)->after('track_inventory');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropForeign(['unit_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'barcode',
                'cost_price',
                'unit_id',
                'category_id',
                'track_inventory',
                'alert_quantity'
            ]);
        });
    }
};
