<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\AdvanceExpense;
use App\Models\AdvanceSettlement;
use App\Models\AdvanceSettlementLine;
use App\Models\Payroll;
use App\Models\GovernmentExpense;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\Setting;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class HRController extends Controller
{
    protected $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function advances(Request $request)
    {
        $query = EmployeeAdvance::with('employee')
            ->when($request->status && $request->status !== 'all', function ($q) use ($request) {
                $q->where('status', $request->status);
            })
            ->when($request->type && $request->type !== 'all', function ($q) use ($request) {
                $q->where('type', $request->type);
            })
            ->when($request->employee_id, function ($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            })
            ->when($request->date_from, function ($q) use ($request) {
                $q->whereDate('date', '>=', $request->date_from);
            })
            ->when($request->date_to, function ($q) use ($request) {
                $q->whereDate('date', '<=', $request->date_to);
            });

        $sortOrder = $request->sort_order === 'asc' ? 'asc' : 'desc';
        $advances = $query->orderBy('date', $sortOrder)->get()->map(function($adv) {
            $adv->remaining = $adv->amount - ($adv->deducted_amount ?? 0);
            $adv->computed_status = $adv->status;
            return $adv;
        });

        $employees = Employee::where('status', 'active')->get(['id', 'name', 'employee_no']);
        $paymentAccounts = Account::where('is_postable', true)
            ->whereIn('parent_id', function($query) {
                $query->select('id')->from('accounts')->whereIn('code', ['1110', '1120']);
            })->get(['id', 'name', 'code']);

        return Inertia::render('HR/Advances', [
            'advances'  => $advances,
            'employees' => $employees,
            'paymentAccounts' => $paymentAccounts,
            'filters' => $request->only(['employee_id', 'status', 'type', 'date_from', 'date_to']),
            'fiscal_start_month' => Setting::get('fiscal_start_month', 1),
        ]);
    }

    /**
     * عرض صفحة تصفية العهدة
     */
    public function showSettlement(EmployeeAdvance $advance)
    {
        $advance->load(['employee', 'paymentAccount']);
        $advance->remaining = $advance->amount - ($advance->deducted_amount ?? 0);

        // جلب التصفيات السابقة لهذه العهدة
        $settlements = AdvanceSettlement::with('lines')
            ->where('advance_id', $advance->id)
            ->latest()
            ->get();

        // جلب حسابات المصروفات
        $expenseAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '5%')  // حسابات المصروفات
                  ->orWhere('code', 'like', '6%') // مصروفات عمومية
                  ->orWhere('code', 'like', '211%'); // دائنون وموردون (للفواتير الآجلة)
            })
            ->get(['id', 'code', 'name']);

        // جلب حسابات النقد والبنك
        $cashAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '111%')  // صندوق
                  ->orWhere('code', 'like', '112%') // بنك
                  ->orWhere(function($sub) {
                      $sub->where('code', 'like', '1%')
                          ->where(function($sub2) {
                              $sub2->where('name', 'like', '%صندوق%')
                                   ->orWhere('name', 'like', '%بنك%');
                          });
                  });
            })
            ->get(['id', 'code', 'name']);

        // حساب ضريبة المدخلات
        $inputTaxAccount = Account::where('code', '1170')->first();

        // حساب العهد (عادة تحت الأصول المتداولة)
        $advanceAccount = Account::where('code', 'like', '116%')
            ->orWhere('name', 'like', '%عهد%')
            ->orWhere('name', 'like', '%سلف%')
            ->first();

        // جلب الموردين
        $vendors = Contact::where('type', 'supplier')->get(['id', 'name']);

        // جلب الفواتير الآجلة
        $unpaidInvoices = Invoice::where('type', 'purchase')
            ->where('payment_mode', 'credit')
            ->with('contact:id,name,account_id')
            ->get(['id', 'invoice_no', 'contact_id', 'total_amount']);

        return Inertia::render('HR/SettleAdvance', [
            'advance' => $advance,
            'settlements' => $settlements,
            'expenseAccounts' => $expenseAccounts,
            'cashAccounts' => $cashAccounts,
            'inputTaxAccountId' => $inputTaxAccount?->id,
            'advanceAccountId' => $advanceAccount?->id,
            'vendors' => $vendors,
            'unpaidInvoices' => $unpaidInvoices,
        ]);
    }

    /**
     * تنفيذ تصفية العهدة مع القيد المحاسبي
     */
    public function processSettlement(Request $request, EmployeeAdvance $advance)
    {
        $validated = $request->validate([
            'settlement_date' => 'required|date',
            'notes' => 'nullable|string',
            'refund_account_id' => 'nullable|exists:accounts,id',
            'refund_type' => 'nullable|in:bank_cash,rollover',
            'lines' => 'required|array|min:1',
            'lines.*.type' => 'required|in:expense,purchase',
            'lines.*.invoice_no' => 'nullable|string',
            'lines.*.invoice_date' => 'required|date',
            'lines.*.vendor_name' => 'nullable|string',
            'lines.*.description' => 'required|string',
            'lines.*.amount' => 'required|numeric|min:0.01',
            'lines.*.is_taxable' => 'boolean',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.expense_account_id' => 'required|exists:accounts,id',
            'lines.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $advance->load('employee');
            $advanceRemaining = $advance->amount - ($advance->deducted_amount ?? 0);

            // حساب المجاميع
            $totalExpenses = 0;
            $totalTax = 0;
            $totalAmount = 0;

            $settlementLines = [];
            foreach ($validated['lines'] as $line) {
                $amount = floatval($line['amount']);
                $taxRate = !empty($line['is_taxable']) ? floatval($line['tax_rate'] ?? 15) : 0;
                $taxAmount = $amount * ($taxRate / 100);
                $lineTotal = $amount + $taxAmount;

                $totalExpenses += $amount;
                $totalTax += $taxAmount;
                $totalAmount += $lineTotal;

                $settlementLines[] = array_merge($line, [
                    'tax_rate' => $taxRate,
                    'tax_amount' => round($taxAmount, 2),
                    'total_amount' => round($lineTotal, 2),
                ]);
            }

            // حساب الفرق
            $refundAmount = 0;
            $additionalAmount = 0;

            if ($totalAmount < $advanceRemaining) {
                $refundAmount = $advanceRemaining - $totalAmount; // المبلغ المرتجع
            } elseif ($totalAmount > $advanceRemaining) {
                $additionalAmount = $totalAmount - $advanceRemaining; // المبلغ الإضافي
            }

            // ترحيل المبلغ المتبقي لعهدة جديدة إذا تم اختيار الترحيل
            $rolledOverAdvanceId = null;
            if ($refundAmount > 0 && ($request->input('refund_type') === 'rollover')) {
                $lastAdv = EmployeeAdvance::orderBy('id', 'desc')->first();
                $nextNum = $lastAdv ? ($lastAdv->id + 1) : 1;
                $newRef = 'CSD-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

                $newAdvance = EmployeeAdvance::create([
                    'employee_id' => $advance->employee_id,
                    'type' => 'custody',
                    'reference_no' => $newRef,
                    'date' => $validated['settlement_date'],
                    'amount' => $refundAmount,
                    'payment_account_id' => $advance->payment_account_id,
                    'purpose' => "ترحيل متبقي عهدة رقم {$advance->reference_no}",
                    'notes' => "مرحلة تلقائياً من تصفية عهدة رقم {$advance->reference_no}",
                    'status' => 'open',
                    'deducted_amount' => 0,
                ]);
                $rolledOverAdvanceId = $newAdvance->id;
            }

            // إنشاء رقم التصفية
            $lastSettlement = AdvanceSettlement::latest('id')->first();
            $nextNum = $lastSettlement ? intval(preg_replace('/\D/', '', $lastSettlement->settlement_no)) + 1 : 1;
            $settlementNo = 'SET-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            // إنشاء التصفية
            $settlement = AdvanceSettlement::create([
                'advance_id' => $advance->id,
                'settlement_no' => $settlementNo,
                'settlement_date' => $validated['settlement_date'],
                'status' => 'approved',
                'total_expenses' => round($totalExpenses, 2),
                'total_tax' => round($totalTax, 2),
                'total_amount' => round($totalAmount, 2),
                'refund_amount' => round($refundAmount, 2),
                'refund_type' => $request->input('refund_type'),
                'rolled_over_to_advance_id' => $rolledOverAdvanceId,
                'additional_amount' => round($additionalAmount, 2),
                'notes' => $validated['notes'],
                'created_by' => Auth::id(),
            ]);

            // إنشاء بنود التصفية
            foreach ($settlementLines as $line) {
                $settlement->lines()->create([
                    'type' => $line['type'],
                    'invoice_no' => $line['invoice_no'] ?? null,
                    'invoice_date' => $line['invoice_date'],
                    'vendor_name' => $line['vendor_name'] ?? null,
                    'description' => $line['description'],
                    'amount' => $line['amount'],
                    'is_taxable' => $line['is_taxable'] ?? false,
                    'tax_rate' => $line['tax_rate'],
                    'tax_amount' => $line['tax_amount'],
                    'total_amount' => $line['total_amount'],
                    'expense_account_id' => $line['expense_account_id'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            // إنشاء القيد المحاسبي
            $journalEntry = $this->createSettlementJournalEntry($advance, $settlement, $settlementLines, $validated);

            $settlement->update(['journal_entry_id' => $journalEntry->id]);

            // تحديث حالة العهدة
            $advance->deducted_amount = ($advance->deducted_amount ?? 0) + $totalAmount + ($request->input('refund_type') === 'rollover' ? $refundAmount : ($request->input('refund_type') === 'bank_cash' ? $refundAmount : 0));
            
            if ($advance->deducted_amount >= $advance->amount) {
                $advance->status = 'settled';
            } else {
                $advance->status = 'partially_settled';
            }
            $advance->save();

            DB::commit();

            return redirect()->route('hr.advances')
                ->with('success', "تمت تصفية العهدة بنجاح. رقم التصفية: {$settlementNo}");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors(['message' => 'حدث خطأ أثناء التصفية: ' . $e->getMessage()]);
        }
    }

    /**
     * تحديث تصفية موجودة
     */
    public function updateSettlement(Request $request, AdvanceSettlement $settlement)
    {
        $validated = $request->validate([
            'settlement_date' => 'required|date',
            'notes' => 'nullable|string',
            'refund_account_id' => 'nullable|exists:accounts,id',
            'refund_type' => 'nullable|in:bank_cash,rollover',
            'lines' => 'required|array|min:1',
            'lines.*.type' => 'required|in:expense,purchase',
            'lines.*.invoice_no' => 'nullable|string',
            'lines.*.invoice_date' => 'required|date',
            'lines.*.vendor_name' => 'nullable|string',
            'lines.*.description' => 'required|string',
            'lines.*.amount' => 'required|numeric|min:0.01',
            'lines.*.is_taxable' => 'boolean',
            'lines.*.tax_rate' => 'nullable|numeric|min:0|max:100',
            'lines.*.expense_account_id' => 'required|exists:accounts,id',
            'lines.*.notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $advance = $settlement->advance;
            
            // 1. التحقق من العهدة المرحلة إن وجدت وحذفها
            if ($settlement->rolled_over_to_advance_id) {
                $rolledOver = EmployeeAdvance::find($settlement->rolled_over_to_advance_id);
                if ($rolledOver) {
                    if ($rolledOver->status !== 'open' || $rolledOver->deducted_amount > 0) {
                        return redirect()->back()->withErrors(['message' => 'لا يمكن تعديل التصفية لأن العهدة المرحل إليها تم البدء في تصفيتها أو تسويتها.']);
                    }
                    $rolledOver->delete();
                    $settlement->rolled_over_to_advance_id = null;
                }
            }

            // عكس أثر التصفية القديمة من رصيد العهدة
            $oldTotalAmount = $settlement->total_amount + ($settlement->refund_type === 'rollover' ? $settlement->refund_amount : ($settlement->refund_type === 'bank_cash' ? $settlement->refund_amount : 0));
            $advance->deducted_amount = max(0, ($advance->deducted_amount ?? 0) - $oldTotalAmount);

            // 2. حذف القيد المحاسبي القديم
            if ($settlement->journal_entry_id) {
                DB::table('journal_entries')->where('id', $settlement->journal_entry_id)->delete();
                $settlement->journal_entry_id = null;
            }

            // 3. إعادة حساب المجاميع الجديدة
            $totalExpenses = 0;
            $totalTax = 0;
            $totalAmount = 0;

            $settlementLines = [];
            foreach ($validated['lines'] as $line) {
                $amount = floatval($line['amount']);
                $taxRate = !empty($line['is_taxable']) ? floatval($line['tax_rate'] ?? 15) : 0;
                $taxAmount = $amount * ($taxRate / 100);
                $lineTotal = $amount + $taxAmount;

                $totalExpenses += $amount;
                $totalTax += $taxAmount;
                $totalAmount += $lineTotal;

                $settlementLines[] = array_merge($line, [
                    'tax_rate' => $taxRate,
                    'tax_amount' => round($taxAmount, 2),
                    'total_amount' => round($lineTotal, 2),
                ]);
            }

            $advanceRemaining = $advance->amount - $advance->deducted_amount;
            $refundAmount = $totalAmount < $advanceRemaining ? ($advanceRemaining - $totalAmount) : 0;
            $additionalAmount = $totalAmount > $advanceRemaining ? ($totalAmount - $advanceRemaining) : 0;

            // إنشاء عهدة مرحلة جديدة إذا تم اختيار الترحيل
            $rolledOverAdvanceId = null;
            if ($refundAmount > 0 && ($request->input('refund_type') === 'rollover')) {
                $lastAdv = EmployeeAdvance::orderBy('id', 'desc')->first();
                $nextNum = $lastAdv ? ($lastAdv->id + 1) : 1;
                $newRef = 'CSD-' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

                $newAdvance = EmployeeAdvance::create([
                    'employee_id' => $advance->employee_id,
                    'type' => 'custody',
                    'reference_no' => $newRef,
                    'date' => $validated['settlement_date'],
                    'amount' => $refundAmount,
                    'payment_account_id' => $advance->payment_account_id,
                    'purpose' => "ترحيل متبقي عهدة رقم {$advance->reference_no}",
                    'notes' => "مرحلة تلقائياً من تعديل تصفية عهدة رقم {$advance->reference_no}",
                    'status' => 'open',
                    'deducted_amount' => 0,
                ]);
                $rolledOverAdvanceId = $newAdvance->id;
            }

            // 4. تحديث التصفية والبنود
            $settlement->update([
                'settlement_date' => $validated['settlement_date'],
                'total_expenses' => round($totalExpenses, 2),
                'total_tax' => round($totalTax, 2),
                'total_amount' => round($totalAmount, 2),
                'refund_amount' => round($refundAmount, 2),
                'refund_type' => $request->input('refund_type'),
                'rolled_over_to_advance_id' => $rolledOverAdvanceId,
                'additional_amount' => round($additionalAmount, 2),
                'notes' => $validated['notes'],
            ]);

            $settlement->lines()->delete();
            foreach ($settlementLines as $line) {
                $settlement->lines()->create([
                    'type' => $line['type'],
                    'invoice_no' => $line['invoice_no'] ?? null,
                    'invoice_date' => $line['invoice_date'],
                    'vendor_name' => $line['vendor_name'] ?? null,
                    'description' => $line['description'],
                    'amount' => $line['amount'],
                    'is_taxable' => $line['is_taxable'] ?? false,
                    'tax_rate' => $line['tax_rate'],
                    'tax_amount' => $line['tax_amount'],
                    'total_amount' => $line['total_amount'],
                    'expense_account_id' => $line['expense_account_id'],
                    'notes' => $line['notes'] ?? null,
                ]);
            }

            // 5. إنشاء القيد المحاسبي الجديد
            $journalEntry = $this->createSettlementJournalEntry($advance, $settlement, $settlementLines, $validated);
            $settlement->update(['journal_entry_id' => $journalEntry->id]);

            // 6. تحديث حالة العهدة النهائية
            $advance->deducted_amount += $totalAmount + ($request->input('refund_type') === 'rollover' ? $refundAmount : ($request->input('refund_type') === 'bank_cash' ? $refundAmount : 0));
            if ($advance->deducted_amount >= $advance->amount) {
                $advance->status = 'settled';
            } else {
                $advance->status = 'partially_settled';
            }
            $advance->save();

            DB::commit();
            return redirect()->back()->with('success', "تم تحديث التصفية {$settlement->settlement_no} والقيد المحاسبي بنجاح.");

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'حدث خطأ أثناء التحديث: ' . $e->getMessage()]);
        }
    }

    public function destroySettlement(AdvanceSettlement $settlement)
    {
        DB::beginTransaction();
        try {
            $advance = $settlement->advance;

            // التحقق من وجود عهدة مرحلة وحذفها
            if ($settlement->rolled_over_to_advance_id) {
                $rolledOver = EmployeeAdvance::find($settlement->rolled_over_to_advance_id);
                if ($rolledOver) {
                    if ($rolledOver->status !== 'open' || $rolledOver->deducted_amount > 0) {
                        throw new \Exception('لا يمكن حذف التصفية لأن العهدة المرحل إليها تم البدء في تصفيتها أو تسويتها.');
                    }
                    $rolledOver->delete();
                }
            }
            
            // 1. Subtract the amount from advance
            $oldTotalAmount = $settlement->total_amount + ($settlement->refund_type === 'rollover' ? $settlement->refund_amount : ($settlement->refund_type === 'bank_cash' ? $settlement->refund_amount : 0));
            $advance->deducted_amount = max(0, ($advance->deducted_amount ?? 0) - $oldTotalAmount);
            
            // 2. Update status
            if ($advance->deducted_amount <= 0) {
                $advance->status = 'open';
            } else {
                $advance->status = 'partially_settled';
            }
            $advance->save();

            // 3. Delete journal entry
            if ($settlement->journal_entry_id) {
                DB::table('journal_entries')->where('id', $settlement->journal_entry_id)->delete();
            }

            // 4. Delete settlement
            $settlement->lines()->delete();
            $settlement->delete();

            DB::commit();
            return back()->with('success', 'تم حذف التصفية وإعادة الرصيد للعهدة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء حذف التصفية: ' . $e->getMessage()]);
        }
    }

    /**
     * إنشاء القيد المحاسبي لتصفية العهدة
     * 
     * القيد المحاسبي:
     * ──────────────────────────────────────────────────
     * مدين: حسابات المصروفات (المبالغ قبل الضريبة)
     * مدين: ضريبة القيمة المضافة - مدخلات (إن وجدت)
     * دائن: حساب العهد/السلف (بمبلغ العهدة الأصلي)
     * مدين/دائن: حساب النقدية (الفرق إن وجد)
     * ──────────────────────────────────────────────────
     */
    private function createSettlementJournalEntry(
        EmployeeAdvance $advance,
        AdvanceSettlement $settlement,
        array $lines,
        array $validated
    ) {
        $fiscalYear = FiscalYear::where('is_closed', false)->first();
        if (!$fiscalYear) {
            throw new \Exception('لا توجد سنة مالية مفتوحة.');
        }

        $employeeName = $advance->employee?->name ?? 'موظف';
        $description = "تصفية عهدة رقم {$advance->reference_no} - {$employeeName} - تصفية #{$settlement->settlement_no}";

        // حساب العهد
        $advanceAccount = Account::where('code', '1106')
            ->orWhere('code', 'like', '116%')
            ->orWhere('name', 'like', '%عهد%')
            ->orWhere('name', 'like', '%سلف%')
            ->first();

        if (!$advanceAccount) {
            throw new \Exception('حساب العهد والسلف غير موجود في شجرة الحسابات. يرجى إضافة حساب بكود 1106.');
        }

        // حساب ضريبة المدخلات
        $inputTaxAccount = Account::where('code', '1107')
            ->orWhere('code', '1170')
            ->orWhere('name', 'like', '%مدخلات%')
            ->first();

        $journalLines = [];

        // 1. مدين: حسابات المصروفات (لكل بند مبلغ قبل الضريبة)
        $expensesByAccount = [];
        foreach ($lines as $line) {
            $accountId = $line['expense_account_id'];
            if (!isset($expensesByAccount[$accountId])) {
                $expensesByAccount[$accountId] = 0;
            }
            $expensesByAccount[$accountId] += floatval($line['amount']);
        }

        foreach ($expensesByAccount as $accountId => $amount) {
            $account = Account::find($accountId);
            $accountName = $account ? $account->name : '';
            $journalLines[] = [
                'account_id' => $accountId,
                'debit' => round($amount, 2),
                'credit' => 0,
                'description' => "مصروفات تصفية عهدة - {$accountName}",
            ];
        }

        // 2. مدين: ضريبة المدخلات (إن وجدت)
        if ($settlement->total_tax > 0 && $inputTaxAccount) {
            $journalLines[] = [
                'account_id' => $inputTaxAccount->id,
                'debit' => round($settlement->total_tax, 2),
                'credit' => 0,
                'description' => "ضريبة مدخلات - تصفية عهدة {$advance->reference_no}",
            ];
        }

        // 3. دائن: حساب العهد/السلف
        $advanceRemaining = $advance->amount - ($advance->deducted_amount ?? 0);
        $creditToAdvance = $settlement->total_amount;
        if ($settlement->refund_amount > 0 && ($settlement->refund_type === 'rollover' || $settlement->refund_type === 'bank_cash')) {
            $creditToAdvance += $settlement->refund_amount;
        }
        $creditToAdvance = min($creditToAdvance, $advanceRemaining);

        $journalLines[] = [
            'account_id' => $advanceAccount->id,
            'debit' => 0,
            'credit' => round($creditToAdvance, 2),
            'description' => "تصفية عهدة {$advance->reference_no} - {$employeeName}",
        ];

        // 4. إذا كان هناك فرق (مبلغ إضافي يجب دفعه للموظف)
        if ($settlement->additional_amount > 0 && !empty($validated['refund_account_id'])) {
            $journalLines[] = [
                'account_id' => $validated['refund_account_id'],
                'debit' => 0,
                'credit' => round($settlement->additional_amount, 2),
                'description' => "مبلغ إضافي مدفوع للموظف - تصفية عهدة {$advance->reference_no}",
            ];
        }

        // 5. إذا كان هناك مبلغ مرتجع (الموظف يعيد الفرق)
        if ($settlement->refund_amount > 0) {
            if ($settlement->refund_type === 'rollover' && $settlement->rolled_over_to_advance_id) {
                $newAdvance = EmployeeAdvance::find($settlement->rolled_over_to_advance_id);
                $newRef = $newAdvance ? $newAdvance->reference_no : '';
                $journalLines[] = [
                    'account_id' => $advanceAccount->id,
                    'debit' => round($settlement->refund_amount, 2),
                    'credit' => 0,
                    'description' => "ترحيل متبقي عهدة رقم {$advance->reference_no} إلى عهدة جديدة رقم {$newRef}",
                ];
            } elseif (!empty($validated['refund_account_id'])) {
                $journalLines[] = [
                    'account_id' => $validated['refund_account_id'],
                    'debit' => round($settlement->refund_amount, 2),
                    'credit' => 0,
                    'description' => "مبلغ مرتجع من الموظف - تصفية عهدة {$advance->reference_no}",
                ];
            }
        }

        return $this->journalService->create([
            'entry_date' => $settlement->settlement_date,
            'description' => $description,
            'fiscal_year_id' => $fiscalYear->id,
            'transaction_type' => 'advance_settlement',
            'reference_id' => $settlement->id,
            'status' => 'posted',
            'lines' => $journalLines,
        ]);
    }

    // ==========================================
    // Employees Management
    // ==========================================
    public function employees()
    {
        $employees = Employee::latest()->get();
        return Inertia::render('HR/Employees', [
            'employees' => $employees,
            'flash' => [
                'success' => session('success'),
            ],
        ]);
    }

    public function storeEmployee(Request $request)
    {
        $validated = $request->validate([
            'employee_no' => 'nullable|unique:employees,employee_no',
            'name' => 'required|string',
            'name_en' => 'nullable|string',
            'nationality' => 'required|string',
            'birth_date' => 'nullable|date',
            'iqama_no' => 'nullable|string',
            'operation_card_no' => 'nullable|string',
            'driver_card_no' => 'nullable|string',
            'transport_license_no' => 'nullable|string',
            'iqama_expiry' => 'nullable|date',
            'license_expiry' => 'nullable|date',
            'authorization_expiry' => 'nullable|date',
            'work_card_expiry' => 'nullable|date',
            'driver_card_expiry' => 'nullable|date',
            'transport_license_expiry' => 'nullable|date',
            'national_id' => 'nullable|string',
            'passport_no' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'job_title' => 'required|string',
            'is_driver' => 'boolean',
            'department' => 'nullable|string',
            'hire_date' => 'required|date',
            'basic_salary' => 'nullable|numeric',
            'commission' => 'nullable|numeric',
            'housing_allowance' => 'nullable|numeric',
            'transport_allowance' => 'nullable|numeric',
            'other_allowances' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'account_no' => 'nullable|string',
            'iban' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'status' => 'required|in:active,suspended,terminated',
        ]);

        if (empty($validated['employee_no'])) {
            $lastEmployee = Employee::orderBy('id', 'desc')->first();
            $lastNum = 0;
            if ($lastEmployee && preg_match('/(\d+)/', $lastEmployee->employee_no, $matches)) {
                $lastNum = (int)$matches[1];
            }
            $validated['employee_no'] = 'EMP-' . str_pad($lastNum + 1, 5, '0', STR_PAD_LEFT);
        }

        if (is_null($validated['basic_salary'])) {
            $validated['basic_salary'] = 0;
        }
        if (is_null($validated['commission'])) {
            $validated['commission'] = 0;
        }

        if ($request->hasFile('license_copy')) {
            $validated['license_copy'] = $request->file('license_copy')->store('employees', 'public');
        }
        if ($request->hasFile('iqama_copy')) {
            $validated['iqama_copy'] = $request->file('iqama_copy')->store('employees', 'public');
        }
        if ($request->hasFile('document_file')) {
            $validated['document_file'] = $request->file('document_file')->store('employees', 'public');
        }
        if ($request->hasFile('authorization_copy')) {
            $validated['authorization_copy'] = $request->file('authorization_copy')->store('employees', 'public');
        }
        if ($request->hasFile('operation_card_copy')) {
            $validated['operation_card_copy'] = $request->file('operation_card_copy')->store('employees', 'public');
        }
        if ($request->hasFile('driver_card_copy')) {
            $validated['driver_card_copy'] = $request->file('driver_card_copy')->store('employees', 'public');
        }
        if ($request->hasFile('combined_documents_pdf')) {
            $validated['combined_documents_pdf'] = $request->file('combined_documents_pdf')->store('employees', 'public');
        }

        Employee::create($validated);
        return back()->with('success', 'تم إضافة الموظف بنجاح');
    }

    public function updateEmployee(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'employee_no' => 'nullable|unique:employees,employee_no,' . $employee->id,
            'name' => 'required|string',
            'name_en' => 'nullable|string',
            'nationality' => 'required|string',
            'birth_date' => 'nullable|date',
            'iqama_no' => 'nullable|string',
            'operation_card_no' => 'nullable|string',
            'driver_card_no' => 'nullable|string',
            'transport_license_no' => 'nullable|string',
            'iqama_expiry' => 'nullable|date',
            'license_expiry' => 'nullable|date',
            'authorization_expiry' => 'nullable|date',
            'work_card_expiry' => 'nullable|date',
            'driver_card_expiry' => 'nullable|date',
            'transport_license_expiry' => 'nullable|date',
            'national_id' => 'nullable|string',
            'passport_no' => 'nullable|string',
            'passport_expiry' => 'nullable|date',
            'job_title' => 'required|string',
            'is_driver' => 'boolean',
            'department' => 'nullable|string',
            'hire_date' => 'required|date',
            'basic_salary' => 'nullable|numeric',
            'commission' => 'nullable|numeric',
            'housing_allowance' => 'nullable|numeric',
            'transport_allowance' => 'nullable|numeric',
            'other_allowances' => 'nullable|numeric',
            'bank_name' => 'nullable|string',
            'account_no' => 'nullable|string',
            'iban' => 'nullable|string',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'email' => 'nullable|email',
            'status' => 'required|in:active,suspended,terminated',
        ]);

        if (empty($validated['employee_no'])) {
            $validated['employee_no'] = $employee->employee_no;
        }

        if (is_null($validated['basic_salary'])) {
            $validated['basic_salary'] = 0;
        }
        if (is_null($validated['commission'])) {
            $validated['commission'] = 0;
        }

        if ($request->hasFile('license_copy')) {
            $validated['license_copy'] = $request->file('license_copy')->store('employees', 'public');
        }
        if ($request->hasFile('iqama_copy')) {
            $validated['iqama_copy'] = $request->file('iqama_copy')->store('employees', 'public');
        }
        if ($request->hasFile('document_file')) {
            $validated['document_file'] = $request->file('document_file')->store('employees', 'public');
        }
        if ($request->hasFile('authorization_copy')) {
            $validated['authorization_copy'] = $request->file('authorization_copy')->store('employees', 'public');
        }
        if ($request->hasFile('operation_card_copy')) {
            $validated['operation_card_copy'] = $request->file('operation_card_copy')->store('employees', 'public');
        }
        if ($request->hasFile('driver_card_copy')) {
            $validated['driver_card_copy'] = $request->file('driver_card_copy')->store('employees', 'public');
        }
        if ($request->hasFile('combined_documents_pdf')) {
            $validated['combined_documents_pdf'] = $request->file('combined_documents_pdf')->store('employees', 'public');
        }

        $employee->update($validated);
        return back()->with('success', 'تم تحديث الموظف بنجاح');
    }

    public function destroyEmployee(Employee $employee)
    {
        // فحص استباقي للارتباطات قبل محاولة الحذف، مع تحديد نوع كل ارتباط بدقة.
        // أي سجل مالي أو تشغيلي مرتبط يمنع الحذف للحفاظ على سلامة البيانات.
        $blockers = [];

        if (($count = $employee->payrolls()->count()) > 0) {
            $blockers[] = "سجلات رواتب ({$count})";
        }
        if (($count = $employee->advances()->count()) > 0) {
            $blockers[] = "عُهد/سُلف ({$count})";
        }
        if (($count = DB::table('trips')->where('driver_id', $employee->id)->count()) > 0) {
            $blockers[] = "رحلات ({$count})";
        }
        if (($count = DB::table('vehicles')->where('driver_id', $employee->id)->count()) > 0) {
            $blockers[] = "سيارات مُسندة ({$count})";
        }
        if (($count = DB::table('users')->where('employee_id', $employee->id)->count()) > 0) {
            $blockers[] = "حساب مستخدم مرتبط ({$count})";
        }

        if (! empty($blockers)) {
            $message = 'لا يمكن حذف الموظف لارتباطه بـ: ' . implode('، ', $blockers)
                . '. للحفاظ على سلامة بيانات النظام، يُرجى تغيير حالته إلى "معطل" بدلاً من الحذف.';

            return back()
                ->with('error', $message)
                ->withErrors(['employee' => $message]);
        }

        try {
            DB::transaction(function () use ($employee) {
                $employee->delete();
            });

            return back()->with('success', 'تم حذف الموظف بنجاح');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Failed to delete employee ID {$employee->id}: " . $e->getMessage());

            $message = 'تعذّر حذف الموظف لارتباطه بسجلات أخرى في النظام. يُرجى تغيير حالته إلى "معطل" بدلاً من الحذف.';

            return back()
                ->with('error', $message)
                ->withErrors(['employee' => $message]);
        }
    }

    public function toggleEmployeeStatus(Employee $employee)
    {
        $employee->status = $employee->status === 'active' ? 'suspended' : 'active';
        $employee->save();
        return back()->with('success', 'تم تغيير حالة الموظف بنجاح. الموظف الآن ' . ($employee->status === 'active' ? 'نشط' : 'معطل') . '.');
    }

    // ==========================================
    // Advances Management
    // ==========================================
    public function storeAdvance(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:advance,custody',
            'reference_no' => 'required|string|unique:employee_advances,reference_no',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|exists:accounts,id',
            'purpose' => 'nullable|string',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ]);

        if ($validated['type'] === 'custody') {
            $hasUnsettled = EmployeeAdvance::where('employee_id', $validated['employee_id'])
                ->where('type', 'custody')
                ->where('status', '!=', 'settled')
                ->exists();

            if ($hasUnsettled) {
                if (!$request->input('bypass_restriction') || !Auth::user()->isAdmin()) {
                    return back()->withErrors([
                        'employee_id' => 'الموظف لديه عهدة سابقة غير مسواة. يجب تصفية العهدة السابقة أولاً، أو الحصول على استثناء معتمد من الإدارة.'
                    ]);
                }
            }
        }

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('advances', 'public');
            }

            $advance = EmployeeAdvance::create([
                'employee_id' => $validated['employee_id'],
                'type' => $validated['type'],
                'reference_no' => $validated['reference_no'],
                'date' => $validated['date'],
                'amount' => $validated['amount'],
                'payment_account_id' => $validated['payment_account_id'],
                'purpose' => $validated['purpose'],
                'notes' => $validated['notes'],
                'status' => 'open',
                'deducted_amount' => 0,
                'attachment_path' => $attachmentPath,
            ]);

            // Create Journal Entry
            $fiscalYear = FiscalYear::where('is_closed', false)->first();
            if ($fiscalYear) {
                // Advance Account (1106 / 1160)
                $advanceAccount = Account::where('code', '1106')
                    ->orWhere('code', 'like', '116%')
                    ->orWhere('name', 'like', '%عهد%')
                    ->orWhere('name', 'like', '%سلف%')
                    ->first();
                if ($advanceAccount) {
                    $this->journalService->create([
                        'entry_date' => $advance->date,
                        'description' => "صرف " . ($advance->type === 'custody' ? 'عهدة' : 'سلفة') . " للموظف {$advance->employee->name} رقم {$advance->reference_no}",
                        'fiscal_year_id' => $fiscalYear->id,
                        'transaction_type' => 'advance',
                        'reference_id' => $advance->id,
                        'status' => 'posted',
                        'lines' => [
                            [
                                'account_id' => $advanceAccount->id,
                                'debit' => $advance->amount,
                                'credit' => 0,
                                'description' => $advance->purpose,
                            ],
                            [
                                'account_id' => $validated['payment_account_id'],
                                'debit' => 0,
                                'credit' => $advance->amount,
                                'description' => $advance->purpose,
                            ]
                        ]
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'تم إضافة العهدة/السلفة وتوليد القيد المحاسبي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function destroyAdvance(EmployeeAdvance $advance)
    {
        DB::beginTransaction();
        try {
            // Delete ALL associated journal entries (Issuance, Settlement, Deduction)
            DB::table('journal_entries')
                ->whereIn('transaction_type', ['advance', 'advance_settlement', 'advance_deduction'])
                ->where('reference_id', $advance->id)
                ->delete();

            $advance->delete();
            DB::commit();
            return back()->with('success', 'تم حذف السلفة/العهدة وجميع القيود المرتبطة بها بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ أثناء الحذف: ' . $e->getMessage()]);
        }
    }

    public function convertToCustody(EmployeeAdvance $advance)
    {
        DB::beginTransaction();
        try {
            // If it was settled, we need to undo the deduction journal entry
            if ($advance->status === 'settled') {
                DB::table('journal_entries')
                    ->where('transaction_type', 'advance_deduction')
                    ->where('reference_id', $advance->id)
                    ->delete();
                
                $advance->deducted_amount = 0;
                $advance->status = 'open';
            }

            $advance->type = 'custody';
            $advance->save();

            DB::commit();
            return back()->with('success', 'تم تحويل السلفة إلى عهدة بنجاح (وإلغاء أثر الخصم المحاسبي إن وجد)');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function deductFromSalary(Request $request, EmployeeAdvance $advance)
    {
        DB::beginTransaction();
        try {
            $fiscalYear = FiscalYear::where('is_closed', false)->first();
            if (!$fiscalYear) throw new \Exception('لا توجد سنة مالية مفتوحة');

            $advanceAccount = Account::where('code', 'like', '116%')->orWhere('name', 'like', '%عهدة%')->orWhere('name', 'like', '%سلف%')->first();
            $salaryAccount = Account::where('code', 'like', '5%')->where('name', 'like', '%رواتب%')->first();

            if (!$advanceAccount || !$salaryAccount) {
                throw new \Exception('لم يتم العثور على حساب السلف أو حساب الرواتب في الدليل');
            }

            $remainingAmount = $advance->amount - ($advance->deducted_amount ?? 0);
            if ($remainingAmount <= 0) throw new \Exception('هذه السلفة مسواة بالفعل');

            // Create Journal Entry
            $this->journalService->create([
                'entry_date' => now()->format('Y-m-d'),
                'description' => "تسوية سلفة بالخصم من الراتب - موظف: {$advance->employee->name} - رقم: {$advance->reference_no}",
                'fiscal_year_id' => $fiscalYear->id,
                'transaction_type' => 'advance_deduction',
                'reference_id' => $advance->id,
                'status' => 'posted',
                'lines' => [
                    [
                        'account_id' => $salaryAccount->id,
                        'debit' => $remainingAmount,
                        'credit' => 0,
                        'description' => "خصم سلفة من الراتب المستحق",
                    ],
                    [
                        'account_id' => $advanceAccount->id,
                        'debit' => 0,
                        'credit' => $remainingAmount,
                        'description' => "إقفال رصيد السلفة",
                    ]
                ]
            ]);

            $advance->update([
                'deducted_amount' => $advance->amount,
                'status' => 'settled'
            ]);

            DB::commit();
            return back()->with('success', 'تم تأكيد خصم السلفة من الراتب وتوليد القيد المحاسبي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // Payroll Management
    // ==========================================
    public function payroll()
    {
        $payrolls = Payroll::with('employee')->orderBy('payment_date', 'desc')->get();
        $employees = Employee::where('status', 'active')->get();
        $paymentAccounts = Account::where('is_postable', true)
            ->whereIn('parent_id', function($query) {
                $query->select('id')->from('accounts')->whereIn('code', ['1110', '1120']);
            })->get(['id', 'name', 'code']);

        return Inertia::render('HR/Payroll', [
            'payrolls' => $payrolls,
            'employees' => $employees,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    public function storePayroll(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'month' => 'required|string',
            'payment_date' => 'required|date',
            'basic_salary' => 'required|numeric',
            'housing_allowance' => 'nullable|numeric',
            'transport_allowance' => 'nullable|numeric',
            'other_allowances' => 'nullable|numeric',
            'overtime_amount' => 'nullable|numeric',
            'gosi_employee' => 'nullable|numeric',
            'gosi_employer' => 'nullable|numeric',
            'advance_deduction' => 'nullable|numeric',
            'other_deductions' => 'nullable|numeric',
            'payment_account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $gross = $validated['basic_salary'] + ($validated['housing_allowance'] ?? 0) + ($validated['transport_allowance'] ?? 0) + ($validated['other_allowances'] ?? 0) + ($validated['overtime_amount'] ?? 0);
            $deductions = ($validated['gosi_employee'] ?? 0) + ($validated['advance_deduction'] ?? 0) + ($validated['other_deductions'] ?? 0);
            $net = $gross - $deductions;

            $payroll = Payroll::create(array_merge($validated, [
                'gross_salary' => $gross,
                'net_salary' => $net,
                'status' => 'paid',
            ]));

            // Create Journal Entry
            $fiscalYear = FiscalYear::where('is_closed', false)->first();
            if ($fiscalYear) {
                $salaryAccount = Account::where('code', 'like', '5%')->where('name', 'like', '%رواتب%')->first();
                
                $lines = [];
                // Debit Salary Expense
                if ($salaryAccount) {
                    $lines[] = [
                        'account_id' => $salaryAccount->id,
                        'debit' => $gross,
                        'credit' => 0,
                        'description' => "راتب شهر {$payroll->month} - {$payroll->employee->name}",
                    ];
                }

                // Credit Advance deduction if any
                if ($payroll->advance_deduction > 0) {
                    $advanceAccount = Account::where('code', 'like', '116%')->orWhere('name', 'like', '%عهد%')->first();
                    if ($advanceAccount) {
                        $lines[] = [
                            'account_id' => $advanceAccount->id,
                            'debit' => 0,
                            'credit' => $payroll->advance_deduction,
                            'description' => "خصم سلفة من الراتب",
                        ];
                    }
                }

                // Credit Payment Account
                $lines[] = [
                    'account_id' => $validated['payment_account_id'],
                    'debit' => 0,
                    'credit' => $net, // Or remaining amount after other deductions
                    'description' => "دفع راتب شهر {$payroll->month} - {$payroll->employee->name}",
                ];

                if (count($lines) >= 2) {
                    $this->journalService->create([
                        'entry_date' => $payroll->payment_date,
                        'description' => "مسير راتب شهر {$payroll->month} للموظف {$payroll->employee->name}",
                        'fiscal_year_id' => $fiscalYear->id,
                        'transaction_type' => 'payroll',
                        'reference_id' => $payroll->id,
                        'status' => 'posted',
                        'lines' => $lines
                    ]);
                }
            }

            DB::commit();
            return back()->with('success', 'تم إصدار الراتب واعتماد القيد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    // ==========================================
    // Government Expenses Management
    // ==========================================
    public function governmentExpenses()
    {
        $expenses = GovernmentExpense::with(['employee', 'paymentAccount', 'expenseAccount'])->orderBy('expense_date', 'desc')->get();
        $employees = Employee::where('status', 'active')->get();
        
        $expenseAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '5%')
                  ->orWhere('name', 'like', '%حكوم%')
                  ->orWhere('name', 'like', '%رسوم%');
            })->get(['id', 'code', 'name']);

        $cashAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '111%')  // صندوق
                  ->orWhere('code', 'like', '112%') // بنك
                  ->orWhere('code', 'like', '116%') // عهد
                  ->orWhere(function($sub) {
                      $sub->where('code', 'like', '1%')
                          ->where(function($sub2) {
                              $sub2->where('name', 'like', '%صندوق%')
                                   ->orWhere('name', 'like', '%بنك%')
                                   ->orWhere('name', 'like', '%عهد%');
                          });
                  });
            })->get(['id', 'code', 'name']);

        return Inertia::render('HR/GovernmentExpenses', [
            'expenses' => $expenses,
            'employees' => $employees,
            'expenseAccounts' => $expenseAccounts,
            'cashAccounts' => $cashAccounts,
        ]);
    }

    public function storeGovernmentExpense(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'type' => 'required|string',
            'reference_no' => 'nullable|string',
            'expense_date' => 'required|date',
            'expiry_date' => 'nullable|date',
            'amount' => 'required|numeric|min:0',
            'provider' => 'nullable|string',
            'notes' => 'nullable|string',
            'payment_account_id' => 'required|exists:accounts,id',
        ]);

        // Automatically determine expense account
        // ID 47 is "مصاريف حكومية"
        $expenseAccountId = 47; 
        
        // We can expand this mapping later if needed
        $mapping = [
            'iqama_renewal' => 47,
            'work_permit' => 47,
            'insurance' => 47, 
            'exit_reentry' => 47,
            'other' => 47
        ];

        if (isset($mapping[$validated['type']])) {
            $expenseAccountId = $mapping[$validated['type']];
        }

        DB::beginTransaction();
        try {
            $expense = GovernmentExpense::create(array_merge($validated, [
                'expense_account_id' => $expenseAccountId
            ]));

            // Create Journal Entry
            $fiscalYear = FiscalYear::where('is_closed', false)->first();
            if ($fiscalYear) {
                $empName = $expense->employee ? " للموظف " . $expense->employee->name : "";
                $journalEntry = $this->journalService->create([
                    'entry_date' => $expense->expense_date,
                    'description' => "مصروف حكومي ({$expense->type}){$empName} - رقم {$expense->reference_no}",
                    'fiscal_year_id' => $fiscalYear->id,
                    'transaction_type' => 'government_expense',
                    'reference_id' => $expense->id,
                    'status' => 'posted',
                    'lines' => [
                        [
                            'account_id' => $expenseAccountId,
                            'debit' => $expense->amount,
                            'credit' => 0,
                            'description' => $expense->notes,
                        ],
                        [
                            'account_id' => $validated['payment_account_id'],
                            'debit' => 0,
                            'credit' => $expense->amount,
                            'description' => $expense->notes,
                        ]
                    ]
                ]);
                $expense->update(['journal_entry_id' => $journalEntry->id]);
            }

            DB::commit();
            return back()->with('success', 'تم إضافة المصروف الحكومي وتوليد القيد بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }
}