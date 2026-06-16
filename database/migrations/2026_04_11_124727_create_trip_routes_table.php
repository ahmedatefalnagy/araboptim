<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_routes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Dammam to Riyadh"
            $table->string('origin');
            $table->string('destination');
            $table->decimal('standard_budget', 12, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('route_id')->nullable()->after('trip_no')->constrained('trip_routes');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['route_id']);
            $table->dropColumn('route_id');
        });
        Schema::dropIfExists('trip_routes');
    }
};
