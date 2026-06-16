<?php

namespace App\Http\Controllers;

use App\Models\Advance;
use App\Models\Contact;
use App\Models\Setting;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AdvanceController extends Controller
{
    private function getFiscalYearPeriod(): array
    {
        $defaultYearId = Setting::get('default_fiscal_year_id');
        $defaultYear = FiscalYear::find($defaultYearId);
        
        if ($defaultYear) {
            return [
                'start_date' => $defaultYear->start_date->format('Y-m-d'),
                'end_date' => $defaultYear->end_date->format('Y-m-d'),
            ];
        }
        
        return [
            'start_date' => date('Y-01-01'),
            'end_date' => date('Y-12-31'),
        ];
    }

    public function index(): Response
    {
        $period = $this->getFiscalYearPeriod();
        
        $advances = Advance::with(['employee', 'settledBy'])
            ->where('status', '!=', 'closed')
            ->orderByDesc('issue_date')
            ->get()
            ->map(function ($advance) {
                return [
                    'id' => $advance->id,
                    'employee_id' => $advance->employee_id,
                    'employee_name' => $advance->employee?->name,
                    'amount' => $advance->amount,
                    'spent' => $advance->spent,
                    'remaining' => $advance->remaining,
                    'status' => $advance->status,
                    'issue_date' => $advance->issue_date?->format('Y-m-d'),
                    'settlement_date' => $advance->settlement_date?->format('Y-m-d'),
                    'settled_by_name' => $advance->settledBy?->name,
                    'notes' => $advance->notes,
                ];
            });

        $employees = Contact::where('is_active', true)
            ->where('type', 'employee')
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Advances/Index', [
            'advances' => $advances,
            'employees' => $employees,
            'period' => $period,
        ]);
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:contacts,id',
            'amount' => 'required|numeric|min:0.01',
            'issue_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $advance = Advance::create([
            'employee_id' => $validated['employee_id'],
            'amount' => $validated['amount'],
            'spent' => 0,
            'remaining' => $validated['amount'],
            'status' => 'active',
            'issue_date' => $validated['issue_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->back()->with('success', 'تم إنشاء العهدة بنجاح');
    }

    public function addExpense(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'advance_id' => 'required|exists:advances,id',
            'type' => 'required|in:expense,voucher,invoice',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string',
            'is_taxable' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0',
        ]);

        $advance = Advance::findOrFail($validated['advance_id']);
        
        if ($advance->status !== 'active') {
            return redirect()->back()->with('error', 'هذه العهدة غير نشطة');
        }

        $taxAmount = 0;
        if (!empty($validated['is_taxable']) && !empty($validated['tax_rate'])) {
            $taxAmount = $validated['amount'] * ($validated['tax_rate'] / 100);
        }

        $totalAmount = $validated['amount'] + $taxAmount;

        if ($totalAmount > $advance->remaining) {
            return redirect()->back()->with('error', 'المبلغ يتجاوز الرصيد المتبقي للعهدة');
        }

        $advance->transactions()->create([
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'is_taxable' => $validated['is_taxable'] ?? false,
            'tax_rate' => $validated['tax_rate'] ?? 0,
            'tax_amount' => $taxAmount,
        ]);

        $advance->spent += $totalAmount;
        $advance->remaining = $advance->amount - $advance->spent;
        $advance->save();

        return redirect()->back()->with('success', 'تم إضافة المصروف بنجاح');
    }

    public function settle(Request $request, Advance $advance): \Illuminate\Http\RedirectResponse
    {
        if ($advance->status !== 'active') {
            return redirect()->back()->with('error', 'هذه العهدة غير نشطة');
        }

        $validated = $request->validate([
            'action' => 'required|in:settle,close',
            'notes' => 'nullable|string',
        ]);

        if ($validated['action'] === 'settle') {
            $advance->status = 'settled';
            $advance->settlement_date = now();
            $advance->notes = ($advance->notes ? $advance->notes . "\n" : '') . ($validated['notes'] ?? 'تم التسوية');
        } else {
            $advance->status = 'closed';
            $advance->notes = ($advance->notes ? $advance->notes . "\n" : '') . ($validated['notes'] ?? 'تم الإغلاق');
        }
        
        $advance->remaining = 0;
        $advance->save();

        return redirect()->back()->with('success', 'تم تسوية/إغلاق العهدة بنجاح');
    }

    public function recharge(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'advance_id' => 'required|exists:advances,id',
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string',
        ]);

        $advance = Advance::findOrFail($validated['advance_id']);

        $advance->transactions()->create([
            'type' => 'recharge',
            'amount' => $validated['amount'],
            'description' => $validated['notes'] ?? 'إعادة تعبئة عهدة',
        ]);

        $advance->amount += $validated['amount'];
        $advance->remaining = $advance->amount - $advance->spent;
        $advance->status = 'active';
        $advance->save();

        return redirect()->back()->with('success', 'تم إعادة تعبئة العهدة بنجاح');
    }

    public function history(Advance $advance): Response
    {
        $advance->load(['employee', 'settledBy', 'transactions']);

        return Inertia::render('Advances/History', [
            'advance' => [
                'id' => $advance->id,
                'employee_name' => $advance->employee?->name,
                'amount' => $advance->amount,
                'spent' => $advance->spent,
                'remaining' => $advance->remaining,
                'status' => $advance->status,
                'issue_date' => $advance->issue_date?->format('Y-m-d'),
                'settlement_date' => $advance->settlement_date?->format('Y-m-d'),
            ],
            'transactions' => $advance->transactions->map(function ($tx) {
                return [
                    'id' => $tx->id,
                    'type' => $tx->type,
                    'amount' => $tx->amount,
                    'tax_amount' => $tx->tax_amount,
                    'total' => $tx->amount + $tx->tax_amount,
                    'description' => $tx->description,
                    'is_taxable' => $tx->is_taxable,
                    'tax_rate' => $tx->tax_rate,
                    'created_at' => $tx->created_at?->format('Y-m-d H:i'),
                ];
            }),
        ]);
    }
}