<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Trip;
use App\Models\DriverLocation;
use App\Models\Payroll;
use App\Models\EmployeeAdvance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DriverApiController extends Controller
{
    /**
     * Driver Login API
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::with('employee')->where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'بيانات الدخول غير صحيحة.'], 401);
        }

        $employee = $user->employee;
        if ($user->role !== 'admin') {
            if (!$employee || !$employee->is_driver) {
                return response()->json(['message' => 'هذا الحساب غير مسجل كسائق في النظام.'], 403);
            }
        }

        $token = $user->createToken('driver-app-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'employee',
            ],
            'driver' => $employee ? [
                'id' => $employee->id,
                'name' => $employee->name,
                'phone' => $employee->phone,
                'iqama_no' => $employee->tax_number ?? $employee->notes,
            ] : null
        ]);
    }

    /**
     * Driver Profile API
     */
    public function profile(Request $request)
    {
        $driver = $request->user()->employee;

        if (!$driver) {
            return response()->json(['message' => 'السائق غير موجود.'], 404);
        }

        $vehicle = \App\Models\Vehicle::where('driver_id', $driver->id)->first();

        return response()->json([
            'id' => $driver->id,
            'name' => $driver->name,
            'phone' => $driver->phone,
            'email' => $driver->email,
            'iqama_no' => $driver->iqama_no,
            'plate_no' => $vehicle ? $vehicle->plate_no : '--',
            'iqama_copy' => $driver->iqama_copy ? asset('storage/' . $driver->iqama_copy) : null,
            'license_copy' => $driver->license_copy ? asset('storage/' . $driver->license_copy) : null,
            'vehicle_license_copy' => $driver->vehicle_license_copy ? asset('storage/' . $driver->vehicle_license_copy) : null,
            'work_card_copy' => $driver->work_card_copy ? asset('storage/' . $driver->work_card_copy) : null,
            'other_document' => $driver->notes,
        ]);
    }

    /**
     * Upload Driver Profile Document
     */
    public function uploadDocument(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'document_type' => 'required|in:iqama_copy,license_copy,vehicle_license_copy,work_card_copy',
            'file' => 'required|file|mimes:pdf,jpg,png,jpeg|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $driver = $request->user()->employee;

        if (!$driver) {
            return response()->json(['message' => 'السائق غير موجود.'], 404);
        }

        if ($request->hasFile('file')) {
            $type = $request->document_type;

            $folders = [
                'iqama_copy' => 'drivers/iqamas',
                'license_copy' => 'drivers/licenses',
                'vehicle_license_copy' => 'drivers/vehicle_licenses',
                'work_card_copy' => 'drivers/work_cards',
            ];

            $folder = $folders[$type] ?? 'drivers/documents';
            $path = $request->file('file')->store($folder, 'public');

            // Delete old file if exists
            if ($driver->$type) {
                Storage::disk('public')->delete($driver->$type);
            }

            // Update employee record
            $driver->$type = $path;
            $driver->save();

            return response()->json([
                'message' => 'تم رفع المستند بنجاح.',
                'url' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['message' => 'الملف مطلوب.'], 400);
    }

    /**
     * Fetch Active Trip
     */
    public function activeTrip(Request $request)
    {
        $driver = $request->user()->employee;

        $trip = Trip::with(['vehicle', 'broker'])
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['planned', 'loading', 'transit'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$trip) {
            return response()->json(['message' => 'لا توجد رحلة نشطة حالياً.'], 404);
        }

        return response()->json([
            'id' => $trip->id,
            'trip_no' => $trip->trip_no,
            'waybill_no' => $trip->waybill_no,
            'origin' => $trip->origin,
            'destination' => $trip->destination,
            'loading_site' => $trip->loading_site,
            'discharge_site' => $trip->discharge_site,
            'status' => $trip->status,
            'vehicle' => [
                'plate_no' => $trip->vehicle->plate_no ?? '--',
                'model' => $trip->vehicle->model ?? '--',
            ],
            'broker_name' => $trip->broker->name ?? '--',
            'driver_commission' => (float)$trip->driver_commission,
            'loading_invoice_path' => $trip->loading_invoice_path ? asset('storage/' . $trip->loading_invoice_path) : null,
            'delivery_invoice_path' => $trip->delivery_invoice_path ? asset('storage/' . $trip->delivery_invoice_path) : null,
        ]);
    }

    /**
     * Upload Loading Invoice (Driver starts loading)
     */
    public function uploadLoadingInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'loading_invoice' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $trip = Trip::find($request->trip_id);
        
        if ($request->hasFile('loading_invoice')) {
            $path = $request->file('loading_invoice')->store('invoices/loading', 'public');
            $trip->loading_invoice_path = $path;
            // Set status to loading (notifying manager to verify and change status to transit)
            $trip->status = 'loading';
            $trip->save();

            return response()->json([
                'message' => 'تم رفع فاتورة التحميل بنجاح، بانتظار بدء الرحلة من قبل المدير.',
                'loading_invoice_path' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['message' => 'ملف الفاتورة مطلوب.'], 400);
    }

    /**
     * Upload Delivery Invoice (Trip transit ended, waiting manager to complete)
     */
    public function uploadDeliveryInvoice(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'delivery_invoice' => 'required|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $trip = Trip::find($request->trip_id);
        
        if ($request->hasFile('delivery_invoice')) {
            $path = $request->file('delivery_invoice')->store('invoices/delivery', 'public');
            $trip->delivery_invoice_path = $path;
            $trip->status = 'transit'; // Driver finished delivery, manager will verify and complete
            $trip->save();

            return response()->json([
                'message' => 'تم رفع فاتورة التسليم بنجاح، بانتظار إنهاء الرحلة من قبل المدير.',
                'delivery_invoice_path' => asset('storage/' . $path),
            ]);
        }

        return response()->json(['message' => 'ملف الفاتورة مطلوب.'], 400);
    }

    /**
     * Store Location telemetry (longitude, latitude, speed)
     */
    public function updateLocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'trip_id' => 'required|exists:trips,id',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'speed' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $location = DriverLocation::create([
            'trip_id' => $request->trip_id,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'speed' => $request->speed,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'message' => 'تم حفظ إحداثيات الموقع بنجاح.',
            'id' => $location->id
        ]);
    }

    /**
     * Driver Trips & Financial History
     */
    public function tripsHistory(Request $request)
    {
        $driver = $request->user()->employee;

        // Fetch trips performed by driver
        $trips = Trip::where('driver_id', $driver->id)
            ->orderBy('date_issue', 'desc')
            ->get();

        // Fetch payroll records
        $payrolls = Payroll::where('employee_id', $driver->id)
            ->orderBy('month', 'desc')
            ->get();

        // Fetch advances
        $advances = EmployeeAdvance::where('employee_id', $driver->id)
            ->orderBy('date', 'desc')
            ->get();

        // Group everything by month
        $history = [];

        // Build base dates from trips, payrolls and advances
        $months = collect()
            ->concat($trips->map(fn($t) => Carbon::parse($t->created_at)->format('Y-m')))
            ->concat($payrolls->map(fn($p) => $p->month))
            ->concat($advances->map(fn($a) => Carbon::parse($a->date)->format('Y-m')))
            ->unique()
            ->sortDesc();

        foreach ($months as $month) {
            $monthTrips = $trips->filter(fn($t) => Carbon::parse($t->created_at)->format('Y-m') === $month);
            $monthPayroll = $payrolls->first(fn($p) => $p->month === $month);
            $monthAdvances = $advances->filter(fn($a) => Carbon::parse($a->date)->format('Y-m') === $month && $a->type === 'advance');
            $monthDeductions = $advances->filter(fn($a) => Carbon::parse($a->date)->format('Y-m') === $month && $a->type === 'custody'); // or deductions

            $history[] = [
                'month' => $month,
                'financials' => [
                    'basic_salary' => $monthPayroll ? (float)$monthPayroll->basic_salary : (float)$driver->basic_salary,
                    'allowances' => $monthPayroll ? (float)($monthPayroll->housing_allowance + $monthPayroll->transport_allowance + $monthPayroll->other_allowances + $monthPayroll->overtime_amount) : 0.0,
                    'deductions' => $monthPayroll ? (float)($monthPayroll->gosi_employee + $monthPayroll->advance_deduction + $monthPayroll->other_deductions) : 0.0,
                    'advances_total' => (float)$monthAdvances->sum('amount'),
                    'net_salary' => $monthPayroll ? (float)$monthPayroll->net_salary : (float)$driver->basic_salary,
                    'payroll_status' => $monthPayroll ? $monthPayroll->status : 'none',
                ],
                'trips' => $monthTrips->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'trip_no' => $t->trip_no,
                        'origin' => $t->origin,
                        'destination' => $t->destination,
                        'driver_commission' => (float)$t->driver_commission,
                        'is_commission_paid' => (bool)$t->is_commission_paid,
                        'status' => $t->status,
                    ];
                })->values()
            ];
        }

        return response()->json($history);
    }

    /**
     * Get all drivers, their assigned vehicles, active trips, locations and docs for Admin
     */
    public function getDriversForAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بالوصول.'], 403);
        }

        $drivers = \App\Models\Employee::where('is_driver', true)->get();

        $data = [];
        foreach ($drivers as $driver) {
            $vehicle = \App\Models\Vehicle::where('driver_id', $driver->id)->first();
            
            $activeTrip = Trip::with(['vehicle', 'broker'])
                ->where('driver_id', $driver->id)
                ->whereIn('status', ['planned', 'loading', 'transit'])
                ->orderBy('id', 'desc')
                ->first();

            $lastLocation = null;
            if ($activeTrip) {
                $lastLocation = DriverLocation::where('trip_id', $activeTrip->id)
                    ->orderBy('id', 'desc')
                    ->first();
            }

            $unpaidCommissions = Trip::where('driver_id', $driver->id)
                ->where('is_commission_paid', false)
                ->sum('driver_commission');

            $data[] = [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'email' => $driver->email,
                'iqama_no' => $driver->iqama_no ?? $driver->tax_number ?? $driver->notes,
                'status' => $driver->status,
                'license_copy' => $driver->license_copy ? asset('storage/' . $driver->license_copy) : null,
                'iqama_copy' => $driver->iqama_copy ? asset('storage/' . $driver->iqama_copy) : null,
                'vehicle_license_copy' => $driver->vehicle_license_copy ? asset('storage/' . $driver->vehicle_license_copy) : null,
                'work_card_copy' => $driver->work_card_copy ? asset('storage/' . $driver->work_card_copy) : null,
                'notes' => $driver->notes,
                'vehicle' => $vehicle ? [
                    'id' => $vehicle->id,
                    'plate_no' => $vehicle->plate_no,
                    'model' => $vehicle->model,
                    'type' => $vehicle->type,
                ] : null,
                'active_trip' => $activeTrip ? [
                    'id' => $activeTrip->id,
                    'trip_no' => $activeTrip->trip_no,
                    'waybill_no' => $activeTrip->waybill_no,
                    'origin' => $activeTrip->origin,
                    'destination' => $activeTrip->destination,
                    'loading_site' => $activeTrip->loading_site,
                    'discharge_site' => $activeTrip->discharge_site,
                    'status' => $activeTrip->status,
                    'driver_commission' => (float)$activeTrip->driver_commission,
                    'loading_invoice_path' => $activeTrip->loading_invoice_path ? asset('storage/' . $activeTrip->loading_invoice_path) : null,
                    'delivery_invoice_path' => $activeTrip->delivery_invoice_path ? asset('storage/' . $activeTrip->delivery_invoice_path) : null,
                    'last_location' => $lastLocation ? [
                        'latitude' => (float)$lastLocation->latitude,
                        'longitude' => (float)$lastLocation->longitude,
                        'speed' => (float)$lastLocation->speed,
                        'recorded_at' => $lastLocation->recorded_at,
                    ] : null,
                ] : null,
                'financials' => [
                    'basic_salary' => (float)$driver->basic_salary,
                    'unpaid_commissions' => (float)$unpaidCommissions,
                ]
            ];
        }

        return response()->json($data);
    }

    /**
     * Get Cash Register (Cash and Bank accounts) with transaction lines and balances for Admin
     */
    public function getCashRegisterForAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بالوصول.'], 403);
        }

        $accountId = $request->input('account_id');
        
        // Fetch Cash and Bank Accounts
        // Restricted to codes starting with 1101 (Cash) and 1102 (Banks)
        $accounts = \App\Models\Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '1101%') // Cash
                  ->orWhere('code', 'like', '1102%'); // Banks
            })->get(['id', 'code', 'name']);

        // Default to the first account (usually Cash/1101) if not selected
        if (!$accountId && $accounts->count() > 0) {
            $accountId = $accounts->first()->id;
        }

        $lines = [];
        $openingBalance = 0;
        $currentBalance = 0;

        if ($accountId) {
            $selectedAccount = \App\Models\Account::with('type')->find($accountId);
            $normalBalance = $selectedAccount->type->normal_balance ?? 'debit';

            // Set default date range to full year or requested dates
            $startDate = $request->input('start_date', date('Y-01-01'));
            $endDate = $request->input('end_date', date('Y-12-31'));

            $openingQuery = \Illuminate\Support\Facades\DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('jel.account_id', $accountId)
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<', $startDate)
                ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
                ->first();

            if ($normalBalance === 'debit') {
                $openingBalance = ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0);
            } else {
                $openingBalance = ($openingQuery->total_credit ?? 0) - ($openingQuery->total_debit ?? 0);
            }

            // Get Transactions
            $lines = \App\Models\JournalEntryLine::with(['journalEntry', 'contact'])
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->where('account_id', $accountId)
                ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
                ->orderBy('journal_entries.entry_date')
                ->orderBy('journal_entries.id')
                ->select('journal_entry_lines.*')
                ->get()
                ->map(function ($line) {
                    return [
                        'id' => $line->id,
                        'date' => $line->journalEntry->entry_date ? $line->journalEntry->entry_date->format('Y-m-d') : null,
                        'entry_no' => $line->journalEntry->entry_no,
                        'description' => $line->description ?: $line->journalEntry->description,
                        'debit' => (float) $line->debit,
                        'credit' => (float) $line->credit,
                        'reference_id' => $line->journalEntry->reference_id,
                        'transaction_type' => $line->journalEntry->transaction_type,
                    ];
                });

            // Calculate current balance
            $currentQuery = \Illuminate\Support\Facades\DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('jel.account_id', $accountId)
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<=', $endDate)
                ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
                ->first();

            if ($normalBalance === 'debit') {
                $currentBalance = ($currentQuery->total_debit ?? 0) - ($currentQuery->total_credit ?? 0);
            } else {
                $currentBalance = ($currentQuery->total_credit ?? 0) - ($currentQuery->total_debit ?? 0);
            }
        }

        return response()->json([
            'accounts' => $accounts,
            'selected_account_id' => (int)$accountId,
            'opening_balance' => (float)$openingBalance,
            'current_balance' => (float)$currentBalance,
            'lines' => $lines,
        ]);
    }

    /**
     * Get Expenses (Vouchers with type='expense') for Admin
     */
    public function getExpensesForAdmin(Request $request)
    {
        if ($request->user()->role !== 'admin') {
            return response()->json(['message' => 'غير مصرح لك بالوصول.'], 403);
        }

        $vouchers = \App\Models\Voucher::with(['contact', 'debitAccount', 'creditAccount'])
            ->where('type', 'expense')
            ->latest('date')
            ->get()
            ->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'voucher_no' => $voucher->voucher_no,
                    'date' => $voucher->date ? $voucher->date->format('Y-m-d') : null,
                    'amount' => (float)$voucher->amount,
                    'description' => $voucher->description,
                    'debit_account' => $voucher->debitAccount ? $voucher->debitAccount->name : '--',
                    'credit_account' => $voucher->creditAccount ? $voucher->creditAccount->name : '--',
                    'contact' => $voucher->contact ? $voucher->contact->name : null,
                    'attachment_url' => $voucher->attachment_path ? asset('storage/' . $voucher->attachment_path) : null,
                ];
            });

        return response()->json($vouchers);
    }
}
