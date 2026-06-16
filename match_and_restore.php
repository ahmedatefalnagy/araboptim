<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'journal_entries_2026-05-11.xlsx';
$spreadsheet = IOFactory::load($file);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray(null, true, true, true);

// Skip header
array_shift($data);

$mapping = [];
foreach ($data as $row) {
    $amount = (float)str_replace(',', '', $row['F']); // Debit column
    $desc = $row['C'];
    $date = date('Y-m-d', strtotime($row['B']));
    
    // Key by amount and date for matching
    $key = $date . '_' . number_format($amount, 2);
    $mapping[$key][] = $desc;
}

// Now let's try to find our current "Owner Current" lines and match them
require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\JournalEntryLine;
use App\Models\Account;

$accOwner = Account::where('code', '3200')->first();
$lines = JournalEntryLine::where('account_id', $accOwner->id)
    ->whereHas('journalEntry', fn($q) => $q->where('entry_no', 'like', 'MAIN-%')->orWhere('entry_no', 'like', 'PUR-%')->orWhere('entry_no', 'like', 'ADM-%'))
    ->with('journalEntry')
    ->get();

echo "Found " . $lines->count() . " lines in Owner Current to match.\n";

foreach ($lines as $line) {
    $date = $line->journalEntry->entry_date->format('Y-m-d');
    $amount = (float)($line->debit + $line->credit);
    $key = $date . '_' . number_format($amount, 2);
    
    if (isset($mapping[$key])) {
        $origDesc = $mapping[$key][0];
        echo "Match found: Date $date, Amount $amount -> Description: $origDesc\n";
    }
}
