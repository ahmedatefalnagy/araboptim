<?php
 
 namespace App\Exports;
 
 use Maatwebsite\Excel\Concerns\FromCollection;
 use Maatwebsite\Excel\Concerns\WithHeadings;
 use Maatwebsite\Excel\Concerns\WithStyles;
 use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

 class TrialBalanceExport implements FromCollection, WithHeadings, WithStyles
 {
     protected $data;
 
     public function __construct($data)
     {
         $this->data = $data;
     }
 
     public function collection()
     {
         return $this->data;
     }
 
     public function headings(): array
     {
         return [
             ['ميزان المراجعة'],
             [],
             ['كود الحساب', 'اسم الحساب', 'إجمالي المدين', 'إجمالي الدائن', 'الرصيد النهائي']
         ];
     }

     public function styles(Worksheet $sheet)
     {
         return [
             3 => ['font' => ['bold' => true]],
         ];
     }
 }
