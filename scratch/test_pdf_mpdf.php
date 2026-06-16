<?php
// سكربت اختبار مؤقت لتوليد PDF عبر mPDF — يُحذف بعد التحقق.
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\PdfController;
use App\Models\Invoice;
use App\Models\Voucher;

$controller = app(PdfController::class);
$outDir = __DIR__;

function check($label, $resp, $outDir)
{
    $content = method_exists($resp, 'getContent') ? $resp->getContent() : (string) $resp;
    $isPdf = substr($content, 0, 4) === '%PDF';
    $size = strlen($content);
    $file = $outDir . '/_test_' . $label . '.pdf';
    file_put_contents($file, $content);
    echo sprintf("[%s] %s | %d bytes | %s\n", $isPdf ? 'OK ' : 'BAD', $label, $size, $file);
}

try {
    $voucher = Voucher::first();
    if ($voucher) {
        check('voucher_' . $voucher->type, $controller->voucher($voucher), $outDir);
    } else {
        echo "No voucher found\n";
    }

    $invoice = Invoice::first();
    if ($invoice) {
        check('invoice', $controller->invoice($invoice), $outDir);
        check('delivery_note', $controller->deliveryNote($invoice), $outDir);
        check('grn', $controller->grn($invoice), $outDir);
    } else {
        echo "No invoice found\n";
    }
} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "  at " . $e->getFile() . ':' . $e->getLine() . "\n";
}
