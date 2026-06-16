<?php

use App\Models\Voucher;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use App\Services\JournalEntryService;

$journalService = app(JournalEntryService::class);
$fiscalYear = FiscalYear::where('is_closed', false)->first();

if (!$fiscalYear) {
    die("Error: No open fiscal year found.\n");
}

echo "Fixing orphan Vouchers (missing Journal Entries)...\n\n";

$vouchers = Voucher::all();
foreach ($vouchers as $voucher) {
    // Check if JV exists with reference
    $exists = JournalEntry::where('transaction_type', 'voucher')
        ->where('reference_id', $voucher->id)
        ->exists();

    if (!$exists) {
        echo "Generating JV for Voucher ID: {$voucher->id} (No: {$voucher->voucher_no})...\n";
        
        $typesLabel = [
            'expense' => 'سند صرف مصروف',
            'advance' => 'سند سلفة موظف',
            'petty_cash_issue' => 'سند صرف عهدة نقدية',
            'petty_cash_receipt' => 'سند تسوية عهدة',
            'receipt' => 'سند قبض',
            'payment' => 'سند صرف عام'
        ];

        $description = ($typesLabel[$voucher->type] ?? 'سند') . ' رقم ' . $voucher->voucher_no . ($voucher->description ? ' - ' . $voucher->description : '');

        try {
            $journalService->create([
                'entry_date' => $voucher->date,
                'description' => $description,
                'fiscal_year_id' => $fiscalYear->id,
                'transaction_type' => 'voucher',
                'reference_id' => $voucher->id,
                'lines' => [
                    [
                        'account_id' => $voucher->debit_account_id,
                        'debit' => $voucher->amount,
                        'credit' => 0,
                        'description' => $description
                    ],
                    [
                        'account_id' => $voucher->credit_account_id,
                        'debit' => 0,
                        'credit' => $voucher->amount,
                        'description' => $description
                    ]
                ]
            ]);
            echo "DONE.\n";
        } catch (\Exception $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
        }
    }
}

echo "\nVoucher fix complete.\n";
