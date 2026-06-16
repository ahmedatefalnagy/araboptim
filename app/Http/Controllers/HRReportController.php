<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Payroll;
use App\Models\Setting;
use App\Helpers\ArabicHelper;
use App\Exports\HRReportExport;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\Pdf;

class HRReportController extends Controller
{
    public function index(Request $request): Response
    {
        $reportData = $this->getReportData($request);
        
        $employees = Employee::orderBy('name')->get(['id', 'name', 'basic_salary', 'hire_date', 'status']);
        
        return Inertia::render('Reports/HRReport', array_merge($reportData, [
            'employees' => $employees,
        ]));
    }

    public function exportExcel(Request $request)
    {
        $reportData = $this->getReportData($request);
        return Excel::download(
            new HRReportExport($reportData['reportData'], $reportData['detailedTransactions']), 
            'hr_report_' . date('Ymd_His') . '.xlsx'
        );
    }

    public function exportPdf(Request $request)
    {
        $reportData = $this->getReportData($request);
        
        // Shape names for PDF
        $summary = $reportData['reportData']->map(function($row) {
            $row['name'] = ArabicHelper::shape($row['name']);
            return $row;
        });

        $details = $reportData['detailedTransactions']->map(function($tx) {
            $tx['employee_name'] = ArabicHelper::shape($tx['employee_name']);
            $tx['purpose'] = ArabicHelper::shape($tx['purpose']);
            $tx['notes'] = ArabicHelper::shape($tx['notes']);
            return $tx;
        });

        $pdf = Pdf::loadView('reports.hr_pdf', [
            'summary' => $summary,
            'details' => $details,
            'filters' => $reportData['filters'],
            'totals' => $reportData['totals'],
            'companyName' => ArabicHelper::shape(Setting::get('company_name', 'نظام المحاسبة'))
        ])->setPaper('a4', 'landscape');

        return $pdf->download('hr_report_' . date('Ymd') . '.pdf');
    }

    private function getReportData(Request $request)
    {
        $employeeId = $request->input('employee_id');
        $startDate = $request->input('start_date', '2025-01-01');
        $endDate = $request->input('end_date', date('Y-m-t'));
        $type = $request->input('type'); 
        $status = $request->input('status');

        // Detailed transactions
        $advancesQuery = EmployeeAdvance::with(['employee', 'settlements'])
            ->whereBetween('date', [$startDate, $endDate]);

        if ($employeeId) $advancesQuery->where('employee_id', $employeeId);
        if ($type) $advancesQuery->where('type', $type);
        if ($status) {
            if ($status === 'settled') $advancesQuery->whereRaw('amount - deducted_amount <= 0');
            else $advancesQuery->whereRaw('amount - deducted_amount > 0');
        }

        $detailedTransactions = $advancesQuery->orderBy('date', 'desc')->get()->map(function($adv) {
            $settlementNotes = $adv->settlements->pluck('notes')->filter()->join(' | ');
            $allNotes = $adv->notes;
            if ($settlementNotes) {
                $allNotes = $allNotes ? $allNotes . " [التسوية: " . $settlementNotes . "]" : "التسوية: " . $settlementNotes;
            }

            return [
                'id' => $adv->id,
                'employee_name' => $adv->employee->name,
                'type' => $adv->type,
                'date' => $adv->date->format('Y-m-d'),
                'amount' => (float)$adv->amount,
                'deducted' => (float)$adv->deducted_amount,
                'remaining' => (float)($adv->amount - $adv->deducted_amount),
                'purpose' => $adv->purpose,
                'notes' => $allNotes,
                'status' => ($adv->amount - $adv->deducted_amount <= 0) ? 'settled' : 'open'
            ];
        });

        // Summary
        $query = Employee::with(['advances', 'payrolls' => function($q) use ($startDate, $endDate) {
            $q->whereBetween('payment_date', [$startDate, $endDate]);
        }]);

        if ($employeeId) $query->where('id', $employeeId);

        $reportData = $query->get()->map(function ($emp) {
            $totalAdvances = $emp->advances->where('type', 'advance')->sum('amount');
            $deductedAdvances = $emp->advances->where('type', 'advance')->sum('deducted_amount');
            $totalCustody = $emp->advances->where('type', 'custody')->sum('amount');
            $deductedCustody = $emp->advances->where('type', 'custody')->sum('deducted_amount');
            $totalBonus = $emp->advances->where('type', 'bonus')->sum('amount');
            $totalPayroll = $emp->payrolls->sum('net_salary');

            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'basic_salary' => (float)$emp->basic_salary,
                'status' => $emp->status,
                'advances' => [
                    'total' => (float)$totalAdvances,
                    'remaining' => (float)($totalAdvances - $deductedAdvances),
                ],
                'custodies' => [
                    'total' => (float)$totalCustody,
                    'remaining' => (float)($totalCustody - $deductedCustody),
                ],
                'bonuses' => ['total' => (float)$totalBonus],
                'total_payroll_period' => (float)$totalPayroll,
            ];
        });

        return [
            'reportData' => $reportData,
            'detailedTransactions' => $detailedTransactions,
            'filters' => [
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'type' => $type,
                'status' => $status,
            ],
            'totals' => [
                'salaries' => $reportData->sum('basic_salary'),
                'advances_remaining' => $reportData->sum(fn($r) => $r['advances']['remaining']),
                'custodies_remaining' => $reportData->sum(fn($r) => $r['custodies']['remaining']),
                'bonuses' => $reportData->sum(fn($r) => $r['bonuses']['total']),
                'payroll_period' => $reportData->sum('total_payroll_period'),
            ]
        ];
    }
}
