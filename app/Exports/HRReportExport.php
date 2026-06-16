<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class HRReportExport implements WithMultipleSheets
{
    protected $summary;
    protected $details;

    public function __construct($summary, $details)
    {
        $this->summary = $summary;
        $this->details = $details;
    }

    public function sheets(): array
    {
        return [
            new HRSummarySheet($this->summary),
            new HRDetailsSheet($this->details),
        ];
    }
}

class HRSummarySheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $summary;

    public function __construct($summary)
    {
        $this->summary = $summary;
    }

    public function collection()
    {
        return $this->summary;
    }

    public function title(): string
    {
        return 'ملخص الموظفين';
    }

    public function headings(): array
    {
        return [
            'اسم الموظف',
            'الراتب الأساسي',
            'رصيد السلف',
            'رصيد العهد',
            'إجمالي المكافآت',
            'صافي المدفوع في الفترة'
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['basic_salary'],
            $row['advances']['remaining'],
            $row['custodies']['remaining'],
            $row['bonuses']['total'],
            $row['total_payroll_period'],
        ];
    }
}

class HRDetailsSheet implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $details;

    public function __construct($details)
    {
        $this->details = $details;
    }

    public function collection()
    {
        return $this->details;
    }

    public function title(): string
    {
        return 'سجل الحركات التفصيلي';
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'الموظف',
            'النوع',
            'البيان / الغرض',
            'المبلغ',
            'المتبقي',
            'الملاحظات',
            'الحالة'
        ];
    }

    public function map($tx): array
    {
        $types = ['advance' => 'سلفة', 'custody' => 'عهدة', 'bonus' => 'مكافأة'];
        $statuses = ['open' => 'مفتوحة', 'settled' => 'تمت التسوية'];

        return [
            $tx['date'],
            $tx['employee_name'],
            $types[$tx['type']] ?? $tx['type'],
            $tx['purpose'],
            $tx['amount'],
            $tx['remaining'],
            $tx['notes'],
            $statuses[$tx['status']] ?? $tx['status'],
        ];
    }
}
