<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'all'); // all, customer, supplier, partner
        $search = $request->query('search');

        $contacts = Contact::with(['account', 'receivableAccount', 'payableAccount', 'mainCompany'])
            ->when($type === 'customer', fn($q) => $q->where('is_customer', true))
            ->when($type === 'supplier', fn($q) => $q->where('is_supplier', true))
            ->when($type === 'partner', fn($q) => $q->where('is_related_party', true))
            ->when($search, function($q) use ($search) {
                $q->where(function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                       ->orWhere('phone', 'like', "%{$search}%")
                       ->orWhere('email', 'like', "%{$search}%")
                       ->orWhere('tax_number', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();
            
        return Inertia::render('Contacts/Index', [
            'type' => $type,
            'search' => $search,
            'contacts' => $contacts
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'customer');
        $accounts = Account::where('is_postable', true)->orderBy('code')->get(['id', 'code', 'name']);
        $mainCompanies = Contact::where('is_main_company', true)->orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Contacts/Create', [
            'type' => $type,
            'accounts' => $accounts,
            'mainCompanies' => $mainCompanies,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:customer,supplier,employee,partner',
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'is_related_party' => 'boolean',
            'is_main_company' => 'boolean',
            'is_sub_client' => 'boolean',
            'main_company_id' => 'nullable|exists:contacts,id',
            'name' => 'required|string|max:255|unique:contacts,name',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'receivable_account_id' => 'nullable|exists:accounts,id',
            'payable_account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        // Default role based on creation context if not explicitly sent
        if (!isset($validated['is_customer']) && !isset($validated['is_supplier'])) {
            $validated['is_customer'] = $validated['type'] === 'customer';
            $validated['is_supplier'] = $validated['type'] === 'supplier';
            $validated['is_related_party'] = $validated['type'] === 'partner';
        }

        // If it's not a sub client, make sure main_company_id is null
        if (empty($validated['is_sub_client'])) {
            $validated['main_company_id'] = null;
        }

        $contact = Contact::create($validated);

        // Auto-generate account if none provided
        if (!$contact->receivable_account_id && ($contact->is_customer || $contact->is_related_party || $contact->type === 'employee')) {
            $newAccountId = $this->createAccountForContact($contact);
            if ($newAccountId) {
                $contact->update(['receivable_account_id' => $newAccountId]);
            }
        }

        if (!$contact->payable_account_id && $contact->is_supplier) {
            $newAccountId = $this->createAccountForContact($contact);
            if ($newAccountId) {
                $contact->update(['payable_account_id' => $newAccountId]);
            }
        }

        return redirect()->route('contacts.index', ['type' => $contact->type])
            ->with('success', 'تم إضافة جهة الاتصال وتحديد حساباتها المحاسبية بنجاح!');
    }

    public function edit(Contact $contact)
    {
        $accounts = Account::where('is_postable', true)->orderBy('code')->get(['id', 'code', 'name']);
        $mainCompanies = Contact::where('is_main_company', true)->where('id', '!=', $contact->id)->orderBy('name')->get(['id', 'name']);
        
        return Inertia::render('Contacts/Edit', [
            'contact' => $contact,
            'accounts' => $accounts,
            'mainCompanies' => $mainCompanies,
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        $validated = $request->validate([
            'is_customer' => 'boolean',
            'is_supplier' => 'boolean',
            'is_related_party' => 'boolean',
            'is_main_company' => 'boolean',
            'is_sub_client' => 'boolean',
            'main_company_id' => 'nullable|exists:contacts,id',
            'name' => 'required|string|max:255|unique:contacts,name,' . $contact->id,
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'receivable_account_id' => 'nullable|exists:accounts,id',
            'payable_account_id' => 'nullable|exists:accounts,id',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        // If it's not a sub client, make sure main_company_id is null
        if (empty($validated['is_sub_client'])) {
            $validated['main_company_id'] = null;
        }

        $contact->update($validated);

        return redirect()->route('contacts.index', ['type' => $contact->type])
            ->with('success', 'تم تعديل البيانات بنجاح!');
    }

    public function destroy(Contact $contact)
    {
        $type = $contact->type;
        // Cannot delete if there are invoices/vouchers (foreign key constraint will catch this)
        try {
            $contact->delete();
            return redirect()->route('contacts.index', ['type' => $type])
                ->with('success', 'تم مسح جهة الاتصال بنجاح.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => 'لا يمكن مسح جهة الاتصال لأن لها حركات مالية مرتبطة.']);
        }
    }

    /**
     * Auto generates an Account in the Chart of Accounts under the right directory
     */
    private function createAccountForContact(Contact $contact)
    {
        // Define root logic based on Chart of Accounts
        $typeData = [
            'customer' => ['name' => 'العملاء (ذمم مدينة)', 'code' => '1103', 'type' => 'asset', 'group' => 'customers'],
            'supplier' => ['name' => 'الموردون', 'code' => '2101', 'type' => 'liability', 'group' => 'suppliers'],
            'employee' => ['name' => 'عهد وسلف الموظفين', 'code' => '1106', 'type' => 'asset', 'group' => 'employees'],
            'partner'  => ['name' => 'دائنون متنوعون', 'code' => '2102', 'type' => 'liability', 'group' => 'partners'],
        ];

        $groupInfo = $typeData[$contact->type ?? 'customer'] ?? $typeData['customer'];
        
        // 1. Find or create the Account Type
        $accType = AccountType::where('code', $groupInfo['type'])->first();
        $accTypeId = $accType ? $accType->id : 1; // Fallback to 1 if not found

        // 2. Find or create the root Account "e.g. 1103 العملاء"
        $parentAccount = Account::firstOrCreate([
            'code' => $groupInfo['code']
        ], [
            'name' => $groupInfo['name'],
            'account_type_id' => $accTypeId,
            'level' => 3,
            'is_postable' => false, // It's a folder container
            'is_active' => true,
        ]);

        // Ensure parent is not postable so we can add children
        if ($parentAccount->is_postable) {
            $parentAccount->update(['is_postable' => false]);
        }

        // 3. Create the actual postable account for this specific customer
        // Find the max code under this parent
        $latestChild = Account::where('parent_id', $parentAccount->id)
            ->where('code', 'like', $groupInfo['code'] . '-%')
            ->orderBy('id', 'desc')
            ->first();

        $newCode = $groupInfo['code'] . '-001';
        if ($latestChild) {
            $parts = explode('-', $latestChild->code);
            if (count($parts) > 1) {
                $num = (int)end($parts);
                $newCode = $groupInfo['code'] . '-' . str_pad($num + 1, 3, '0', STR_PAD_LEFT);
            }
        }

        $newAccount = Account::create([
            'code' => $newCode,
            'name' => $contact->name,
            'parent_id' => $parentAccount->id,
            'account_type_id' => $accType->id,
            'level' => $parentAccount->level + 1,
            'is_postable' => true, // It is postable!
            'is_active' => true,
        ]);

        return $newAccount->id;
    }
}
