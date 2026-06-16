<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Trip;
use App\Models\EmployeeAdvance;
use App\Models\Payroll;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DriverReportController extends Controller
{
    public function statement(Request $request)
    {
        $employeeId = $request->employee_id;
        $month = $request->month ?: Carbon::now()->format('Y-m');
        
        $drivers = Employee::where('is_driver', true)
                           ->get(['id', 'name', 'employee_no', 'basic_salary']);

        $data = null;
        if ($employeeId) {
            $employee = Employee::find($employeeId);
            
            // Completed Trips Commissions
            $trips = Trip::with('vehicle')
                ->where('driver_id', $employeeId)
                ->where('status', 'completed')
                ->whereMonth('actual_arrival', Carbon::parse($month)->month)
                ->whereYear('actual_arrival', Carbon::parse($month)->year)
                ->get();
            
            $totalCommission = $trips->sum('driver_commission');
            
            // Advances this month
            $advances = EmployeeAdvance::where('employee_id', $employeeId)
                ->whereMonth('date', Carbon::parse($month)->month)
                ->whereYear('date', Carbon::parse($month)->year)
                ->get();
            
            $totalAdvances = $advances->sum('amount');
            
            // Payroll Deductions
            $payroll = Payroll::where('employee_id', $employeeId)
                ->where('month', $month)
                ->first();
            
            $deductions = $payroll ? ($payroll->advance_deduction + $payroll->other_deductions + $payroll->gosi_employee) : 0;
            
            $data = [
                'employee' => $employee,
                'month' => $month,
                'basic_salary' => $employee->basic_salary,
                'trips' => $trips,
                'total_commission' => $totalCommission,
                'total_advances' => $totalAdvances,
                'deductions' => $deductions,
                'gross_total' => $employee->basic_salary + $totalCommission,
                'net_total' => ($employee->basic_salary + $totalCommission) - ($totalAdvances + $deductions)
            ];
        }

        return Inertia::render('Logistics/Reports/DriverStatement', [
            'drivers' => $drivers,
            'reportData' => $data,
            'filters' => [
                'employee_id' => (int) $employeeId,
                'month' => $month
            ]
        ]);
    }
}
