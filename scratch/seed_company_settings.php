<?php
// ضبط بيانات الشركة الموحّدة (كيان واحد) — يُحذف بعد التنفيذ.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Setting;

$values = [
    'company_name' => 'شركة التفاؤل العربية للخدمات اللوجستية',
    'company_name_en' => 'ARAB OPTIMISM for Logistic services Co.',
    'company_vat_no' => '312253166440003',
    'company_commercial_record' => '1009037942',
    'company_email' => 'accounts@araboptim.com',
];

foreach ($values as $key => $value) {
    Setting::set($key, $value);
    echo "set {$key} = {$value}" . PHP_EOL;
}
echo "Done." . PHP_EOL;
