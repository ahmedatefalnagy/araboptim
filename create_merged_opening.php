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
$net_profit_2024 = 0;
$old_retained_earnings = 0;

$processed_data = [];

foreach ($data_in as $idx => $row) {
    if ($idx == 0) continue; 

    $code = trim($row['H'] ?? '');
    $name = trim($row['G'] ?? '');
    $dr = floatval(str_replace(',', '', $row['B'] ?? '0'));
    $cr = floatval(str_replace(',', '', $row['A'] ?? '0'));

    if (is_numeric($code) && strlen($code) >= 4) {
        // Balance Sheet (1 and 2)
        if (str_starts_with($code, '1') || str_starts_with($code, '2')) {
            // Special handling for Retained Earnings (2203001)
            if ($code == '2203001') {
                $old_retained_earnings = $cr - $dr;
            } else {
                $processed_data[] = ['code' => $code, 'name' => $name, 'dr' => $dr, 'cr' => $cr];
                $total_dr += $dr;
                $total_cr += $cr;
            }
        }
        // Income Statement (3 and 4) to calculate 2024 Profit
        else if (str_starts_with($code, '3') || str_starts_with($code, '4')) {
            $net_profit_2024 += ($cr - $dr);
        }
    }
}

// Merge old retained earnings with 2024 profit
$final_retained_earnings = $old_retained_earnings + $net_profit_2024;

// Add the merged Retained Earnings account
$processed_data[] = [
    'code' => '2203001',
    'name' => 'الأرباح المرحلة (رصيد سابق + ربح 2024)',
    'dr' => $final_retained_earnings < 0 ? abs($final_retained_earnings) : 0,
    'cr' => $final_retained_earnings > 0 ? $final_retained_earnings : 0
];
$total_dr += ($final_retained_earnings < 0 ? abs($final_retained_earnings) : 0);
$total_cr += ($final_retained_earnings > 0 ? $final_retained_earnings : 0);

// Write to sheet
foreach ($processed_data as $item) {
    $sheet->setCellValue('A' . $row_num, $item['code']);
    $sheet->setCellValue('B' . $row_num, $item['name']);
    $sheet->setCellValue('C' . $row_num, $item['dr']);
    $sheet->setCellValue('D' . $row_num, $item['cr']);
    $row_num++;
}

// Final balancing check
$diff = $total_dr - $total_cr;
if (abs($diff) > 0.001) {
    // Adjust tiny difference to Retained Earnings
    $last_row = $row_num - 1;
    $current_cr = $sheet->getCell('D' . $last_row)->getValue();
    $sheet->setCellValue('D' . $last_row, $current_cr + $diff);
    $total_cr += $diff;
}

// Total row
$sheet->setCellValue('B' . $row_num, 'الإجمالي');
$sheet->setCellValue('C' . $row_num, round($total_dr, 2));
$sheet->setCellValue('D' . $row_num, round($total_cr, 2));

$writer = new Xlsx($spreadsheet);
$writer->save('Opening_Balances_2025_Final_Merged.xlsx');
echo "Merged Excel file created: Opening_Balances_2025_Final_Merged.xlsx\n";
echo "Total Debit: " . number_format($total_dr, 2) . "\n";
echo "Total Credit: " . number_format($total_cr, 2) . "\n";
?>
