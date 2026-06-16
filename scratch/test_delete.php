<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    Illuminate\Http\Request::capture()
);

$employees = Employee::all();

foreach ($employees as $employee) {
    echo "--------------------------------------------------\n";
    echo "Employee ID: {$employee->id}, Name: {$employee->name}\n";
    
    // Check advances
    $advCount = DB::table('employee_advances')->where('employee_id', $employee->id)->count();
    // Check payrolls
    $payCount = DB::table('payrolls')->where('employee_id', $employee->id)->count();
    // Check vehicles (driver_id)
    $vehCount = DB::table('vehicles')->where('driver_id', $employee->id)->count();
    // Check trips (driver_id)
    $tripCount = DB::table('trips')->where('driver_id', $employee->id)->count();
    
    echo "Advances: {$advCount}, Payrolls: {$payCount}, Vehicles: {$vehCount}, Trips: {$tripCount}\n";
    
    try {
        DB::beginTransaction();
        $employee->delete();
        echo "RESULT: Success (deleted inside transaction)\n";
        DB::rollBack();
    } catch (\Exception $e) {
        DB::rollBack();
        echo "RESULT: FAILED to delete.\n";
        echo "  Exception: " . get_class($e) . "\n";
        echo "  Message: " . $e->getMessage() . "\n";
    }
}
