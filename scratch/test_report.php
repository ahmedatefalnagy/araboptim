<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Http\Controllers\ReportController;
use Illuminate\Http\Request;

try {
    $controller = app(ReportController::class);
    $resp = $controller->exportTrialBalancePdf(new Request());
    $content = method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp;
    $ok = substr($content, 0, 4) === '%PDF';
    file_put_contents(__DIR__ . '/_test_trial_balance.pdf', $content);
    echo ($ok ? 'OK ' : 'BAD') . " trial_balance | " . strlen($content) . " bytes\n";
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n";
}
