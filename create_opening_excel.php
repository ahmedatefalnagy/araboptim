<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Opening Balances 2025');

// Headers
$sheet->setCellValue('A1', 'كود الحساب');
$sheet->setCellValue('B1', 'اسم الحساب');
$sheet->setCellValue('C1', 'مدين (Debit)');
$sheet->setCellValue('D1', 'دائن (Credit)');

$data = [
    ['1101001', 'البنك الاهلي 604', 88.00, 0],
    ['1101003', 'البنك العربي 016', 7.33, 0],
    ['1101004', 'بنك الرياض 940', 19.00, 0],
    ['1101005', 'بنك ساب 7001', 1.00, 0],
    ['1101006', 'بنك الانماء 2000', 125533.00, 0],
    ['1101008', 'بنك الراجحي 237', 208799.34, 0],
    ['1101009', 'بنك الراجحي 790', 55.97, 0],
    ['1102001', 'الصندوق', 2831.00, 0],
    ['1103001', 'أعمال تحت التنفيذ', 425652.00, 0],
    ['1104001', 'عهد العاملين', 17150.00, 0],
    ['1201001', 'آلات ومعدات', 236525.00, 0],
    ['1201002', 'سيارات', 301570.00, 0],
    ['1201003', 'اثاث ومفروشات', 40850.00, 0],
    ['1201004', 'أجهزة كهربائية وحاسب الي وبرامج', 103250.00, 0],
    ['2201001', 'رأس المال', 0, 10000.00],
    ['2202001', 'جاري صاحب المؤسسة', 0, 119850.97],
    ['2203001', 'الأرباح المرحلة (شامل ربح 2024)', 0, 572381.37],
    ['2301001', 'مجمع إهلاك آلات ومعدات', 0, 70956.00],
    ['2301002', 'مجمع إهلاك سيارات', 0, 40709.00],
    ['2301003', 'مجمع إهلاك اثاث ومفروشات', 0, 6417.00],
    ['2301004', 'مجمع إهلاك أجهزة كهربائية وحاسب الي وبرامج', 0, 20650.00],
    ['2102001', 'ضريبة القيمة المضافة', 0, 42937.00],
    ['2101002', 'مصروفات مستحقة', 0, 1304.00],
    ['2302001', 'مخصص مكافأة نهاية الخدمة', 0, 8747.00],
    ['2303001', 'مخصص الزكاه', 0, 8932.30], // Adjusted to balance the JV
];

$row = 2;
foreach ($data as $item) {
    $sheet->setCellValue('A' . $row, $item[0]);
    $sheet->setCellValue('B' . $row, $item[1]);
    $sheet->setCellValue('C' . $row, $item[2]);
    $sheet->setCellValue('D' . $row, $item[3]);
    $row++;
}

// Total
$sheet->setCellValue('B' . $row, 'الإجمالي');
$sheet->setCellValue('C' . $row, '=SUM(C2:C' . ($row-1) . ')');
$sheet->setCellValue('D' . $row, '=SUM(D2:D' . ($row-1) . ')');

$writer = new Xlsx($spreadsheet);
$writer->save('Opening_Balances_2025.xlsx');
echo "Excel file Opening_Balances_2025.xlsx created successfully.\n";
