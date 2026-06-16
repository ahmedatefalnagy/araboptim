<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class FixedAssetController extends Controller
{
    public function index(Request $request)
    {
        $yearStart = Carbon::now()->startOfYear()->format('Y-m-d');
        
        // جلب حسابات الأصول الثابتة (تبدأ بـ 12 ولا تبدأ بـ 124)
        $assetAccounts = Account::where('code', 'like', '12%')
            ->where('code', 'not like', '124%')
            ->where('is_postable', true)
            ->get(['id', 'code', 'name', 'depreciation_rate']);

        $accDepAccounts = Account::where('code', 'like', '124%')
            ->where('is_postable', true)
            ->get(['id', 'code', 'name']);

        $results = [];

        foreach ($assetAccounts as $asset) {
            // رصيد الأصل
            $openingAsset = DB::table('journal_entry_lines as jel')
                ->join('journal_entries as je', 'je.id', '=', 'jel.journal_entry_id')
                ->where('je.status', 'posted')
                ->where('jel.account_id', $asset->id)
                ->sum(DB::raw('jel.debit - jel.credit'));

            // محاولة إيجاد مجمع الإهلاك الخاص به بناءً على الاسم المتقارب
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
                    ->where('jel.account_id', $accDep->id)
                    ->sum(DB::raw('jel.debit - jel.credit')));
            }

            // صافي القيمة الدفترية
            $nbvOpening = $openingAsset - $openingAccDep;

            // تحديد النسبة
            $rate = 0;
            $assetName = $asset->name;
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

            // قسط الإهلاك المقترح
            $suggestedDepreciation = $nbvOpening > 0 ? ($nbvOpening * ($rate / 100)) : 0;

            $results[] = [
                'id' => $asset->id,
                'code' => $asset->code,
                'name' => $asset->name,
                'opening_asset' => (float)$openingAsset,
                'opening_acc_dep' => (float)$openingAccDep,
                'nbv_opening' => (float)$nbvOpening,
                'rate' => $rate,
                'suggested_depreciation' => (float)$suggestedDepreciation,
            ];
        }

        // بيانات للـ Dropdowns في الواجهة
        $paymentAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '11%')->orWhere('code', 'like', '21%');
            })->get(['id', 'code', 'name']);

        $expenseAccounts = Account::where('code', 'like', '5%')
            ->where('is_postable', true)->get(['id', 'code', 'name']);

        $parentAssetAccounts = Account::where('code', 'like', '12%')
            ->where('code', 'not like', '124%')
            ->where('is_postable', false)->get(['id', 'code', 'name', 'account_type_id', 'level', 'report_group']);

        return Inertia::render('FixedAssets/Index', [
            'assets' => $results,
            'paymentAccounts' => $paymentAccounts,
            'accDepAccounts' => $accDepAccounts,
            'expenseAccounts' => $expenseAccounts,
            'parentAssetAccounts' => $parentAssetAccounts,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'parent_account_id' => 'required|exists:accounts,id',
            'name' => 'required|string|max:255',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
            'purchase_date' => 'required|date',
            'purchase_amount' => 'required|numeric|min:0.01',
            'payment_account_id' => 'required|exists:accounts,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. إنشاء حساب الأصل
            $parent = Account::findOrFail($request->parent_account_id);
            $code = $this->generateNextCode($parent);

            $assetAccount = Account::create([
                'parent_id' => $parent->id,
                'code' => $code,
                'name' => $request->name,
                'account_type_id' => $parent->account_type_id,
                'level' => $parent->level + 1,
                'is_postable' => true,
                'is_active' => true,
                'report_group' => $parent->report_group,
                'depreciation_rate' => $request->depreciation_rate,
            ]);

            // 2. إنشاء القيد المحاسبي لشراء الأصل
            $je = JournalEntry::create([
                'entry_no' => 'FA-' . time(),
                'entry_date' => $request->purchase_date,
                'description' => 'شراء أصل ثابت: ' . $request->name,
                'status' => 'posted',
            ]);

            // المدين: حساب الأصل
            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $assetAccount->id,
                'description' => 'إثبات شراء الأصل الثابت',
                'debit' => $request->purchase_amount,
                'credit' => 0,
            ]);

            // الدائن: حساب الدفع (بنك/صندوق/مورد)
            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $request->payment_account_id,
                'description' => 'سداد قيمة شراء الأصل الثابت',
                'debit' => 0,
                'credit' => $request->purchase_amount,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'تم إضافة الأصل وإثبات قيد الشراء بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    public function update(Request $request, Account $account)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'depreciation_rate' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $account->update([
                'name' => $request->name,
                'depreciation_rate' => $request->depreciation_rate,
            ]);

            return redirect()->back()->with('success', 'تم تعديل بيانات الأصل (النسبة والاسم) بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء التعديل: ' . $e->getMessage());
        }
    }

    public function depreciate(Request $request)
    {
        $request->validate([
            'asset_account_id' => 'required|exists:accounts,id',
            'expense_account_id' => 'required|exists:accounts,id',
            'acc_dep_account_id' => 'required|exists:accounts,id',
            'depreciation_amount' => 'required|numeric|min:0.01',
            'entry_date' => 'required|date',
            'description' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $je = JournalEntry::create([
                'entry_no' => 'DEP-' . time(),
                'entry_date' => $request->entry_date,
                'description' => $request->description,
                'status' => 'posted',
            ]);

            // المدين: حساب مصروف الإهلاك
            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $request->expense_account_id,
                'description' => $request->description,
                'debit' => $request->depreciation_amount,
                'credit' => 0,
            ]);

            // الدائن: حساب مجمع الإهلاك
            JournalEntryLine::create([
                'journal_entry_id' => $je->id,
                'account_id' => $request->acc_dep_account_id,
                'description' => $request->description,
                'debit' => 0,
                'credit' => $request->depreciation_amount,
            ]);

            DB::commit();
            return redirect()->back()->with('success', 'تم إثبات قيد الإهلاك بنجاح.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ: ' . $e->getMessage());
        }
    }

    private function generateNextCode(Account $parent)
    {
        $lastChild = Account::where('parent_id', $parent->id)->orderBy('code', 'desc')->first();
        if (!$lastChild) return $parent->code . "01";
        $suffixLength = strlen($lastChild->code) - strlen($parent->code);
        $nextSuffixInt = (int)substr($lastChild->code, strlen($parent->code)) + 1;
        $newCode = $parent->code . str_pad($nextSuffixInt, $suffixLength, '0', STR_PAD_LEFT);
        while (Account::where('code', $newCode)->exists()) {
            $nextSuffixInt++;
            $newCode = $parent->code . str_pad($nextSuffixInt, $suffixLength, '0', STR_PAD_LEFT);
        }
        return $newCode;
    }
}
