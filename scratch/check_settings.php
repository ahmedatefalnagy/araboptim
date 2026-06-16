<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach (['company_name', 'company_name_en', 'company_cr', 'company_vat', 'company_vat_no', 'company_email', 'company_phone', 'company_address'] as $k) {
    $v = \App\Models\Setting::get($k, '<<MISSING>>');
    echo $k . ' = [' . $v . ']' . PHP_EOL;
}
