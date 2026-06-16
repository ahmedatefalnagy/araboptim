<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class JournalEntriesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $entries;

    public function __construct($entries)
    {
        $this->entries = $entries;
    }

    public function collection()
    {
        return collect($this->entries);
    }

    public function headings(): array
    {
        return [
            'رقم القيد',
            'التاريخ',
            'البيان',
            'السنة المالية',
            'الحالة',
            'إجمالي المدين',
            'إجمالي الدائن',
        ];
    }

    public function map($entry): array
    {
        return [
            $entry['entry_no'],
            $entry['entry_date'],
            $entry['description'] ?? '-',
            $entry['fiscal_year'] ?? '-',
            $entry['status'] === 'posted' ? 'معتمد' : 'مسودة',
            number_format((float) ($entry['total_debit'] ?? 0), 2),
            number_format((float) ($entry['total_credit'] ?? 0), 2),
        ];
    }
}