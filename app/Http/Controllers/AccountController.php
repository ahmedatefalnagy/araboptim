<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAccountRequest;
use App\Models\Account;
use App\Models\AccountType;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AccountController extends Controller
{
    public function index(): Response
    {
        $accounts = Account::with(['parent', 'type'])
            ->orderBy('code')
            ->get()
            ->map(function (Account $account) {
                return [
                    'id' => $account->id,
                    'parent_id' => $account->parent_id,
                    'parent_name' => $account->parent?->name,
                    'code' => $account->code,
                    'name' => $account->name,
                    'level' => $account->level,
                    'is_postable' => $account->is_postable,
                    'is_active' => $account->is_active,
                    'report_group' => $account->report_group,
                    'depreciation_rate' => $account->depreciation_rate,
                    'account_type' => [
                        'id' => $account->type?->id,
                        'name' => $account->type?->name,
                        'code' => $account->type?->code,
                    ],
                ];
            });

        $accountTypes = AccountType::orderBy('id')->get(['id', 'name', 'code']);
        $parents = Account::orderBy('code')->get(['id', 'code', 'name', 'level', 'account_type_id', 'report_group']);

        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
            'accountTypes' => $accountTypes,
            'parents' => $parents,
        ]);
    }

    public function store(StoreAccountRequest $request): RedirectResponse
    {
        $parentId = $request->input('parent_id');
        $parent = Account::findOrFail($parentId); // Always required for new accounts via Request
        
        $code = $request->input('code');
        if (empty($code)) {
            $code = $this->generateNextCode($parent);
        }

        Account::create([
            'parent_id' => $parentId,
            'code' => $code,
            'name' => $request->name,
            'account_type_id' => $parent->account_type_id, // Inherit type from parent
            'level' => $parent->level + 1,
            'is_postable' => (bool) $request->is_postable,
            'is_active' => (bool) $request->is_active,
            'report_group' => $request->report_group ?? $parent->report_group,
            'depreciation_rate' => $request->depreciation_rate,
        ]);

        return redirect()->route('accounts.index')->with('success', 'تمت إضافة الحساب بنجاح برقم: ' . $code);
    }

    public function update(StoreAccountRequest $request, Account $account): RedirectResponse
    {
        $parentId = $request->input('parent_id');
        $parent = Account::find($parentId);

        $account->update([
            'parent_id' => $parentId,
            'code' => $request->code ?? $account->code,
            'name' => $request->name,
            'account_type_id' => $request->account_type_id,
            'level' => $parent ? $parent->level + 1 : 1,
            'is_postable' => (bool) $request->is_postable,
            'is_active' => (bool) $request->is_active,
            'report_group' => $request->report_group,
            'depreciation_rate' => $request->depreciation_rate,
        ]);

        return redirect()->route('accounts.index')->with('success', 'تم تعديل الحساب بنجاح');
    }

    public function destroy(Account $account, \Illuminate\Http\Request $request): RedirectResponse
    {
        // Check if account has children
        if ($account->children()->count() > 0) {
            if ($request->input('force') !== 'true') {
                return back()->with('error', 'لا يمكن حذف حساب يحتوي على حسابات فرعية. استخدم الحذف القسري إذا كنت متأكداً.');
            }
            // If force, recursive delete could be complex, let's just block it for now if it has children
            // or we delete children too.
            $account->children()->delete();
        }

        // Check if account has transactions
        if ($account->journalEntryLines()->count() > 0) {
            if ($request->input('force') !== 'true') {
                return back()->with('error', 'هذا الحساب مرتبط بعمليات محاسبية. هل تريد حذفه وحذف جميع العمليات المرتبطة به؟');
            }
            // Force delete transactions (DANGEROUS)
            $account->journalEntryLines()->delete();
        }

        $account->delete();
        return redirect()->route('accounts.index')->with('success', 'تم حذف الحساب بنجاح');
    }

    private function generateNextCode(Account $parent)
    {
        $lastChild = Account::where('parent_id', $parent->id)
            ->orderBy('code', 'desc')
            ->first();

        if (!$lastChild) {
            // First child logic: add '01' (or '1' for level 1 root accounts)
            // But let's use 2 digits for everything to be safe and consistent.
            // If parent is '1', first child is '11'. If parent is '11', first child is '1101'.
            if (strlen($parent->code) == 1) {
                return $parent->code . "1"; // 1 -> 11
            }
            return $parent->code . "01"; // 11 -> 1101, 1101 -> 110101
        }

        $parentCode = $parent->code;
        $lastCode = $lastChild->code;
        
        // Extract the suffix (the part after the parent code)
        $suffix = substr($lastCode, strlen($parentCode));
        $suffixLength = strlen($suffix);
        
        // Increment the suffix
        $nextSuffixInt = (int)$suffix + 1;
        $nextSuffix = str_pad($nextSuffixInt, $suffixLength, '0', STR_PAD_LEFT);
        
        $newCode = $parentCode . $nextSuffix;

        // Safety check to avoid existing codes
        while (Account::where('code', $newCode)->exists()) {
            $nextSuffixInt++;
            $nextSuffix = str_pad($nextSuffixInt, $suffixLength, '0', STR_PAD_LEFT);
            $newCode = $parentCode . $nextSuffix;
        }

        return $newCode;
    }

    public function getNextCode(\Illuminate\Http\Request $request)
    {
        $parentId = $request->input('parent_id');
        if (!$parentId) return response()->json(['code' => '']);
        
        $parent = Account::find($parentId);
        if (!$parent) return response()->json(['code' => '']);
        
        return response()->json(['code' => $this->generateNextCode($parent)]);
    }
}