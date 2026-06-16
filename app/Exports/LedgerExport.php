<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LedgerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $lines;
    protected $openingBalance;
    protected $accountName;

    public function __construct($lines, $openingBalance, $accountName)
    {
        $this->lines = $lines;
        $this->openingBalance = $openingBalance;
        $this->accountName = $accountName;
    }

    public function collection()
    {
        return $this->lines;
    }

    public function headings(): array
    {
        return [
            ['كشف حساب: ' . $this->accountName],
            ['الرصيد الافتتاحي: ' . $this->openingBalance],
            [],
            ['التاريخ', 'رقم القيد', 'البيان', 'مدين', 'دائن', 'الرصيد الحسابي']
        ];
    }

    public function map($line): array
    {
        static $runningBalance = null;
        if ($runningBalance === null) {
            $runningBalance = $this->openingBalance;
        }

        $runningBalance += ($line['debit'] - $line['credit']);

        return [
            $line['date'],
            $line['entry_no'],
            $line['description'],
            $line['debit'],
            $line['credit'],
            $runningBalance
        ];
    }
}
