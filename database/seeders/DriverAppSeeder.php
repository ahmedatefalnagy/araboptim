<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\User;
use App\Models\Contact;
use App\Models\Trip;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DriverAppSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create a test driver employee
        $driver = Employee::updateOrCreate(
            ['phone' => '0555555555'],
            [
                'name' => 'أحمد السائق',
                'employee_no' => 'EMP-1002',
                'email' => 'driver@driver.com',
                'is_driver' => true,
                'basic_salary' => 4500.00,
                'hire_date' => now()->subYear(),
                'status' => 'active',
                'license_copy' => 'drivers/licenses/test_license.png',
                'iqama_copy' => 'drivers/iqamas/test_iqama.png',
                'vehicle_license_copy' => 'drivers/vehicle_licenses/test_vehicle_license.png',
            ]
        );

        // 2. Create User account for the driver
        User::updateOrCreate(
            ['email' => 'driver@driver.com'],
            [
                'name' => 'أحمد السائق',
                'password' => Hash::make('password'),
                'employee_id' => $driver->id,
                'role' => 'employee',
            ]
        );

        // 3. Create a Broker (Contact)
        $broker = Contact::updateOrCreate(
            ['name' => 'شركة التفاؤل للمقاولات'],
            [
                'type' => 'supplier', // or customer
                'is_customer' => true,
                'is_active' => true,
                'phone' => '0512345678',
            ]
        );

        // 4. Create a Vehicle (Truck)
        // Check if vehicles table exists and seed
        if (\Schema::hasTable('vehicles')) {
            $vehicleId = DB::table('vehicles')->insertGetId([
                'plate_no' => 'أ ب ج 1234',
                'model' => 'Actros 2024',
                'type' => 'رأس تريلا',
                'driver_id' => $driver->id,
                'status' => 'available',
                'odometer' => 125000,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $vehicleId = 1;
        }

        // 5. Create a Planned Trip for the driver
        Trip::updateOrCreate(
            ['trip_no' => 'TRIP-999'],
            [
                'waybill_no' => 'WB-888',
                'vehicle_id' => $vehicleId,
                'driver_id' => $driver->id,
                'broker_id' => $broker->id,
                'end_customer_name' => 'مصنع الخليج للخرسانة',
                'origin' => 'الدمام - ميناء الملك عبد العزيز',
                'destination' => 'الرياض - الملز',
                'loading_site' => 'رصيف رقم 5',
                'discharge_site' => 'موقع الإنشاء الرئيسي',
                'status' => 'planned',
                'driver_commission' => 250.00,
                'total_trip_budget' => 1500.00,
                'notes' => 'يرجى تحميل شحنة الحديد وتأكيد التحميل برفع الفاتورة.',
            ]
        );
    }
}
