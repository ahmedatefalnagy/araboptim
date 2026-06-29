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
            ->when($type === 'employee', fn($q) => $q->where('type', 'employee'))
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

        // Link directly to general accounts (like Odoo) to prevent inflating chart of accounts
        $generalCodes = [
            'customer' => '1103',
            'supplier' => '2101',
            'employee' => '1106',
            'partner'  => '2102',
        ];

        if (!$contact->receivable_account_id && ($contact->is_customer || $contact->is_related_party || $contact->type === 'employee')) {
            $typeKey = $contact->type === 'employee' ? 'employee' : ($contact->is_related_party ? 'partner' : 'customer');
            $code = $generalCodes[$typeKey];
            $acc = Account::where('code', $code)->first();
            if ($acc) {
                if (!$acc->is_postable) {
                    $acc->update(['is_postable' => true]);
                }
                $contact->update(['receivable_account_id' => $acc->id]);
            }
        }

        if (!$contact->payable_account_id && $contact->is_supplier) {
            $code = $generalCodes['supplier'];
            $acc = Account::where('code', $code)->first();
            if ($acc) {
                if (!$acc->is_postable) {
                    $acc->update(['is_postable' => true]);
                }
                $contact->update(['payable_account_id' => $acc->id]);
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
}

