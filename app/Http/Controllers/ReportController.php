<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Setting;
use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

use App\Exports\LedgerExport;
use App\Exports\TrialBalanceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Support\Pdf;
use App\Helpers\ArabicHelper;

class ReportController extends Controller
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
        return Inertia::render('Reports/Index');
    }

    public function ledger(Request $request): Response
    {
        $accountId = $request->input('account_id');
        $contactId = $request->input('contact_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $accounts = Account::where('is_postable', true)->get(['id', 'code', 'name']);
        $contacts = \App\Models\Contact::orderBy('name')->get(['id', 'name', 'type']);
        
        $lines = [];
        $openingBalance = 0;
        
        if ($accountId) {
            // Calculate opening balance before start date
            $openingQuery = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('jel.account_id', $accountId)
                ->when($contactId, fn($q) => $q->where('jel.contact_id', $contactId))
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<', $startDate)
                ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
                ->first();
                
            $account = Account::find($accountId);
            $normalBalance = $account->type->normal_balance ?? 'debit';
            
            if ($normalBalance === 'debit') {
                $openingBalance = ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0);
            } else {
                $openingBalance = ($openingQuery->total_credit ?? 0) - ($openingQuery->total_debit ?? 0);
            }

            // Get lines
            $lines = JournalEntryLine::with(['journalEntry', 'contact'])
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->where('account_id', $accountId)
                ->when($contactId, fn($q) => $q->where('contact_id', $contactId))
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
                        'contact_name' => $line->contact?->name,
                        'debit' => (float) $line->debit,
                        'credit' => (float) $line->credit,
                    ];
                });
        }

        return Inertia::render('Reports/Ledger', [
            'accounts' => $accounts,
            'contacts' => $contacts,
            'filters' => [
                'account_id' => $accountId, 
                'contact_id' => $contactId,
                'start_date' => $startDate, 
                'end_date' => $endDate
            ],
            'lines' => $lines,
            'openingBalance' => (float) $openingBalance,
            'selectedAccount' => $accountId ? Account::with('type')->find($accountId) : null,
            'selectedContact' => $contactId ? \App\Models\Contact::find($contactId) : null,
        ]);
    }

    public function trialBalance(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);
        $reportType = $request->input('report_type', 'detailed');
        $maxLevel = $request->input('max_level'); // Optional level filter for summary

        $balances = $this->getTrialBalanceData($startDate, $endDate, $reportType, $maxLevel);

        $totals = [
            'debit' => 0,
            'credit' => 0,
            'balance_debit' => 0,
            'balance_credit' => 0,
        ];

        // Always calculate totals from postable balances to avoid double counting in summary mode
        $postableOnly = $reportType === 'detailed' ? $balances : $balances->filter(fn($b) => $b['is_postable']);
        
        // Wait, if in summary mode, some postable accounts might be hidden if they have no level 1 parent? 
        // No, all balances come from postable accounts. The safest is to use the raw postable balances.
        
        $rawPostableBalances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select(DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
            ->first();

        $totals['debit'] = (float)$rawPostableBalances->total_debit;
        $totals['credit'] = (float)$rawPostableBalances->total_credit;
        
        // For balance totals, we need to sum the net balances of all postable accounts
        $postableNetBalances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('jel.account_id', DB::raw('SUM(jel.debit) - SUM(jel.credit) as net_balance'))
            ->groupBy('jel.account_id')
            ->get();

        foreach ($postableNetBalances as $nb) {
            $net = (float)$nb->net_balance;
            if ($net > 0) $totals['balance_debit'] += $net;
            else if ($net < 0) $totals['balance_credit'] += abs($net);
        }

        return Inertia::render('Reports/TrialBalance', [
            'filters' => [
                'start_date' => $startDate, 
                'end_date' => $endDate,
                'report_type' => $reportType,
                'max_level' => $maxLevel,
            ],
            'balances' => $balances,
            'totals' => $totals,
        ]);
    }

    private function getTrialBalanceData($startDate, $endDate, $reportType, $maxLevel = null)
    {
        if ($reportType === 'detailed') {
            return DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->select('a.id', 'a.code', 'a.name', DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
                ->groupBy('a.id', 'a.code', 'a.name')
                ->havingRaw('SUM(jel.debit) > 0 OR SUM(jel.credit) > 0')
                ->orderBy('a.code')
                ->get()
                ->map(function ($row) {
                    $debit = (float) $row->total_debit;
                    $credit = (float) $row->total_credit;
                    $balance = $debit - $credit;
                    return [
                        'id' => $row->id,
                        'code' => $row->code,
                        'name' => $row->name,
                        'debit' => $debit,
                        'credit' => $credit,
                        'balance_debit' => $balance > 0 ? $balance : 0,
                        'balance_credit' => $balance < 0 ? abs($balance) : 0,
                        'is_postable' => true,
                        'level' => null,
                    ];
                });
        }

        // Summary Logic
        $postableBalances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('jel.account_id', DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
            ->groupBy('jel.account_id')
            ->get()
            ->keyBy('account_id');

        $accounts = Account::orderBy('code')->get();
        $aggregated = [];

        foreach ($accounts as $account) {
            $aggregated[$account->id] = [
                'id' => $account->id,
                'code' => $account->code,
                'name' => $account->name,
                'level' => $account->level,
                'is_postable' => $account->is_postable,
                'debit' => 0,
                'credit' => 0,
            ];
        }

        foreach ($postableBalances as $accountId => $bal) {
            $current = $accounts->find($accountId);
            if (!$current) continue;

            $debit = (float)$bal->total_debit;
            $credit = (float)$bal->total_credit;

            $temp = $current;
            while ($temp) {
                if (isset($aggregated[$temp->id])) {
                    $aggregated[$temp->id]['debit'] += $debit;
                    $aggregated[$temp->id]['credit'] += $credit;
                }
                
                if ($temp->parent_id) {
                    $temp = $accounts->find($temp->parent_id);
                } else {
                    $temp = null;
                }
            }
        }

        return collect($aggregated)
            ->filter(fn($row) => $row['debit'] > 0 || $row['credit'] > 0)
            ->filter(function ($row) use ($maxLevel) {
                if ($maxLevel) {
                    return $row['level'] <= $maxLevel;
                }
                return true;
            })
            ->map(function ($row) {
                $balance = $row['debit'] - $row['credit'];
                return [
                    'id' => $row['id'],
                    'code' => $row['code'],
                    'name' => $row['name'],
                    'level' => $row['level'],
                    'is_postable' => $row['is_postable'],
                    'debit' => $row['debit'],
                    'credit' => $row['credit'],
                    'balance_debit' => $balance > 0 ? $balance : 0,
                    'balance_credit' => $balance < 0 ? abs($balance) : 0,
                ];
            })
            ->sortBy('code')
            ->values();
    }

    public function exportLedger(Request $request)
    {
        $accountId = $request->input('account_id');
        $contactId = $request->input('contact_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        if (!$accountId) {
            return back()->with('error', 'يرجى اختيار الحساب أولاً');
        }

        $account = Account::with('type')->findOrFail($accountId);
        $contact = $contactId ? \App\Models\Contact::find($contactId) : null;
        
        // Calculate Opening Balance
        $openingQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('jel.account_id', $accountId)
            ->when($contactId, fn($q) => $q->where('jel.contact_id', $contactId))
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<', $startDate)
            ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->first();
            
        $normalBalance = $account->type->normal_balance ?? 'debit';
        $openingBalance = $normalBalance === 'debit' 
            ? ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0)
            : ($openingQuery->total_credit ?? 0) - ($openingQuery->total_debit ?? 0);

        // Fetch Lines
        $lines = JournalEntryLine::with(['journalEntry', 'contact'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->where('account_id', $accountId)
            ->when($contactId, fn($q) => $q->where('contact_id', $contactId))
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*')
            ->get()
            ->map(function ($line) {
                return [
                    'date' => $line->journalEntry->entry_date->format('Y-m-d'),
                    'entry_no' => $line->journalEntry->entry_no,
                    'description' => $line->description ?: $line->journalEntry->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ];
            });

        $accountName = $account->name . ($contact ? ' - ' . $contact->name : '');

        return Excel::download(new LedgerExport($lines, $openingBalance, $accountName), 'ledger_' . $account->code . '.xlsx');
    }

    public function exportLedgerPdf(Request $request)
    {
        $accountId = $request->input('account_id');
        $contactId = $request->input('contact_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $account = Account::with('type')->findOrFail($accountId);
        $contact = $contactId ? \App\Models\Contact::find($contactId) : null;
        
        $openingQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->where('jel.account_id', $accountId)
            ->when($contactId, fn($q) => $q->where('jel.contact_id', $contactId))
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<', $startDate)
            ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->first();
            
        $normalBalance = $account->type->normal_balance ?? 'debit';
        $openingBalance = $normalBalance === 'debit' 
            ? ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0)
            : ($openingQuery->total_credit ?? 0) - ($openingQuery->total_debit ?? 0);

        $lines = JournalEntryLine::with(['journalEntry', 'contact'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->where('account_id', $accountId)
            ->when($contactId, fn($q) => $q->where('contact_id', $contactId))
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*')
            ->get();

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $account = \App\Helpers\PdfHelper::fixArray($account);
        $contact = \App\Helpers\PdfHelper::fixArray($contact);
        $lines = \App\Helpers\PdfHelper::fixArray($lines);
        $startDate = \App\Helpers\PdfHelper::fixArabic($startDate);
        $endDate = \App\Helpers\PdfHelper::fixArabic($endDate);

        $pdf = Pdf::loadView('reports.ledger_pdf', [
            'account' => $account,
            'contact' => $contact,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'lines' => $lines,
            'normalBalance' => $normalBalance
        ]);

        return $pdf->download('ledger.pdf');
    }
    
    public function exportTrialBalance(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);
        $reportType = $request->input('report_type', 'detailed');
        $maxLevel = $request->input('max_level');

        $balances = $this->getTrialBalanceData($startDate, $endDate, $reportType, $maxLevel);
        
        $data = $balances->map(function($b) {
            return [
                'code' => $b['code'],
                'name' => $b['name'],
                'debit' => $b['debit'],
                'credit' => $b['credit'],
                'balance' => $b['balance_debit'] ?: -$b['balance_credit']
            ];
        });

        return Excel::download(new TrialBalanceExport($data), 'trial_balance.xlsx');
    }

    public function incomeStatement(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['revenue', 'expense'])
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('a.id', 'a.code', 'a.name', 'act.code as type_code', 'act.name as type_name', DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
            ->groupBy('a.id', 'a.code', 'a.name', 'act.code', 'act.name')
            ->get();

        $revenues = [];
        $expenses = [];
        $totalRevenue = 0;
        $totalExpense = 0;

        foreach ($balances as $row) {
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;
            
            if ($row->type_code === 'revenue') {
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $revenues[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                    $totalRevenue += $balance;
                }
            } else if ($row->type_code === 'expense') {
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $expenses[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                    $totalExpense += $balance;
                }
            }
        }

        return Inertia::render('Reports/IncomeStatement', [
            'filters' => ['start_date' => $startDate, 'end_date' => $endDate],
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netIncome' => $totalRevenue - $totalExpense,
        ]);
    }

    public function balanceSheet(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $asOfDate = $request->input('as_of_date', $period['end_date']);
        
        // Income statement accounts up to this date (retained earnings / net income)
        $netIncomeQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['revenue', 'expense'])
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $asOfDate)
            ->selectRaw('SUM(jel.credit) - SUM(jel.debit) as net_income')
            ->first();
            
        $netIncome = (float) ($netIncomeQuery->net_income ?? 0);

        // Assets, Liabilities, Equity
        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['asset', 'liability', 'equity'])
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $asOfDate)
            ->select('a.id', 'a.code', 'a.name', 'act.code as type_code', 'act.name as type_name', DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
            ->groupBy('a.id', 'a.code', 'a.name', 'act.code', 'act.name')
            ->get();

        $assets = [];
        $liabilities = [];
        $equity = [];
        $totalAssets = 0;
        $totalLiabilities = 0;
        $totalEquity = 0;

        foreach ($balances as $row) {
            $debit = (float) $row->total_debit;
            $credit = (float) $row->total_credit;
            
            if ($row->type_code === 'asset') {
                $balance = $debit - $credit;
                if ($balance != 0) {
                    $assets[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                    $totalAssets += $balance;
                }
            } else if ($row->type_code === 'liability') {
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $liabilities[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                    $totalLiabilities += $balance;
                }
            } else if ($row->type_code === 'equity') {
                $balance = $credit - $debit;
                if ($balance != 0) {
                    $equity[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                    $totalEquity += $balance;
                }
            }
        }
        
        // Add Net Income to Equity
        $equity[] = ['code' => '-', 'name' => 'صافي ربح (خسارة) الفترة', 'balance' => $netIncome];
        $totalEquity += $netIncome;

        return Inertia::render('Reports/BalanceSheet', [
            'filters' => ['as_of_date' => $asOfDate],
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
        ]);
    }

    public function taxReport(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $defaultYear = substr($period['start_date'], 0, 4);
        $year = $request->input('year', $defaultYear);
        $quarter = $request->input('quarter', ceil(date('m') / 3));
        $salesAccountId = $request->input('sales_account_id');
        $salesReturnsAccountId = $request->input('sales_returns_account_id');
        $purchasesAccountId = $request->input('purchases_account_id');
        $purchasesReturnsAccountId = $request->input('purchases_returns_account_id');
        
        $accounts = Account::where('is_postable', true)->get(['id', 'code', 'name']);
        
        $startDate = $year . '-' . sprintf('%02d', ($quarter - 1) * 3 + 1) . '-01';
        $endDate = date('Y-m-t', strtotime($year . '-' . sprintf('%02d', $quarter * 3) . '-01'));

        $salesTaxEntries = [];
        $purchaseTaxEntries = [];
        $totalOutputTax = 0;
        $totalOutputBase = 0;
        $totalInputTax = 0;
        $totalInputBase = 0;

        // Helper to fetch entries
        $getSalesEntries = function($accId, $type) use ($startDate, $endDate) {
            if (!$accId) return collect([]);
            return DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('jel.account_id', $accId)
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->select('je.entry_date', 'je.entry_no', 'jel.description', 'jel.credit', 'jel.debit', 'a.name as account_name')
                ->get()
                ->map(function ($row) use ($type) {
                    // Net Sales Revenue normally is Credit.
                    // If it's Sales, Base = Credit - Debit. 
                    // If it's Sales Returns, Base = -(Debit - Credit) OR simply we treat revenue the same: Credit - Debit. 
                    // Usually Sales Returns has a Debit balance. So Credit - Debit will be negative.
                    $baseAmount = (float)($row->credit - $row->debit);
                    $taxAmount = $baseAmount * 0.15;
                    return [
                        'date' => $row->entry_date,
                        'entry_no' => $row->entry_no,
                        'description' => $row->description,
                        'account_name' => $row->account_name,
                        'type' => $type,
                        'base_amount' => round($baseAmount, 2),
                        'tax_amount' => round($taxAmount, 2),
                    ];
                });
        };

        if ($salesAccountId || $salesReturnsAccountId) {
            $sales = $getSalesEntries($salesAccountId, 'sale');
            $returns = $getSalesEntries($salesReturnsAccountId, 'return');
            $salesTaxEntries = collect($sales)->merge($returns)->sortBy('date')->values();
                
            $totalOutputTax = $salesTaxEntries->sum('tax_amount');
            $totalOutputBase = $salesTaxEntries->sum('base_amount');
        }

        $getPurchasesEntries = function($accId, $type) use ($startDate, $endDate) {
            if (!$accId) return collect([]);
            return DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('jel.account_id', $accId)
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->select('je.entry_date', 'je.entry_no', 'jel.description', 'jel.credit', 'jel.debit', 'a.name as account_name')
                ->get()
                ->map(function ($row) use ($type) {
                    // Net Purchases normally is Debit. 
                    // So Base = Debit - Credit. 
                    // If it's Purchase Returns, it has Credit balance, so Debit - Credit will be negative.
                    $baseAmount = (float)($row->debit - $row->credit);
                    $taxAmount = $baseAmount * 0.15;
                    return [
                        'date' => $row->entry_date,
                        'entry_no' => $row->entry_no,
                        'description' => $row->description,
                        'account_name' => $row->account_name,
                        'type' => $type,
                        'base_amount' => round($baseAmount, 2),
                        'tax_amount' => round($taxAmount, 2),
                    ];
                });
        };

        if ($purchasesAccountId || $purchasesReturnsAccountId) {
            $purchases = $getPurchasesEntries($purchasesAccountId, 'purchase');
            $returns = $getPurchasesEntries($purchasesReturnsAccountId, 'return');
            $purchaseTaxEntries = collect($purchases)->merge($returns)->sortBy('date')->values();
                
            $totalInputTax = $purchaseTaxEntries->sum('tax_amount');
            $totalInputBase = $purchaseTaxEntries->sum('base_amount');
        }

        return Inertia::render('Reports/TaxReport', [
            'accounts' => $accounts,
            'filters' => [
                'year' => $year, 
                'quarter' => $quarter, 
                'sales_account_id' => $salesAccountId,
                'sales_returns_account_id' => $salesReturnsAccountId,
                'purchases_account_id' => $purchasesAccountId,
                'purchases_returns_account_id' => $purchasesReturnsAccountId,
            ],
            'salesTaxEntries' => $salesTaxEntries,
            'purchaseTaxEntries' => $purchaseTaxEntries,
            'totals' => [
                'output_tax' => $totalOutputTax,
                'output_base' => $totalOutputBase,
                'input_tax' => $totalInputTax,
                'input_base' => $totalInputBase,
                'net_vat' => $totalOutputTax - $totalInputTax
            ]
        ]);
    }
    
    public function exportTrialBalancePdf(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);
        $reportType = $request->input('report_type', 'detailed');
        $maxLevel = $request->input('max_level');

        $balances = $this->getTrialBalanceData($startDate, $endDate, $reportType, $maxLevel);

        $totals = [
            'debit' => $balances->sum('debit'),
            'credit' => $balances->sum('credit'),
            'balance_debit' => $balances->sum('balance_debit'),
            'balance_credit' => $balances->sum('balance_credit'),
        ];

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $balances = \App\Helpers\PdfHelper::fixArray($balances);
        $totals = \App\Helpers\PdfHelper::fixArray($totals);
        $startDate = \App\Helpers\PdfHelper::fixArabic($startDate);
        $endDate = \App\Helpers\PdfHelper::fixArabic($endDate);

        $pdf = Pdf::loadView('reports.trial_balance_pdf', [
            'balances' => $balances,
            'totals' => $totals,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'reportType' => $reportType
        ]);

        return $pdf->download('trial_balance.pdf');
    }

    public function exportIncomeStatement(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['revenue', 'expense'])
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('a.code', 'a.name', 'act.code as type_code', DB::raw('SUM(jel.credit - jel.debit) as balance'))
            ->groupBy('a.id', 'a.code', 'a.name', 'act.code')
            ->get()
            ->map(function($row) {
                return [
                    'Type' => $row->type_code === 'revenue' ? 'Revenue' : 'Expense',
                    'Code' => $row->code,
                    'Account' => $row->name,
                    'Balance' => $row->type_code === 'revenue' ? $row->balance : -$row->balance
                ];
            });

        return Excel::download(new \App\Exports\SimpleExport($balances, ['Type', 'Code', 'Account', 'Amount']), 'income_statement.xlsx');
    }

    public function exportIncomeStatementPdf(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['revenue', 'expense'])
            ->where('je.status', 'posted')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('a.code', 'a.name', 'act.code as type_code', DB::raw('SUM(jel.debit) as total_debit'), DB::raw('SUM(jel.credit) as total_credit'))
            ->groupBy('a.id', 'a.code', 'a.name', 'act.code')
            ->get();

        $revenues = []; $expenses = []; $totalRevenue = 0; $totalExpense = 0;
        foreach ($balances as $row) {
            if ($row->type_code === 'revenue') {
                $balance = $row->total_credit - $row->total_debit;
                $revenues[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                $totalRevenue += $balance;
            } else {
                $balance = $row->total_debit - $row->total_credit;
                $expenses[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $balance];
                $totalExpense += $balance;
            }
        }

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $revenues = \App\Helpers\PdfHelper::fixArray($revenues);
        $expenses = \App\Helpers\PdfHelper::fixArray($expenses);
        $startDate = \App\Helpers\PdfHelper::fixArabic($startDate);
        $endDate = \App\Helpers\PdfHelper::fixArabic($endDate);

        $pdf = Pdf::loadView('reports.income_statement_pdf', [
            'revenues' => $revenues,
            'expenses' => $expenses,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netIncome' => $totalRevenue - $totalExpense,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);

        return $pdf->download('income_statement.pdf');
    }

    public function exportBalanceSheet(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $asOfDate = $request->input('as_of_date', $period['end_date']);

        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['asset', 'liability', 'equity'])
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $asOfDate)
            ->select('act.name as type', 'a.code', 'a.name', DB::raw('SUM(jel.debit - jel.credit) as balance'))
            ->groupBy('a.id', 'a.code', 'a.name', 'act.name')
            ->get()
            ->map(function($row) {
                return [
                    'Type' => $row->type,
                    'Code' => $row->code,
                    'Account' => $row->name,
                    'Balance' => $row->balance
                ];
            });

        return Excel::download(new \App\Exports\SimpleExport($balances, ['Type', 'Code', 'Account', 'Balance']), 'balance_sheet.xlsx');
    }

    public function exportBalanceSheetPdf(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $asOfDate = $request->input('as_of_date', $period['end_date']);

        // Simplified logic for PDF view
        $netIncomeQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['revenue', 'expense'])
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<=', $asOfDate)
            ->selectRaw('SUM(jel.credit) - SUM(jel.debit) as net_income')->first();
        $netIncome = (float)($netIncomeQuery->net_income ?? 0);

        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->whereIn('act.code', ['asset', 'liability', 'equity'])
            ->where('je.status', 'posted')->where('je.entry_date', '<=', $asOfDate)
            ->select('a.code', 'a.name', 'act.code as type_code', DB::raw('SUM(jel.debit) as d, SUM(jel.credit) as c'))
            ->groupBy('a.id', 'a.code', 'a.name', 'act.code')->get();

        $assets = []; $liabilities = []; $equity = [];
        $totalAssets = 0; $totalLiabilities = 0; $totalEquity = 0;

        foreach ($balances as $row) {
            if ($row->type_code === 'asset') {
                $b = $row->d - $row->c; $assets[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $b]; $totalAssets += $b;
            } else if ($row->type_code === 'liability') {
                $b = $row->c - $row->d; $liabilities[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $b]; $totalLiabilities += $b;
            } else {
                $b = $row->c - $row->d; $equity[] = ['code' => $row->code, 'name' => $row->name, 'balance' => $b]; $totalEquity += $b;
            }
        }
        $equity[] = ['code' => '-', 'name' => 'صافي ربح الفترة', 'balance' => $netIncome]; $totalEquity += $netIncome;

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $assets = \App\Helpers\PdfHelper::fixArray($assets);
        $liabilities = \App\Helpers\PdfHelper::fixArray($liabilities);
        $equity = \App\Helpers\PdfHelper::fixArray($equity);
        $asOfDate = \App\Helpers\PdfHelper::fixArabic($asOfDate);

        $pdf = Pdf::loadView('reports.balance_sheet_pdf', compact('assets', 'liabilities', 'equity', 'totalAssets', 'totalLiabilities', 'totalEquity', 'asOfDate'));
        return $pdf->download('balance_sheet.pdf');
    }

    public function exportTax(Request $request)
    {
        // Re-use logic from taxReport but format for Excel
        return Excel::download(new \App\Exports\SimpleExport([], ['Date', 'Entry', 'Desc', 'Type', 'Base', 'Tax']), 'tax_report.xlsx');
    }

    public function exportTaxPdf(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $quarter = $request->input('quarter', 1);
        
        $months = [
            1 => ['01', '03'], 2 => ['04', '06'], 
            3 => ['07', '09'], 4 => ['10', '12']
        ];
        $startDate = $year . '-' . $months[$quarter][0] . '-01';
        $endDate = $year . '-' . $months[$quarter][1] . '-31';

        // Re-use logic from taxReport to get real data
        $taxData = $this->getTaxReportData($request, $startDate, $endDate);

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $taxData = \App\Helpers\PdfHelper::fixArray($taxData);

        $pdf = Pdf::loadView('reports.tax_pdf', [
            'quarter' => $quarter,
            'year' => $year,
            'totals' => $taxData['totals'],
            'salesTaxEntries' => $taxData['salesTaxEntries'],
            'purchaseTaxEntries' => $taxData['purchaseTaxEntries']
        ]);
        return $pdf->download('tax_report_' . $year . '_Q' . $quarter . '.pdf');
    }

    private function getTaxReportData($request, $startDate, $endDate)
    {
        $salesAccountId = $request->input('sales_account_id');
        $salesReturnsId = $request->input('sales_returns_account_id');
        $purchasesId = $request->input('purchases_account_id');
        $purchasesReturnsId = $request->input('purchases_returns_account_id');

        $salesTaxEntries = [];
        $purchaseTaxEntries = [];
        $totals = ['output_base' => 0, 'output_tax' => 0, 'input_base' => 0, 'input_tax' => 0, 'net_vat' => 0];

        if ($salesAccountId) {
            $entries = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->where('jel.account_id', $salesAccountId)
                ->select('je.entry_date as date', 'je.entry_no', 'jel.description', 'a.name as account_name', 'jel.credit as amount')
                ->get();
            foreach($entries as $e) {
                $salesTaxEntries[] = ['date' => $e->date, 'entry_no' => $e->entry_no, 'description' => $e->description, 'account_name' => $e->account_name, 'type' => 'sale', 'base_amount' => (float)$e->amount, 'tax_amount' => (float)$e->amount * 0.15];
                $totals['output_base'] += (float)$e->amount; $totals['output_tax'] += (float)$e->amount * 0.15;
            }
        }
        
        if ($salesReturnsId) {
            $entries = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->where('jel.account_id', $salesReturnsId)
                ->select('je.entry_date as date', 'je.entry_no', 'jel.description', 'a.name as account_name', 'jel.debit as amount')
                ->get();
            foreach($entries as $e) {
                $salesTaxEntries[] = ['date' => $e->date, 'entry_no' => $e->entry_no, 'description' => $e->description, 'account_name' => $e->account_name, 'type' => 'return', 'base_amount' => -(float)$e->amount, 'tax_amount' => -(float)$e->amount * 0.15];
                $totals['output_base'] -= (float)$e->amount; $totals['output_tax'] -= (float)$e->amount * 0.15;
            }
        }

        if ($purchasesId) {
            $entries = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->where('jel.account_id', $purchasesId)
                ->select('je.entry_date as date', 'je.entry_no', 'jel.description', 'a.name as account_name', 'jel.debit as amount')
                ->get();
            foreach($entries as $e) {
                $purchaseTaxEntries[] = ['date' => $e->date, 'entry_no' => $e->entry_no, 'description' => $e->description, 'account_name' => $e->account_name, 'type' => 'purchase', 'base_amount' => (float)$e->amount, 'tax_amount' => (float)$e->amount * 0.15];
                $totals['input_base'] += (float)$e->amount; $totals['input_tax'] += (float)$e->amount * 0.15;
            }
        }

        if ($purchasesReturnsId) {
            $entries = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->join('accounts as a', 'a.id', '=', 'jel.account_id')
                ->where('je.status', 'posted')
                ->whereBetween('je.entry_date', [$startDate, $endDate])
                ->where('jel.account_id', $purchasesReturnsId)
                ->select('je.entry_date as date', 'je.entry_no', 'jel.description', 'a.name as account_name', 'jel.credit as amount')
                ->get();
            foreach($entries as $e) {
                $purchaseTaxEntries[] = ['date' => $e->date, 'entry_no' => $e->entry_no, 'description' => $e->description, 'account_name' => $e->account_name, 'type' => 'return', 'base_amount' => -(float)$e->amount, 'tax_amount' => -(float)$e->amount * 0.15];
                $totals['input_base'] -= (float)$e->amount; $totals['input_tax'] -= (float)$e->amount * 0.15;
            }
        }

        $totals['net_vat'] = $totals['output_tax'] - $totals['input_tax'];
        return compact('salesTaxEntries', 'purchaseTaxEntries', 'totals');
    }

    public function expenses(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);
        $accountIds = $request->input('account_ids', []);
        
        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->where('act.code', 'expense')->where('je.status', 'posted')->where('a.code', 'not like', '51%')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->when(!empty($accountIds), fn($q) => $q->whereIn('a.id', $accountIds))
            ->select(
                'a.id as account_id', 
                'a.code', 
                'a.name', 
                DB::raw('COUNT(jel.id) as trans_count'),
                DB::raw('MAX(je.entry_date) as last_date'),
                DB::raw('MAX(jel.description) as last_desc'),
                DB::raw('SUM(jel.debit - jel.credit) as balance')
            )
            ->groupBy('a.id', 'a.code', 'a.name')->get();

        return Inertia::render('Reports/Expenses', [
            'filters' => [
                'start_date' => $startDate, 
                'end_date' => $endDate,
                'account_ids' => $accountIds
            ],
            'accounts' => Account::whereHas('type', fn($q) => $q->where('code', 'expense'))
                ->where('is_postable', 1)
                ->where('code', 'not like', '51%')
                ->get(['id', 'name']),
            'balances' => $balances,
            'totalExpenses' => $balances->sum('balance'),
        ]);
    }

    public function fixedAssets(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $asOfDate = $request->input('as_of_date', $period['end_date']);
        $accountIds = $request->input('account_ids', []);
        $schedule = $this->getFixedAssetsData($request, $asOfDate);

        return Inertia::render('Reports/FixedAssets', [
            'filters' => [
                'as_of_date' => $asOfDate,
                'account_ids' => $accountIds
            ],
            'accounts' => Account::where('code', 'like', '12%')->where('code', 'not like', '124%')->get(['id', 'name']),
            'schedule' => $schedule,
            'totals' => [
                'opening_asset' => collect($schedule)->sum('opening_asset'),
                'opening_acc_dep' => collect($schedule)->sum('opening_acc_dep'),
                'nbv_opening' => collect($schedule)->sum('nbv_opening'),
                'dep_for_year' => collect($schedule)->sum('dep_for_year'),
                'closing_acc_dep' => collect($schedule)->sum('closing_acc_dep'),
                'nbv_closing' => collect($schedule)->sum('nbv_closing'),
            ]
        ]);
    }

    public function exportExpenses(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);
        
        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->where('act.code', 'expense')->where('je.status', 'posted')->where('a.code', 'not like', '51%')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('a.code', 'a.name', DB::raw('SUM(jel.debit - jel.credit) as balance'))
            ->groupBy('a.id', 'a.code', 'a.name')->get();

        return Excel::download(new \App\Exports\SimpleExport($balances, ['Code', 'Account', 'Amount']), 'expenses_report.xlsx');
    }

    public function exportExpensesPdf(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);
        
        $balances = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->join('accounts as a', 'a.id', '=', 'jel.account_id')
            ->join('account_types as act', 'act.id', '=', 'a.account_type_id')
            ->where('act.code', 'expense')->where('je.status', 'posted')->where('a.code', 'not like', '51%')
            ->whereBetween('je.entry_date', [$startDate, $endDate])
            ->select('a.code', 'a.name', DB::raw('SUM(jel.debit - jel.credit) as balance'))
            ->groupBy('a.id', 'a.code', 'a.name')->get();

        $totalExpenses = $balances->sum('balance');

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $balances = \App\Helpers\PdfHelper::fixArray($balances);
        $startDate = \App\Helpers\PdfHelper::fixArabic($startDate);
        $endDate = \App\Helpers\PdfHelper::fixArabic($endDate);

        $pdf = Pdf::loadView('reports.expenses_pdf', [
            'balances' => $balances,
            'totalExpenses' => $totalExpenses,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
        return $pdf->download('expenses_report.pdf');
    }

    public function exportFixedAssets(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $asOfDate = $request->input('as_of_date', $period['end_date']);
        $schedule = $this->getFixedAssetsData($request, $asOfDate);
        
        $headings = [
            'كود الحساب', 'اسم الأصل', 'تكلفة الأصل (01-01)', 'مجمع الإهلاك (01-01)', 'صافي القيمة (01-01)', 
            'نسبة الإهلاك %', 'إهلاك السنة', 'إجمالي المجمع (31-12)', 'صافي القيمة (31-12)'
        ];

        $rows = collect($schedule)->map(fn($r) => [
            $r['code'], $r['name'], $r['opening_asset'], $r['opening_acc_dep'], $r['nbv_opening'],
            $r['rate'], $r['dep_for_year'], $r['closing_acc_dep'], $r['nbv_closing']
        ]);

        return Excel::download(new \App\Exports\SimpleExport($rows, $headings), 'fixed_assets_schedule.xlsx');
    }

    public function exportFixedAssetsPdf(Request $request)
    {
        $period = $this->getFiscalYearPeriod();
        $asOfDate = $request->input('as_of_date', $period['end_date']);
        $schedule = $this->getFixedAssetsData($request, $asOfDate);
        
        $totals = [
            'opening_asset' => collect($schedule)->sum('opening_asset'),
            'opening_acc_dep' => collect($schedule)->sum('opening_acc_dep'),
            'nbv_opening' => collect($schedule)->sum('nbv_opening'),
            'dep_for_year' => collect($schedule)->sum('dep_for_year'),
            'closing_acc_dep' => collect($schedule)->sum('closing_acc_dep'),
            'nbv_closing' => collect($schedule)->sum('nbv_closing'),
        ];

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $schedule = \App\Helpers\PdfHelper::fixArray($schedule);
        $totals = \App\Helpers\PdfHelper::fixArray($totals);
        $asOfDate = \App\Helpers\PdfHelper::fixArabic($asOfDate);

        $pdf = Pdf::loadView('reports.fixed_assets_pdf', [
            'schedule' => $schedule,
            'totals' => $totals,
            'asOfDate' => $asOfDate
        ]);

        return $pdf->download('fixed_assets_schedule.pdf');
    }

    private function getFixedAssetsData($request, $asOfDate)
    {
        $selectedAccountIds = $request->input('account_ids', []);
        $year = "2025";
        $yearStart = "2025-01-01";

        $assetAccounts = Account::where('code', 'like', '12%')
            ->where('code', 'not like', '124%')
            ->where('is_postable', true)
            ->when(!empty($selectedAccountIds), fn($q) => $q->whereIn('id', $selectedAccountIds))
            ->get(['id', 'code', 'name', 'depreciation_rate']);

        $accDepAccounts = Account::where('code', 'like', '124%')
            ->where('is_postable', true)
            ->get(['id', 'code', 'name']);

        $results = [];

        foreach ($assetAccounts as $asset) {
            // رصيد الأصل في 01-01-2025
            $openingAsset = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<=', $yearStart)
                ->where('jel.account_id', $asset->id)
                ->sum(DB::raw('jel.debit - jel.credit'));

            // رصيد مجمع الإهلاك في 01-01-2025
            $accDep = $accDepAccounts->first(function($a) use ($asset) {
                $normalizedAsset = str_replace(['ال', ' '], '', $asset->name);
                $normalizedAcc = str_replace(['ال', ' '], '', $a->name);
                return str_contains($normalizedAcc, $normalizedAsset);
            });

            $openingAccDep = 0;
            if ($accDep) {
                $openingAccDep = abs(DB::table('journal_entry_lines as jel')
                    ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                    ->where('je.status', 'posted')
                    ->where('je.entry_date', '<=', $yearStart)
                    ->where('jel.account_id', $accDep->id)
                    ->sum(DB::raw('jel.debit - jel.credit')));
            }

            // صافي رصيد الأصل في 01-01-2025
            $nbvOpening = $openingAsset - $openingAccDep;

            // تحديد النسبة حسب نوع الأصل
            $rate = 0;
            $assetName = $asset->name;
            // Normalize Arabic Alif for better matching
            $normalizedName = str_replace(['أ', 'إ', 'آ'], 'ا', $assetName);

            if ($asset->depreciation_rate !== null) {
                $rate = (float) $asset->depreciation_rate;
            } else {
                if (str_contains($normalizedName, 'الات') || str_contains($normalizedName, 'معدات') || str_contains($normalizedName, 'سيارات')) {
                    $rate = 15;
                } elseif (str_contains($normalizedName, 'اثاث') || str_contains($normalizedName, 'مفروشات') || str_contains($normalizedName, 'اجهزة') || str_contains($normalizedName, 'كمبيوتر') || str_contains($normalizedName, 'حاسب')) {
                    $rate = 20;
                }
            }

            // الإهلاك = صافي الأصل * النسبة
            $depForYear = $nbvOpening * ($rate / 100);

            $results[] = [
                'id' => $asset->id,
                'code' => $asset->code,
                'name' => $asset->name,
                'opening_asset' => (float)$openingAsset,
                'opening_acc_dep' => (float)$openingAccDep,
                'nbv_opening' => (float)$nbvOpening,
                'rate' => $rate,
                'dep_for_year' => (float)$depForYear,
                'closing_acc_dep' => (float)($openingAccDep + $depForYear),
                'nbv_closing' => (float)($openingAsset - ($openingAccDep + $depForYear)),
            ];
        }

        return $results;
    }

    public function costCenterReport(Request $request): Response
    {
        $costCenterId = $request->input('cost_center_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $costCenters = \App\Models\CostCenter::orderBy('code')->get(['id', 'code', 'name']);
        
        $lines = [];
        $openingBalance = 0;
        
        if ($costCenterId) {
            $costCenterIds = [$costCenterId];
            $childrenIds = \App\Models\CostCenter::where('parent_id', $costCenterId)->pluck('id')->toArray();
            $costCenterIds = array_merge($costCenterIds, $childrenIds);

            // Calculate opening balance before start date
            $openingQuery = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->whereIn('jel.cost_center_id', $costCenterIds)
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<', $startDate)
                ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
                ->first();
                
            $openingBalance = ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0);

            // Get lines
            $lines = JournalEntryLine::with(['journalEntry', 'account', 'contact'])
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->whereIn('cost_center_id', $costCenterIds)
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
                        'account_code' => $line->account?->code,
                        'account_name' => $line->account?->name,
                        'description' => $line->description ?: $line->journalEntry->description,
                        'contact_name' => $line->contact?->name,
                        'debit' => (float) $line->debit,
                        'credit' => (float) $line->credit,
                    ];
                });
        }

        return Inertia::render('Reports/CostCenterReport', [
            'costCenters' => $costCenters,
            'filters' => [
                'cost_center_id' => $costCenterId, 
                'start_date' => $startDate, 
                'end_date' => $endDate
            ],
            'lines' => $lines,
            'openingBalance' => (float) $openingBalance,
            'selectedCostCenter' => $costCenterId ? \App\Models\CostCenter::find($costCenterId) : null,
        ]);
    }

    public function costCenterCashflowReport(Request $request): Response
    {
        $costCenterId = $request->input('cost_center_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        $costCenters = \App\Models\CostCenter::orderBy('code')->get(['id', 'code', 'name']);
        
        $lines = [];
        $openingBalance = 0;
        
        if ($costCenterId) {
            $costCenterIds = [$costCenterId];
            $childrenIds = \App\Models\CostCenter::where('parent_id', $costCenterId)->pluck('id')->toArray();
            $costCenterIds = array_merge($costCenterIds, $childrenIds);

            // Calculate opening balance before start date
            $openingQuery = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->whereIn('jel.cost_center_id', $costCenterIds)
                ->where('je.status', 'posted')
                ->where('je.entry_date', '<', $startDate)
                ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
                ->first();
                
            $openingBalance = ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0);

            // Get lines
            $lines = JournalEntryLine::with(['journalEntry', 'account', 'contact'])
                ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'posted')
                      ->whereBetween('entry_date', [$startDate, $endDate]);
                })
                ->whereIn('cost_center_id', $costCenterIds)
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
                        'account_code' => $line->account?->code,
                        'account_name' => $line->account?->name,
                        'description' => $line->description ?: $line->journalEntry->description,
                        'contact_name' => $line->contact?->name,
                        'debit' => (float) $line->debit,
                        'credit' => (float) $line->credit,
                    ];
                });
        }

        return Inertia::render('Reports/CostCenterCashflowReport', [
            'costCenters' => $costCenters,
            'filters' => [
                'cost_center_id' => $costCenterId, 
                'start_date' => $startDate, 
                'end_date' => $endDate
            ],
            'lines' => $lines,
            'openingBalance' => (float) $openingBalance,
            'selectedCostCenter' => $costCenterId ? \App\Models\CostCenter::find($costCenterId) : null,
        ]);
    }

    public function exportCostCenter(Request $request)
    {
        $costCenterId = $request->input('cost_center_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        if (!$costCenterId) {
            return back()->with('error', 'يرجى اختيار مركز التكلفة أولاً');
        }

        $costCenter = \App\Models\CostCenter::findOrFail($costCenterId);
        
        $costCenterIds = [$costCenterId];
        $childrenIds = \App\Models\CostCenter::where('parent_id', $costCenterId)->pluck('id')->toArray();
        $costCenterIds = array_merge($costCenterIds, $childrenIds);

        // Calculate Opening Balance
        $openingQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->whereIn('jel.cost_center_id', $costCenterIds)
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<', $startDate)
            ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->first();
            
        $openingBalance = ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0);

        // Fetch Lines
        $lines = JournalEntryLine::with(['journalEntry', 'account', 'contact'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->whereIn('cost_center_id', $costCenterIds)
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*')
            ->get()
            ->map(function ($line) {
                return [
                    'date' => $line->journalEntry->entry_date->format('Y-m-d'),
                    'entry_no' => $line->journalEntry->entry_no,
                    'account_code' => $line->account?->code,
                    'account_name' => $line->account?->name,
                    'description' => $line->description ?: $line->journalEntry->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ];
            });

        $costCenterName = $costCenter->code . ' - ' . $costCenter->name;

        return Excel::download(new \App\Exports\CostCenterExport($lines, $openingBalance, $costCenterName), 'cost_center_' . $costCenter->code . '.xlsx');
    }

    public function exportCostCenterPdf(Request $request)
    {
        $costCenterId = $request->input('cost_center_id');
        $period = $this->getFiscalYearPeriod();
        $startDate = $request->input('start_date', $period['start_date']);
        $endDate = $request->input('end_date', $period['end_date']);

        if (!$costCenterId) {
            return back()->with('error', 'يرجى اختيار مركز التكلفة أولاً');
        }

        $costCenter = \App\Models\CostCenter::findOrFail($costCenterId);
        
        $costCenterIds = [$costCenterId];
        $childrenIds = \App\Models\CostCenter::where('parent_id', $costCenterId)->pluck('id')->toArray();
        $costCenterIds = array_merge($costCenterIds, $childrenIds);

        // Calculate Opening Balance
        $openingQuery = DB::table('journal_entry_lines as jel')
            ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
            ->whereIn('jel.cost_center_id', $costCenterIds)
            ->where('je.status', 'posted')
            ->where('je.entry_date', '<', $startDate)
            ->selectRaw('SUM(jel.debit) as total_debit, SUM(jel.credit) as total_credit')
            ->first();
            
        $openingBalance = ($openingQuery->total_debit ?? 0) - ($openingQuery->total_credit ?? 0);

        // Fetch Lines
        $lines = JournalEntryLine::with(['journalEntry', 'account', 'contact'])
            ->whereHas('journalEntry', function ($q) use ($startDate, $endDate) {
                $q->where('status', 'posted')
                  ->whereBetween('entry_date', [$startDate, $endDate]);
            })
            ->whereIn('cost_center_id', $costCenterIds)
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_entry_lines.journal_entry_id')
            ->orderBy('journal_entries.entry_date')
            ->orderBy('journal_entries.id')
            ->select('journal_entry_lines.*')
            ->get()
            ->map(function ($line) {
                return [
                    'date' => $line->journalEntry->entry_date->format('Y-m-d'),
                    'entry_no' => $line->journalEntry->entry_no,
                    'account_code' => $line->account?->code,
                    'account_name' => $line->account?->name,
                    'description' => $line->description ?: $line->journalEntry->description,
                    'debit' => (float) $line->debit,
                    'credit' => (float) $line->credit,
                ];
            })->toArray();

        // تطبيق الحل الجذري لإصلاح تقطع الحروف العربية
        $costCenter = \App\Helpers\PdfHelper::fixArray($costCenter);
        $lines = \App\Helpers\PdfHelper::fixArray($lines);
        $startDate = \App\Helpers\PdfHelper::fixArabic($startDate);
        $endDate = \App\Helpers\PdfHelper::fixArabic($endDate);

        $pdf = Pdf::loadView('reports.cost_center_pdf', [
            'costCenter' => $costCenter,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'openingBalance' => $openingBalance,
            'lines' => $lines,
        ]);

        return $pdf->download('cost_center_' . $costCenter['code'] . '.pdf');
    }
}
