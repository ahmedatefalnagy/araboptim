<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Contact;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\FiscalYear;
use App\Models\JournalEntryLine;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class VoucherController extends Controller
{
    protected $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $type = $request->query('type', 'expense');
        
        $vouchers = Voucher::with(['contact', 'debitAccount', 'creditAccount', 'journalEntry'])
            ->where('type', $type)
            ->latest('date')
            ->get();
            
        return Inertia::render('Vouchers/Index', [
            'type' => $type,
            'vouchers' => $vouchers
        ]);
    }

    private function syncEmployeesToContacts()
    {
        try {
            $employees = \App\Models\Employee::where('status', 'active')->get();
            foreach ($employees as $emp) {
                $contact = Contact::where('type', 'employee')
                    ->where('name', $emp->name)
                    ->first();
                if (!$contact) {
                    Contact::create([
                        'type' => 'employee',
                        'name' => $emp->name,
                        'email' => $emp->email,
                        'phone' => $emp->phone,
                        'is_active' => true,
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silence exceptions to prevent breaking if tables are not fully migrated
        }
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'expense');
        
        $this->syncEmployeesToContacts();
        
        $employees = Contact::where('type', 'employee')->get(['id', 'name']);
        $contacts = Contact::whereIn('type', ['customer', 'supplier'])->get(['id', 'name']);
        
        // Fetch explicit Payment Methods (Cash & Banks)
        // Restricted to codes starting with 1101 (Cash) and 1102 (Banks)
        $paymentMethods = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '1101%')
                  ->orWhere('code', 'like', '1102%');
            })->get(['id', 'code', 'name']);
            
        // Fetch Expense Accounts
        $expenseAccounts = Account::where('is_postable', true)
            ->whereHas('type', function($q) {
                $q->where('code', 'expense');
            })->get(['id', 'code', 'name']);

        // Fetch All Accounts for general purpose
        $allAccounts = Account::where('is_postable', true)->get(['id', 'code', 'name']);
        
        $costCenters = \App\Models\CostCenter::where('is_active', true)->get(['id', 'code', 'name']);
        
        return Inertia::render('Vouchers/Create', [
            'type' => $type,
            'employees' => $employees,
            'contacts' => $contacts,
            'paymentMethods' => $paymentMethods,
            'expenseAccounts' => $expenseAccounts,
            'allAccounts' => $allAccounts,
            'costCenters' => $costCenters,
        ]);
    }

    public function store(Request $request)
    {
        $this->syncEmployeesToContacts();
        $type = $request->input('type');
        $rules = [
            'type' => 'required|in:expense,advance,petty_cash_issue,petty_cash_receipt,receipt,payment',
            'voucher_no' => 'required|string|unique:vouchers,voucher_no',
            'date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string',
            'debit_description' => 'nullable|string',
            'credit_description' => 'nullable|string',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'attachment' => 'nullable|file|mimes:pdf,jpg,png,jpeg|max:5120',
        ];

        if ($type === 'expense') {
            $rules['expense_account_id'] = 'required|exists:accounts,id';
            $rules['payment_account_id'] = 'required|exists:accounts,id';
        } elseif ($type === 'advance') {
            $rules['contact_id'] = 'required|exists:contacts,id';
            $rules['payment_account_id'] = 'required|exists:accounts,id';
        } elseif ($type === 'petty_cash_issue') {
            $rules['contact_id'] = 'required|exists:contacts,id';
            $rules['payment_account_id'] = 'required|exists:accounts,id';
        } elseif ($type === 'petty_cash_receipt') {
            $rules['contact_id'] = 'required|exists:contacts,id';
            $rules['expense_account_id'] = 'required|exists:accounts,id';
        } elseif ($type === 'receipt' || $type === 'payment') {
            $rules['debit_account_id'] = 'required|exists:accounts,id';
            $rules['credit_account_id'] = 'required|exists:accounts,id';
        }

        $validated = $request->validate($rules);
        $validated['created_by'] = auth()->id() ?? 1;

        // Resolve Debit and Credit dynamically
        if (!in_array($validated['type'], ['receipt', 'payment'])) {
            $accounts = $this->resolveVoucherAccounts($validated);
            $validated['debit_account_id'] = $accounts['debit'];
            $validated['credit_account_id'] = $accounts['credit'];
        }

        DB::beginTransaction();
        try {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('vouchers', 'public');
            }

            // Unset non-db auxiliary fields
            $voucherData = collect($validated)->except(['expense_account_id', 'payment_account_id', 'attachment'])->toArray();
            $voucherData['attachment_path'] = $attachmentPath;
            
            $voucher = Voucher::create($voucherData);
            
            $this->generateJournalEntry($voucher);
            
            DB::commit();
            return redirect()->route('vouchers.index', ['type' => $voucher->type])
                ->with('success', 'تم تسجيل السند وإنشاء القيد المحاسبي المربوط بنجاح!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'حدث خطأ أثناء حفظ السند: ' . $e->getMessage()]);
        }
    }

    private function resolveVoucherAccounts(array $data)
    {
        $type = $data['type'];
        
        if ($type === 'expense') {
            return [
                'debit' => $data['expense_account_id'],
                'credit' => $data['payment_account_id']
            ];
        }

        if (isset($data['contact_id'])) {
            $contact = Contact::findOrFail($data['contact_id']);
        }

        if ($type === 'receipt' && isset($contact)) {
            return [
                'debit' => $data['debit_account_id'], // Cash/Bank
                'credit' => $contact->receivable_account_id ?: $contact->account_id
            ];
        }

        if ($type === 'payment' && isset($contact)) {
            return [
                'debit' => $contact->payable_account_id ?: $contact->account_id,
                'credit' => $data['credit_account_id'] // Cash/Bank
            ];
        }

        if ($type === 'advance') {
            // Check if contact has a receivable account (Employees might use receivable for advances)
            $advanceAccountId = $contact->receivable_account_id ?: $this->getOrCreateEmployeeAccount($contact, 'advance');
            return [
                'debit' => $advanceAccountId,
                'credit' => $data['payment_account_id']
            ];
        }

        if ($type === 'petty_cash_issue') {
            $pettyCashAccountId = $contact->receivable_account_id ?: $this->getOrCreateEmployeeAccount($contact, 'petty_cash');
            return [
                'debit' => $pettyCashAccountId,
                'credit' => $data['payment_account_id']
            ];
        }

        if ($type === 'petty_cash_receipt') {
            $pettyCashAccountId = $contact->receivable_account_id ?: $this->getOrCreateEmployeeAccount($contact, 'petty_cash');
            return [
                'debit' => $data['expense_account_id'],
                'credit' => $pettyCashAccountId
            ];
        }
    }

    private function getOrCreateEmployeeAccount(Contact $contact, $accountType)
    {
        // استخدام الحساب العام لعهد وسلف الموظفين (1106) لتجنب تضخيم شجرة الحسابات بأسماء الموظفين فردياً
        $account = Account::where('code', '1106')
            ->orWhere('name', 'like', '%عهد وسلف%')
            ->first();

        if ($account) {
            return $account->id;
        }

        $prefix = $accountType === 'advance' ? 'سلفة الموظف' : 'عهدة الموظف';
        $accountName = $prefix . ' - ' . $contact->name;
        
        $account = Account::where('name', $accountName)->first();
        if ($account) {
            return $account->id;
        }

        // Determine parent code logic
        $assetType = AccountType::where('code', 'asset')->first();
        
        $baseCode = $accountType === 'advance' ? '120' : '121';
        $latest = Account::where('code', 'like', $baseCode . '%')->orderBy('code', 'desc')->first();
        $nextCode = $latest ? strval(intval($latest->code) + 1) : $baseCode . '1';

        $newAccount = Account::create([
            'code' => $nextCode,
            'name' => $accountName,
            'account_type_id' => $assetType->id,
            'level' => 3,
            'is_postable' => true,
            'is_active' => true,
        ]);

        return $newAccount->id;
    }

    public function destroy(Voucher $voucher)
    {
        DB::beginTransaction();
        try {
            if ($voucher->journalEntry) {
                $voucher->journalEntry->lines()->delete();
                $voucher->journalEntry->delete();
            }
            $type = $voucher->type;
            $voucher->delete();
            DB::commit();
            
            return redirect()->route('vouchers.index', ['type' => $type])
                ->with('success', 'تم إلغاء السند وحذف قيده بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'لا يمكن حذف السند.']);
        }
    }

    private function generateJournalEntry(Voucher $voucher)
    {
        $fiscalYear = FiscalYear::where('is_closed', false)->first();

        if (!$fiscalYear) {
            throw new \Exception('لا توجد سنة مالية مفتوحة.');
        }

        $typesLabel = [
            'expense' => 'سند صرف مصروف',
            'advance' => 'سند سلفة موظف',
            'petty_cash_issue' => 'سند صرف عهدة نقدية',
            'petty_cash_receipt' => 'سند تسوية عهدة',
            'receipt' => 'سند قبض',
            'payment' => 'سند صرف عام'
        ];

        $description = $typesLabel[$voucher->type] . ' رقم ' . $voucher->voucher_no . ($voucher->description ? ' - ' . $voucher->description : '');

        $this->journalService->create([
            'entry_date' => $voucher->date,
            'description' => $description,
            'fiscal_year_id' => $fiscalYear->id,
            'transaction_type' => 'voucher',
            'reference_id' => $voucher->id,
            'status' => 'posted',
            'lines' => [
                [
                    'account_id' => $voucher->debit_account_id,
                    'contact_id' => $voucher->contact_id,
                    'debit' => $voucher->amount,
                    'credit' => 0,
                    'description' => $voucher->debit_description ?: $description
                ],
                [
                    'account_id' => $voucher->credit_account_id,
                    'contact_id' => $voucher->contact_id,
                    'debit' => 0,
                    'credit' => $voucher->amount,
                    'description' => $voucher->credit_description ?: $description
                ]
            ]
        ]);
    }

    public function cashRegister(Request $request)
    {
        $accountId = $request->input('account_id');
        
        // Fetch Cash and Bank Accounts
        // Restricted to codes starting with 1101 (Cash) and 1102 (Banks)
        $accounts = Account::where('is_postable', true)
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
            $selectedAccount = Account::with('type')->find($accountId);
            $normalBalance = $selectedAccount->type->normal_balance ?? 'debit';

            // Opening Balance before current date/year
            $startDate = $request->input('start_date', date('Y-01-01'));
            $endDate = $request->input('end_date', date('Y-12-31'));

            $openingQuery = DB::table('journal_entry_lines as jel')
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
            $lines = JournalEntryLine::with(['journalEntry', 'contact'])
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
                        'date' => $line->journalEntry->entry_date->format('Y-m-d'),
                        'entry_no' => $line->journalEntry->entry_no,
                        'description' => $line->description ?: $line->journalEntry->description,
                        'debit' => (float) $line->debit,
                        'credit' => (float) $line->credit,
                        'reference_id' => $line->journalEntry->reference_id,
                        'transaction_type' => $line->journalEntry->transaction_type,
                    ];
                });

            // Calculate current balance (all transactions up to end date)
            $currentQuery = DB::table('journal_entry_lines as jel')
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

        return Inertia::render('Vouchers/CashRegister', [
            'accounts' => $accounts,
            'selectedAccountId' => (int)$accountId,
            'openingBalance' => (float)$openingBalance,
            'currentBalance' => (float)$currentBalance,
            'lines' => $lines,
            'filters' => [
                'start_date' => $request->input('start_date', date('Y-01-01')),
                'end_date' => $request->input('end_date', date('Y-12-31')),
            ]
        ]);
    }
}
