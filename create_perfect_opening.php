<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

$file = 'ميزان مراجعة 1-1-2024 الى 31-12-2024.xls';
$spreadsheet_in = IOFactory::load($file);
$sheet_in = $spreadsheet_in->getActiveSheet();
$data_in = $sheet_in->toArray(null, true, true, true);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Opening Balances 2025 Final');

// Headers
$sheet->setCellValue('A1', 'كود الحساب');
$sheet->setCellValue('B1', 'اسم الحساب');
$sheet->setCellValue('C1', 'مدين (Debit)');
$sheet->setCellValue('D1', 'دائن (Credit)');

$row_num = 2;
$total_dr = 0;
$total_cr = 0;

foreach ($data_in as $row) {
    $code = trim($row['H'] ?? '');
    $name = trim($row['G'] ?? '');
    $dr = floatval(str_replace(',', '', $row['B'] ?? '0'));
    $cr = floatval(str_replace(',', '', $row['A'] ?? '0'));

    // Only process leaf accounts for Balance Sheet (1, 2, 3)
    if (is_numeric($code) && strlen($code) >= 4 && (str_starts_with($code, '1') || str_starts_with($code, '2') || str_starts_with($code, '3'))) {
        if ($dr != 0 || $cr != 0) {
            $sheet->setCellValue('A' . $row_num, $code);
            $sheet->setCellValue('B' . $row_num, $name);
            $sheet->setCellValue('C' . $row_num, $dr);
            $sheet->setCellValue('D' . $row_num, $cr);
            $total_dr += $dr;
            $total_cr += $cr;
            $row_num++;
        }
    }
}

// Add Profit for 2024 to balance it
$profit = $total_dr - $total_cr;
if ($profit != 0) {
    $sheet->setCellValue('A' . $row_num, '2203002');
    $sheet->setCellValue('B' . $row_num, 'صافي ربح عام 2024 (يُقفل في الأرباح المرحلة)');
    if ($profit > 0) {
        $sheet->setCellValue('D' . $row_num, $profit);
        $total_cr += $profit;
    } else {
        $sheet->setCellValue('C' . $row_num, abs($profit));
        $total_dr += abs($profit);
    }
    $row_num++;
}

// Total row
$sheet->setCellValue('B' . $row_num, 'الإجمالي');
$sheet->setCellValue('C' . $row_num, $total_dr);
$sheet->setCellValue('D' . $row_num, $total_cr);

$writer = new Xlsx($spreadsheet);
$writer->save('Opening_Balances_2025_Perfect.xlsx');
echo "Perfectly balanced Excel file Opening_Balances_2025_Perfect.xlsx created.\n";
echo "Final Total: " . number_format($total_dr, 2) . "\n";
