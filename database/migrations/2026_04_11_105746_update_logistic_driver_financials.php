<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add Driver Commission to Trips
        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('driver_commission', 15, 2)->default(0)->after('broker_price');
        });

        // 2. Add Document Support to Vehicles
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('registration_copy')->nullable(); // PDF/IMG for Istimara
            $table->string('insurance_copy')->nullable(); // PDF/IMG for Insurance
        });

        // 3. Add Document Support to Employees
        Schema::table('employees', function (Blueprint $table) {
            $table->string('license_copy')->nullable(); // PDF/IMG for Driving License
            $table->string('iqama_copy')->nullable(); // PDF/IMG for ID/Iqama
        });

        // 4. Add Trip Allowance/Comm to Payrolls for Payroll calculation
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('trip_allowance', 15, 2)->default(0)->after('other_allowances');
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) { $table->dropColumn('driver_commission'); });
        Schema::table('vehicles', function (Blueprint $table) { $table->dropColumn(['registration_copy', 'insurance_copy']); });
        Schema::table('employees', function (Blueprint $table) { $table->dropColumn(['license_copy', 'iqama_copy']); });
        Schema::table('payrolls', function (Blueprint $table) { $table->dropColumn('trip_allowance'); });
    }
};
