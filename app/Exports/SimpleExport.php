<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SimpleExport implements FromCollection, WithHeadings, WithStyles, WithMapping
{
    protected $data;
    protected $headings;
    protected $title;

    public function __construct($data, $headings, $title = 'Report')
    {
        $this->data = collect($data);
        $this->headings = $headings;
        $this->title = $title;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($row): array
    {
        return array_values((array)$row);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'E2E8F0']]],
        ];
    }
}
