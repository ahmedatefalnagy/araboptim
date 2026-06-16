<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;

$deletedCount = JournalEntry::where('description', 'like', '%تحوبل%')
    ->whereDate('created_at', '2026-05-11')
    ->delete();

echo "Deleted $deletedCount duplicate entries with misspelled 'تحوبل'.\n";

$deletedCount2 = JournalEntry::where('description', 'like', '%مشتريات خاصة بالمدير العام%')
    ->whereDate('created_at', date('Y-m-d'))
    ->delete();
echo "Deleted $deletedCount2 entries with 'مشتريات خاصة بالمدير العام'.\n";

$deletedCount3 = JournalEntry::where('description', 'like', '%سداد فاتورة مشتريات خاصة بالمدير العام%')
    ->whereDate('created_at', date('Y-m-d'))
    ->delete();
echo "Deleted $deletedCount3 entries with 'سداد فاتورة مشتريات خاصة بالمدير العام'.\n";
