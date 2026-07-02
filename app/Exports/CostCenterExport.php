<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CostCenterExport implements FromCollection, WithHeadings, WithMapping
{
    protected $lines;
    protected $openingBalance;
    protected $costCenterName;

    public function __construct($lines, $openingBalance, $costCenterName)
    {
        $this->lines = $lines;
        $this->openingBalance = $openingBalance;
        $this->costCenterName = $costCenterName;
    }

    public function collection()
    {
        return $this->lines;
    }

    public function headings(): array
    {
        return [
            ['تقرير كشف مركز التكلفة: ' . $this->costCenterName],
            ['الرصيد الافتتاحي: ' . $this->openingBalance],
            [],
            ['التاريخ', 'رقم القيد', 'رمز الحساب', 'اسم الحساب', 'البيان', 'مدين', 'دائن', 'الرصيد الجاري']
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
            $line['account_code'],
            $line['account_name'],
            $line['description'],
            $line['debit'],
            $line['credit'],
            $runningBalance
        ];
    }
}
