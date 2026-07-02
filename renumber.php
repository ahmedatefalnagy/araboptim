<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employees = \App\Models\Employee::all();
foreach ($employees as $emp) {
    $emp->update(['employee_no' => 'TEMP-' . $emp->id]);
}

$nonDrivers = \App\Models\Employee::where('is_driver', false)->orderBy('id')->get();
$num = 1;
foreach ($nonDrivers as $emp) {
    $emp->update(['employee_no' => 'EMP-' . str_pad($num++, 5, '0', STR_PAD_LEFT)]);
}

$drivers = \App\Models\Employee::where('is_driver', true)->orderBy('id')->get();
$num = 1001;
foreach ($drivers as $emp) {
    $emp->update(['employee_no' => 'EMP-' . str_pad($num++, 5, '0', STR_PAD_LEFT)]);
}

echo "Renumbering complete.\n";
