<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Account;
use App\Models\Contact;
use App\Models\FiscalYear;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$filePath = 'journal.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray();

$fiscalYear = FiscalYear::where('is_closed', false)->first();
if (!$fiscalYear) {
    die("Error: No active fiscal year found.\n");
}

$previewEntries = [];
// Skip header row
for ($i = 1; $i < count($rows); $i++) {
    $row = $rows[$i];
    if (empty($row[0]) && empty($row[3])) continue; 

    try {
        $rawDate = $row[0];
        if (is_numeric($rawDate)) {
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate)->format('Y-m-d');
        } else {
            $date = Carbon::parse($rawDate)->format('Y-m-d');
        }
    } catch (\Exception $e) {
        echo "Warning: Invalid date on row $i ($rawDate). Skipping line.\n";
        continue;
    }

    $ref = trim($row[1] ?? '');
    $entryDesc = trim($row[2] ?? 'قيد مستورد من اكسيل');
    $accountCode = trim($row[3] ?? '');
    $debit = (float)($row[4] ?? 0);
    $credit = (float)($row[5] ?? 0);
    $lineDesc = trim($row[6] ?? $entryDesc);
    $contactName = trim($row[7] ?? '');

    // Grouping logic: Use Reference if available, otherwise Date + Description
    $groupKey = $ref ? ($date . '_' . $ref) : ($date . '_' . $entryDesc);

    $account = Account::where('code', $accountCode)->first();
    if (!$account) {
        $account = Account::where('name', $accountCode)->first();
    }

    $contact = null;
    if ($contactName) {
        $contact = Contact::where('name', $contactName)->first();
    }

    if (!$account && $contact) {
        $account = $contact->receivableAccount ?? $contact->payableAccount ?? $contact->account;
    }

    if (!$account) {
        echo "Warning: Account not found for row $i ($accountCode). Description: $lineDesc\n";
        continue;
    }

    if (!isset($previewEntries[$groupKey])) {
        $previewEntries[$groupKey] = [
            'entry_date' => $date,
            'description' => $entryDesc,
            'lines' => []
        ];
    }

    $previewEntries[$groupKey]['lines'][] = [
        'account_id' => $account->id,
        'contact_id' => $contact ? $contact->id : null,
        'description' => $lineDesc,
        'debit' => $debit,
        'credit' => $credit,
    ];
}

DB::beginTransaction();
try {
    $count = 0;
    foreach ($previewEntries as $entryData) {
        $totalDebit = array_sum(array_column($entryData['lines'], 'debit'));
        $totalCredit = array_sum(array_column($entryData['lines'], 'credit'));
        
        if (abs($totalDebit - $totalCredit) > 0.5) { 
            echo "Warning: Entry on {$entryData['entry_date']} is unbalanced (D:$totalDebit, C:$totalCredit). Desc: {$entryData['description']}. Skipping.\n";
            continue;
        }

        $entry = JournalEntry::create([
            'entry_no' => getNextEntryNo(),
            'entry_date' => $entryData['entry_date'],
            'description' => $entryData['description'],
            'fiscal_year_id' => $fiscalYear->id,
            'status' => 'draft',
            'created_by' => 1,
        ]);

        foreach ($entryData['lines'] as $line) {
            JournalEntryLine::create([
                'journal_entry_id' => $entry->id,
                'account_id' => $line['account_id'],
                'contact_id' => $line['contact_id'],
                'description' => $line['description'],
                'debit' => $line['debit'],
                'credit' => $line['credit'],
            ]);
        }
        $count++;
    }
    DB::commit();
    echo "\nSuccessfully imported $count journal entries from journal.xlsx\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

function getNextEntryNo() {
    $last = JournalEntry::orderBy('id', 'desc')->first();
    $nextId = $last ? $last->id + 1 : 1;
    return 'JV-' . str_pad($nextId, 6, '0', STR_PAD_LEFT);
}
