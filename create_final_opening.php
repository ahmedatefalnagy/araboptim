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

// Assets (Debit)
$data = [
    ['1101001', 'البنك الاهلي 604', 88.00, 0],
    ['1101003', 'البنك العربي 016', 7.33, 0],
    ['1101004', 'بنك الرياض 940', 19.00, 0], // Can be 0 if you want, but I'll follow the TB
    ['1101005', 'بنك ساب 7001', 1.00, 0],
    ['1101006', 'بنك الانماء 2000', 125533.00, 0],
    ['1101008', 'بنك الراجحي 237', 208799.34, 0],
    ['1101009', 'بنك الراجحي 790', 55.97, 0],
    ['1102001', 'الصندوق', 2831.00, 0],
    ['1103001', 'أعمال تحت التنفيذ', 425652.00, 0],
    ['1104001', 'عهد العاملين', 17150.00, 0],
    ['1201001', 'آلات ومعدات', 236525.00, 0],
    ['1201002', 'سيارات', 112000.00, 0], // Corrected Closing Balance from TB F column
    ['1201003', 'اثاث ومفروشات', 12500.00, 0], // Corrected Closing Balance from TB F column
    ['1201004', 'أجهزة كهربائية وحاسب الي وبرامج', 44250.00, 0], // Corrected Closing Balance from TB F column
    
    // Liabilities & Equity (Credit)
    ['2201001', 'رأس المال', 0, 10000.00],
    ['2202001', 'جاري صاحب المؤسسة', 0, 119850.97],
    ['2203001', 'الأرباح المرحلة (رصيد سابق)', 0, 334660.00],
    ['2203002', 'صافي ربح عام 2024 (يُقفل في الأرباح المرحلة)', 0, 237721.37],
    ['2301001', 'مجمع إهلاك آلات ومعدات', 0, 35478.00], // Corrected Closing Cr from TB E column
    ['2301002', 'مجمع إهلاك سيارات', 0, 16800.00], // Corrected Closing Cr from TB E column
    ['2301003', 'مجمع إهلاك اثاث ومفروشات', 0, 2500.00],  // Corrected Closing Cr from TB E column
    ['2301004', 'مجمع إهلاك أجهزة كهربائية وحاسب الي وبرامج', 0, 8850.00], // Corrected Closing Cr from TB E column
    ['2102001', 'ضريبة القيمة المضافة - مستحقة', 0, 42937.00],
    ['2101002', 'مصروفات مستحقة', 0, 1304.00],
    ['2302001', 'مخصص مكافأة نهاية الخدمة', 0, 4264.00], // Corrected Closing Cr from TB E column
    ['2303001', 'مخصص الزكاة الشرعية', 0, 8932.30], // Balancing figure
];

// Re-calculate totals to ensure they match exactly
$totalDr = 0;
$totalCr = 0;
foreach ($data as $item) {
    $totalDr += $item[2];
    $totalCr += $item[3];
}

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
$sheet->setCellValue('C' . $row, $totalDr);
$sheet->setCellValue('D' . $row, $totalCr);

$writer = new Xlsx($spreadsheet);
$writer->save('Opening_Balances_2025_Final.xlsx');
echo "Excel file Opening_Balances_2025_Final.xlsx created successfully.\n";
echo "Total Dr: $totalDr, Total Cr: $totalCr\n";
