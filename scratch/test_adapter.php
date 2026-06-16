<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\Pdf;
use App\Helpers\PdfHelper;
use App\Models\Voucher;

try {
    $voucher = Voucher::first();
    $data = PdfHelper::fixArray(['voucher' => $voucher, 'title' => 'سند قبض / Receipt Voucher']);
    // اختبار عبر الـ adapter بنفس أسلوب DomPDF القديم
    $resp = Pdf::loadView('pdfs.voucher', $data)->setPaper('a4', 'portrait')->download('test.pdf');
    $content = $resp->getContent();
    $ok = substr($content, 0, 4) === '%PDF';
    echo ($ok ? 'OK ' : 'BAD') . ' adapter chain | ' . strlen($content) . " bytes\n";
} catch (\Throwable $e) {
    echo 'ERROR: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine() . "\n";
}
