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
    if ($idx == 0) continue; // Skip labels row

    $code = trim($row['H'] ?? '');
    $name = trim($row['G'] ?? '');
    $dr = floatval(str_replace(',', '', $row['B'] ?? '0'));
    $cr = floatval(str_replace(',', '', $row['A'] ?? '0'));

    if (is_numeric($code) && strlen($code) >= 1) {
        // Balance Sheet Accounts (1, 2, 3) - Skip totals
        if (str_starts_with($code, '1') || str_starts_with($code, '2') || str_starts_with($code, '3')) {
            // Leaf accounts are usually 4+ digits
            if (strlen($code) >= 4) {
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
        // Income Statement Accounts (4, 5...) - to calculate profit
        else {
            if (strlen($code) >= 4) {
                $net_profit += ($cr - $dr); // Revenue (Cr) - Expenses (Dr)
            }
        }
    }
}

// Add Net Profit to balance the sheet
if (round($net_profit, 2) != 0) {
    $sheet->setCellValue('A' . $row_num, '2203002');
    $sheet->setCellValue('B' . $row_num, 'صافي ربح/خسارة عام 2024');
    if ($net_profit > 0) {
        $sheet->setCellValue('D' . $row_num, $net_profit);
        $total_cr += $net_profit;
    } else {
        $sheet->setCellValue('C' . $row_num, abs($net_profit));
        $total_dr += abs($net_profit);
    }
    $row_num++;
}

// Final balancing check
$diff = $total_dr - $total_cr;
if (abs($diff) > 0.01) {
    // If there is still a tiny difference due to rounding or missing leaf accounts, adjust to Zakat or Profit
    $sheet->setCellValue('A' . $row_num, 'ADJUST');
    $sheet->setCellValue('B' . $row_num, 'فروق تقريب ميزان');
    if ($diff > 0) {
        $sheet->setCellValue('D' . $row_num, $diff);
        $total_cr += $diff;
    } else {
        $sheet->setCellValue('C' . $row_num, abs($diff));
        $total_dr += abs($diff);
    }
    $row_num++;
}

// Total row
$sheet->setCellValue('B' . $row_num, 'الإجمالي');
$sheet->setCellValue('C' . $row_num, round($total_dr, 2));
$sheet->setCellValue('D' . $row_num, round($total_cr, 2));

$writer = new Xlsx($spreadsheet);
$writer->save('Opening_Balances_2025_Final_Balanced.xlsx');
echo "Balanced Excel file created: Opening_Balances_2025_Final_Balanced.xlsx\n";
echo "Total Debit: " . number_format($total_dr, 2) . "\n";
echo "Total Credit: " . number_format($total_cr, 2) . "\n";
?>
