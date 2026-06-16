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

$net_profit = 0;

foreach ($data_in as $idx => $row) {
    if ($idx == 0) continue; 

    $code = trim($row['H'] ?? '');
    $name = trim($row['G'] ?? '');
    $dr = floatval(str_replace(',', '', $row['B'] ?? '0'));
    $cr = floatval(str_replace(',', '', $row['A'] ?? '0'));

    if (is_numeric($code) && strlen($code) >= 1) {
        // Balance Sheet Accounts (Only 1 and 2 in this system)
        if (str_starts_with($code, '1') || str_starts_with($code, '2')) {
            if (strlen($code) >= 4) { // Only leaf accounts
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
        // Income Statement Accounts (3 and 4)
        else if (str_starts_with($code, '3') || str_starts_with($code, '4')) {
            if (strlen($code) >= 4) {
                // Profit = Revenue(Cr) - Expenses(Dr)
                // In this file, Revenue is code 3, Expenses is code 4
                if (str_starts_with($code, '3')) {
                    $net_profit += ($cr - $dr);
                } else {
                    $net_profit += ($cr - $dr); // Expenses are Dr, so this will be negative
                }
            }
        }
    }
}

// Add Net Profit to balance
if (round($net_profit, 2) != 0) {
    $sheet->setCellValue('A' . $row_num, '2203002');
    $sheet->setCellValue('B' . $row_num, 'صافي أرباح عام 2024 (مرحلة لـ 2025)');
    if ($net_profit > 0) {
        $sheet->setCellValue('D' . $row_num, $net_profit);
        $total_cr += $net_profit;
    } else {
        $sheet->setCellValue('C' . $row_num, abs($net_profit));
        $total_dr += abs($net_profit);
    }
    $row_num++;
}

// Total row
$sheet->setCellValue('B' . $row_num, 'الإجمالي');
$sheet->setCellValue('C' . $row_num, round($total_dr, 2));
$sheet->setCellValue('D' . $row_num, round($total_cr, 2));

$writer = new Xlsx($spreadsheet);
$writer->save('Opening_Balances_2025_No_Revenue.xlsx');
echo "Balanced Excel file created without revenue/expenses.\n";
echo "Total Debit: " . number_format($total_dr, 2) . "\n";
echo "Total Credit: " . number_format($total_cr, 2) . "\n";
?>
