<?php

namespace App\Services;

use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalEntryService
{
    public function create(array $data): JournalEntry
    {
        return DB::transaction(function () use ($data) {
            $totalDebit = 0;
            $totalCredit = 0;
            $linesCount = count($data['lines'] ?? []);

            if ($linesCount < 2) {
                throw new \RuntimeException('يجب أن يحتوي القيد على سطرين على الأقل');
            }

            foreach ($data['lines'] as $line) {
                $debit = (float) ($line['debit'] ?? 0);
                $credit = (float) ($line['credit'] ?? 0);

                if ($debit > 0 && $credit > 0) {
                    throw new \RuntimeException('لا يمكن إدخال مدين ودائن معاً في نفس السطر');
                }

                $totalDebit += $debit;
                $totalCredit += $credit;
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \RuntimeException('القيد غير متزن: مجموع المدين يجب أن يساوي مجموع الدائن');
            }

            $status = $data['status'] ?? 'draft';

            $entryData = [
                'entry_no' => $this->generateEntryNumber(),
                'entry_date' => $data['entry_date'],
                'description' => $data['description'] ?? null,
                'fiscal_year_id' => $data['fiscal_year_id'],
                'transaction_type' => $data['transaction_type'] ?? null,
                'reference_id' => $data['reference_id'] ?? null,
                'status' => $status,
                'created_by' => Auth::id(),
            ];

            if ($status === 'posted') {
                $entryData['posted_by'] = Auth::id();
                $entryData['posted_at'] = now();
            }

            $entry = JournalEntry::create($entryData);

            foreach ($data['lines'] as $index => $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'contact_id' => $line['contact_id'] ?? null,
                    'description' => $line['description'] ?? null,
                    'debit' => $line['debit'] ?? 0,
                    'credit' => $line['credit'] ?? 0,
                    'line_order' => $index + 1,
                ]);
            }

            return $entry->load(['lines.account', 'fiscalYear']);
        });
    }

    public function post(JournalEntry $entry): JournalEntry
    {
        return DB::transaction(function () use ($entry) {
            $entry->load('lines');

            if ($entry->status === 'posted') {
                return $entry->fresh(['lines.account', 'fiscalYear']);
            }

            $totalDebit = (float) $entry->lines->sum('debit');
            $totalCredit = (float) $entry->lines->sum('credit');

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new \RuntimeException('القيد غير متزن');
            }

            if ($entry->lines->count() < 2) {
                throw new \RuntimeException('يجب أن يحتوي القيد على سطرين على الأقل');
            }

            $entry->update([
                'status' => 'posted',
                'posted_by' => Auth::id(),
                'posted_at' => now(),
            ]);

            return $entry->fresh(['lines.account', 'fiscalYear']);
        });
    }

    protected function generateEntryNumber(): string
    {
        $lastEntry = JournalEntry::latest('id')->first();

        if (!$lastEntry) {
            return 'JV-000001';
        }

        $lastNumber = 0;

        if (preg_match('/(\d+)$/', $lastEntry->entry_no, $matches)) {
            $lastNumber = (int) $matches[1];
        }

        $nextNumber = $lastNumber + 1;

        return 'JV-' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }
}