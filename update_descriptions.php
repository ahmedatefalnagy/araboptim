<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\JournalEntry;

$entry = JournalEntry::where('entry_no', 'DEP-EXP-2024')->first();
if ($entry) {
    $entry->update(['description' => 'إثبات مصروفات الإهلاك السنوية لعام 2024']);
    foreach ($entry->lines as $line) {
        $line->update(['description' => 'إثبات مصروفات الإهلاك السنوية لعام 2024']);
    }
    echo "Descriptions updated successfully for DEP-EXP-2024.\n";
}

$entry2 = JournalEntry::where('entry_no', 'ASSET-OPEN-2025')->first();
if ($entry2) {
    $entry2->update(['description' => 'إثبات الأرصدة الافتتاحية للأصول الثابتة لعام 2025']);
    foreach ($entry2->lines as $line) {
        if (!$line->description) {
            $line->update(['description' => 'إثبات الأرصدة الافتتاحية للأصول الثابتة لعام 2025']);
        }
    }
    echo "Descriptions updated successfully for ASSET-OPEN-2025.\n";
}
