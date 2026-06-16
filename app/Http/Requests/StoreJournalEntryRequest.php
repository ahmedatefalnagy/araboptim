<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\FiscalYear;

class StoreJournalEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'entry_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'fiscal_year_id' => ['required', 'exists:fiscal_years,id'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.contact_id' => ['nullable', 'exists:contacts,id'],
            'lines.*.description' => ['nullable', 'string'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Validate that the entry date is within the selected fiscal year
            $fiscalYearId = $this->input('fiscal_year_id');
            $entryDate = $this->input('entry_date');
            
            if ($fiscalYearId && $entryDate) {
                $fiscalYear = FiscalYear::find($fiscalYearId);
                if ($fiscalYear && ($entryDate < $fiscalYear->start_date->format('Y-m-d') || $entryDate > $fiscalYear->end_date->format('Y-m-d'))) {
                    $validator->errors()->add('entry_date', "يجب أن يكون تاريخ القيد ضمن السنة المالية المحددة ({$fiscalYear->start_date->format('Y-m-d')} إلى {$fiscalYear->end_date->format('Y-m-d')})");
                }
            }

            $lines = $this->input('lines', []);

            $totalDebit = collect($lines)->sum(fn ($line) => (float) ($line['debit'] ?? 0));
            $totalCredit = collect($lines)->sum(fn ($line) => (float) ($line['credit'] ?? 0));

            foreach ($lines as $index => $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    $validator->errors()->add("lines.$index", 'لا يجوز أن يحتوي نفس السطر على مدين ودائن معًا');
                }

                if ($debit == 0 && $credit == 0) {
                    $validator->errors()->add("lines.$index", 'يجب إدخال مبلغ مدين أو دائن');
                }
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                $validator->errors()->add('lines', 'إجمالي المدين يجب أن يساوي إجمالي الدائن');
            }
        });
    }
}