<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJournalEntryRequest;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\Contact;
use App\Models\Setting;
use App\Services\JournalEntryService;
use App\Traits\FiscalYearHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;
use DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JournalEntriesExport;

class JournalEntryController extends Controller
{
    use FiscalYearHelper;

    public function __construct(
        protected JournalEntryService $journalEntryService
    ) {
    }

    public function index(Request $request): Response
    {
        $period = $this->getFiscalYearPeriod();
        $defaultYear = $this->getDefaultFiscalYear();
        
        $filterStartDate = $request->input('start_date', $period['start_date']);
        $filterEndDate = $request->input('end_date', $period['end_date']);

        $status = $request->input('status');
        $search = $request->input('search');

        $entries = JournalEntry::with(['fiscalYear'])
            ->whereBetween('entry_date', [$filterStartDate, $filterEndDate])
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('entry_no', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
                });
            })
            ->latest('entry_date')
            ->latest('id')
            ->paginate(50)
            ->withQueryString();

        $entriesData = collect($entries->items())->map(function (JournalEntry $entry) {
                return [
                    'id' => $entry->id,
                    'entry_no' => $entry->entry_no,
                    'entry_date' => $entry->entry_date?->format('Y-m-d'),
                    'description' => $entry->description,
                    'status' => $entry->status,
                    'fiscal_year' => $entry->fiscalYear?->name,
                    'total_debit' => $entry->total_debit,
                    'total_credit' => $entry->total_credit,
                ];
            });

        return Inertia::render('JournalEntries/Index', [
            'entries' => $entriesData,
            'pagination' => [
                'links' => $entries->linkCollection()->toArray(),
                'total' => $entries->total(),
            ],
            'fiscalYears' => $this->getAllFiscalYears(),
            'selectedYearId' => $defaultYear?->id,
            'startDate' => $filterStartDate,
            'endDate' => $filterEndDate,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function create(): Response
    {
        $accounts = Account::where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $period = $this->getFiscalYearPeriod();
        $defaultYear = $this->getDefaultFiscalYear();

        $contacts = Contact::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('JournalEntries/Create', [
            'accounts' => $accounts,
            'fiscalYears' => $this->getAllFiscalYears(),
            'selectedFiscalYearId' => $defaultYear?->id,
            'defaultEntryDate' => $period['start_date'],
            'contacts' => $contacts,
        ]);
    }

    public function store(StoreJournalEntryRequest $request): RedirectResponse
    {
        try {
            $entry = $this->journalEntryService->create($request->validated());

            return redirect()
                ->route('journal.entries.show', $entry->id)
                ->with('success', 'تم إنشاء القيد بنجاح');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function show(JournalEntry $entry): Response
    {
        $entry->load(['fiscalYear', 'lines.account']);

        return Inertia::render('JournalEntries/Show', [
            'entry' => [
                'id' => $entry->id,
                'entry_no' => $entry->entry_no,
                'entry_date' => $entry->entry_date?->format('Y-m-d'),
                'description' => $entry->description,
                'status' => $entry->status,
                'fiscal_year' => $entry->fiscalYear?->name,
                'fiscal_year_id' => $entry->fiscal_year_id,
                'total_debit' => $entry->total_debit,
                'total_credit' => $entry->total_credit,
                'lines' => $entry->lines->map(fn($line) => [
                    'id' => $line->id,
                    'account_id' => $line->account_id,
                    'account_code' => $line->account?->code,
                    'account_name' => $line->account?->name,
                    'description' => $line->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ]),
            ],
        ]);
    }

    public function edit(JournalEntry $entry): Response
    {
        // Allow editing even if posted, but we will show a warning in the UI
        $accounts = Account::where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name']);

        $fiscalYears = FiscalYear::orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date']);

        $contacts = Contact::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $entry->load(['fiscalYear', 'lines.account']);

        return Inertia::render('JournalEntries/Edit', [
            'entry' => [
                'id' => $entry->id,
                'entry_no' => $entry->entry_no,
                'entry_date' => $entry->entry_date?->format('Y-m-d'),
                'description' => $entry->description,
                'status' => $entry->status,
                'fiscal_year_id' => $entry->fiscal_year_id,
                'total_debit' => $entry->total_debit,
                'total_credit' => $entry->total_credit,
                'lines' => $entry->lines->map(fn($line) => [
                    'id' => $line->id,
                    'account_id' => $line->account_id,
                    'account_code' => $line->account?->code,
                    'account_name' => $line->account?->name,
                    'description' => $line->description,
                    'debit' => $line->debit,
                    'credit' => $line->credit,
                ]),
            ],
            'accounts' => $accounts,
            'fiscalYears' => $fiscalYears,
            'contacts' => $contacts,
        ]);
    }

    public function update(Request $request, JournalEntry $entry): RedirectResponse
    {
        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.contact_id' => ['nullable', 'exists:contacts,id'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalDebit = collect($validated['lines'])->sum(fn ($l) => (float) ($l['debit'] ?? 0));
        $totalCredit = collect($validated['lines'])->sum(fn ($l) => (float) ($l['credit'] ?? 0));

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with('error', 'يجب أن يتساوى مجموع المدين والدائن');
        }

        try {
            DB::beginTransaction();

            $entry->update([
                'entry_date' => $validated['entry_date'],
                'description' => $validated['description'],
                'fiscal_year_id' => $validated['fiscal_year_id'],
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            $entry->lines()->delete();

            foreach ($validated['lines'] as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'contact_id' => $line['contact_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'debit' => (float) ($line['debit'] ?? 0),
                    'credit' => (float) ($line['credit'] ?? 0),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('journal.entries.show', $entry->id)
                ->with('success', 'تم تحديث القيد بنجاح');
        } catch (Throwable $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(JournalEntry $entry): RedirectResponse
    {
        try {
            $entry->lines()->delete();
            $entry->delete();

            return redirect()
                ->route('journal.entries.index')
                ->with('success', 'تم حذف القيد بنجاح');
        } catch (Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function post(JournalEntry $entry): RedirectResponse
    {
        if ($entry->status === 'posted') {
            return back()->with('error', 'القيد معتمد بالفعل');
        }

        $totalDebit = $entry->lines()->sum('debit');
        $totalCredit = $entry->lines()->sum('credit');

        if (abs($totalDebit - $totalCredit) > 0.01) {
            return back()->with('error', 'يجب أن يتساوى مجموع المدين والدائن');
        }

        $entry->update([
            'status' => 'posted',
            'posted_by' => auth()->id(),
            'posted_at' => now(),
        ]);

        return back()->with('success', 'تم اعتماد القيد بنجاح');
    }

    public function unpost(JournalEntry $entry): RedirectResponse
    {
        if ($entry->status !== 'posted') {
            return back()->with('error', 'القيد غير معتمد بالفعل');
        }

        $entry->update([
            'status' => 'draft',
            'posted_by' => null,
            'posted_at' => null,
        ]);

        return back()->with('success', 'تم إلغاء اعتماد القيد بنجاح، يمكنك تعديله الآن');
    }

    public function exportExcel(Request $request)
    {
        $startDate = $request->input('start_date', date('Y-01-01'));
        $endDate = $request->input('end_date', date('Y-12-31'));

        $entries = JournalEntry::with(['fiscalYear', 'lines.account'])
            ->whereBetween('entry_date', [$startDate, $endDate])
            ->orderBy('entry_date')
            ->get();

        return Excel::download(new JournalEntriesExport($entries), 'journal_entries_' . date('Y-m-d') . '.xlsx');
    }
}