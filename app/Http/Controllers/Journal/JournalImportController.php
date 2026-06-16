<?php

namespace App\Http\Controllers\Journal;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Inertia\Inertia;

class JournalImportController extends Controller
{
    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        $file = $request->file('file');
        $data = Excel::toArray([], $file)[0];
        array_shift($data); // Skip header

        $previewEntries = [];
        $lastGroupKey = null;
        
        foreach ($data as $row) {
            if (empty($row[0]) && empty($row[3])) continue;

            $dateValue = $row[0];
            $date = is_numeric($dateValue) 
                ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateValue)->format('Y-m-d')
                : trim((string)$dateValue);
            
            $ref = !empty($row[1]) ? trim((string)$row[1]) : null;
            $entryDesc = trim($row[2] ?? '');
            
            // Smarter Grouping:
            // 1. If Ref is provided, it's the primary key.
            // 2. If Ref is missing, check if Date and Desc match the last group.
            // 3. If Ref, Date, and Desc are all missing but Account Code exists, it belongs to the last group.
            
            $isSameAsLast = false;
            if ($lastGroupKey && isset($previewEntries[$lastGroupKey])) {
                $lastEntry = $previewEntries[$lastGroupKey];
                
                if ($ref && $lastEntry['reference'] === $ref) {
                    $isSameAsLast = true;
                } elseif (!$ref && !$date && !$entryDesc) {
                    // Contiguous row with missing header info - inherit
                    $isSameAsLast = true;
                    $date = $lastEntry['entry_date'];
                    $entryDesc = $lastEntry['description'];
                } elseif (!$ref && $lastEntry['entry_date'] === $date && $lastEntry['description'] === $entryDesc) {
                    $isSameAsLast = true;
                }
            }

            if ($isSameAsLast) {
                $groupKey = $lastGroupKey;
            } else {
                $groupKey = ($ref ?: 'Excel-' . time()) . '-' . uniqid();
                if (!$entryDesc) $entryDesc = 'قيد مستورد';
            }
            
            $lastGroupKey = $groupKey;

            $accountCode = trim($row[3] ?? '');
            $debit = (float)($row[4] ?? 0);
            $credit = (float)($row[5] ?? 0);
            $lineDesc = $row[6] ?? $entryDesc;
            $contactName = trim($row[7] ?? '');

            // Try finding by code, then by name as fallback
            $account = Account::where('code', $accountCode)->first();
            if (!$account && $accountCode) {
                $account = Account::where('name', $accountCode)->first();
            }

            // Try finding contact if provided
            $contact = null;
            if ($contactName) {
                $contact = Contact::where('name', $contactName)->first();
            }

            // Fallback: If account not found but contact exists, use contact's linked account
            if (!$account && $contact) {
                $account = $contact->receivableAccount ?? $contact->payableAccount ?? $contact->account;
            }

            if (!isset($previewEntries[$groupKey])) {
                $previewEntries[$groupKey] = [
                    'entry_date' => $date,
                    'description' => $entryDesc,
                    'reference' => $ref ?: $groupKey,
                    'lines' => [],
                    'total_debit' => 0,
                    'total_credit' => 0,
                    'is_balanced' => true,
                    'has_errors' => false,
                ];
            }

            $previewEntries[$groupKey]['lines'][] = [
                'account_code' => $accountCode,
                'account_name' => $account ? $account->name : 'غير موجود!',
                'account_id' => $account ? $account->id : null,
                'contact_id' => $contact ? $contact->id : null,
                'contact_name' => $contact ? $contact->name : ($contactName ? "اتصال ($contactName) غير موجود" : null),
                'description' => $lineDesc,
                'debit' => $debit,
                'credit' => $credit,
                'error' => !$account ? "الحساب ($accountCode) غير موجود (كود أو اسم)" : null,
            ];

            $previewEntries[$groupKey]['total_debit'] += $debit;
            $previewEntries[$groupKey]['total_credit'] += $credit;
            if (!$account) $previewEntries[$groupKey]['has_errors'] = true;
        }

        foreach ($previewEntries as &$entry) {
            $entry['is_balanced'] = abs($entry['total_debit'] - $entry['total_credit']) < 0.01;
            if (!$entry['is_balanced']) $entry['has_errors'] = true;
        }

        // Store in session and redirect to GET route
        session(['journal_import_data' => array_values($previewEntries)]);
        
        return redirect()->route('journal.entries.import.review');
    }

    public function showReview()
    {
        $previewData = session('journal_import_data');
        if (!$previewData) {
            return redirect()->route('journal.entries.index')->with('error', 'انتهت جلسة الاستيراد أو لا توجد بيانات');
        }

        return Inertia::render('JournalEntries/ImportPreview', [
            'previewData' => $previewData,
            'accounts' => Account::where('is_postable', true)->where('is_active', true)->get(['id', 'code', 'name']),
            'contacts' => Contact::where('is_active', true)->get(['id', 'name']),
        ]);
    }

    public function confirm(Request $request)
    {
        $entriesData = $request->input('entries');
        $fiscalYear = FiscalYear::where('status', 'active')->first();

        DB::beginTransaction();
        try {
            foreach ($entriesData as $entryData) {
                $entry = JournalEntry::create([
                    'entry_no' => $this->getNextEntryNo(),
                    'entry_date' => $entryData['entry_date'],
                    'description' => $entryData['description'],
                    'fiscal_year_id' => $fiscalYear->id,
                    'status' => 'draft',
                    'created_by' => auth()->id(),
                ]);

                foreach ($entryData['lines'] as $index => $line) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $entry->id,
                        'account_id' => $line['account_id'],
                        'contact_id' => $line['contact_id'] ?? null,
                        'description' => $line['description'],
                        'debit' => $line['debit'],
                        'credit' => $line['credit'],
                        'line_order' => $index + 1,
                    ]);
                }
            }

            session()->forget('journal_import_data');
            DB::commit();
            return redirect()->route('journal.entries.index')->with('success', 'تم استيراد القيود المحددة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'فشل الحفظ: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $accounts = Account::where('is_postable', true)
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['code', 'name']);
        return Excel::download(new class implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
            public function sheets(): array {
                return [
                    // Sheet 1: The Template
                    new class implements \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\FromCollection {
                        public function headings(): array {
                            return ['Date (YYYY-MM-DD)', 'Reference', 'Entry Description', 'Account Code/Name', 'Debit', 'Credit', 'Line Description', 'Contact Name'];
                        }
                        public function title(): string { return 'Data Entry Template'; }
                        public function collection() { return collect([]); }
                    },
                    // Sheet 2: Accounts List
                    new class implements \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\FromCollection {
                        public function headings(): array { return ['Account Code', 'Account Name']; }
                        public function title(): string { return 'Accounts Reference'; }
                        public function collection() { 
                            return Account::where('is_postable', true)->where('is_active', true)->orderBy('code')->get(['code', 'name']);
                        }
                    },
                    // Sheet 3: Contacts List
                    new class implements \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\FromCollection {
                        public function headings(): array { return ['Contact Name', 'Type']; }
                        public function title(): string { return 'Contacts Reference'; }
                        public function collection() { 
                            return Contact::where('is_active', true)->orderBy('name')->get(['name', 'type']);
                        }
                    }
                ];
            }
        }, 'journal_import_template.xlsx');
    }

    private function getNextEntryNo()
    {
        $last = JournalEntry::orderBy('id', 'desc')->first();
        return $last ? $last->entry_no + 1 : 1;
    }
}
