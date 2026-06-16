<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('advance_settlements', function (Blueprint $table) {
            $table->string('refund_type')->nullable()->after('refund_amount');
            $table->foreignId('rolled_over_to_advance_id')->nullable()->after('refund_type')->constrained('employee_advances')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('advance_settlements', function (Blueprint $table) {
            $table->dropForeign(['rolled_over_to_advance_id']);
            $table->dropColumn(['refund_type', 'rolled_over_to_advance_id']);
        });
    }
};
