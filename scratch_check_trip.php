<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$loc = App\Models\DriverLocation::latest()->first();
if ($loc) {
    echo "Latest Location: Lat={$loc->latitude}, Lng={$loc->longitude}, Speed={$loc->speed}, Recorded={$loc->recorded_at}\n";
} else {
    echo "No location recorded yet.\n";
}
