<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripEvent;
use App\Models\Vehicle;
use App\Models\Employee;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Account;
use App\Models\FiscalYear;
use App\Services\ZatcaService;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class TripController extends Controller
{
    public function index()
    {
        $trips = Trip::with(['vehicle', 'driver', 'broker', 'events'])->latest()->get();
        return Inertia::render('Logistics/Trips/Index', [
            'trips' => $trips
        ]);
    }

    public function create()
    {
        return Inertia::render('Logistics/Trips/Create', [
            'vehicles' => Vehicle::where('status', 'available')->get(['id', 'plate_no', 'driver_id']),
            'drivers' => Employee::where('is_driver', true)->get(['id', 'name', 'employee_no']),
            'brokers' => Contact::where('type', 'customer')->select('id','name','is_main_company','is_sub_client','main_company_id')->get(),
            'suppliers' => Contact::where('is_supplier', true)->orWhere('type', 'supplier')->get(['id', 'name']),
            'routes' => \App\Models\TripRoute::where('is_active', true)->get(),
        ]);
    }

    public function quickStoreSupplier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contacts,name',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
        ]);

        $contact = Contact::create([
            'type' => 'supplier',
            'is_supplier' => true,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
        ]);

        // Auto-assign payable account
        $acc = Account::where('code', '1103')->first();
        if ($acc) {
            $contact->update(['payable_account_id' => $acc->id]);
        }

        return response()->json(['id' => $contact->id, 'name' => $contact->name]);
    }

    public function quickStoreSubClient(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contacts,name',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'main_company_id' => 'nullable|exists:contacts,id',
        ]);

        $contact = Contact::create([
            'type' => 'customer',
            'is_sub_client' => true,
            'is_customer' => true,
            'main_company_id' => $validated['main_company_id'] ?? null,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
        ]);

        // Auto-assign receivable account
        $acc = Account::where('code', '1103')->first();
        if ($acc) {
            $contact->update(['receivable_account_id' => $acc->id]);
        }

        return response()->json(['id' => $contact->id, 'name' => $contact->name, 'main_company_id' => $contact->main_company_id]);
    }

    public function quickStoreMainCompany(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:contacts,name',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
        ]);

        $contact = Contact::create([
            'type' => 'customer',
            'is_main_company' => true,
            'is_customer' => true,
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'tax_number' => $validated['tax_number'] ?? null,
        ]);

        // Auto-assign receivable account
        $acc = Account::where('code', '1103')->first();
        if ($acc) {
            $contact->update(['receivable_account_id' => $acc->id]);
        }

        return response()->json(['id' => $contact->id, 'name' => $contact->name]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:employees,id',
            'route_id' => 'nullable|exists:trip_routes,id',
            'broker_id' => 'required|exists:contacts,id',
            'main_company_id' => 'nullable|exists:contacts,id',
            'sub_clients' => 'nullable|array',
            'sub_clients.*.contact_id' => 'required|exists:contacts,id',
            'sub_clients.*.price' => 'required|numeric|min:0',
            'end_customer_name' => 'nullable|string',
            'origin' => 'required|string',
            'destination' => 'required|string',
            'loading_site' => 'nullable|string',
            'discharge_site' => 'nullable|string',
            'waybill_no' => 'nullable|string',
            'cargo_type' => 'nullable|string',
            'weight' => 'nullable|numeric',
            'container_no' => 'nullable|string',
            'etd' => 'nullable', 
            'eta' => 'nullable',
            'eta_unloading' => 'nullable',
            'start_km' => 'nullable|numeric',
            'diesel_liters' => 'nullable|numeric',
            'total_trip_budget' => 'nullable|numeric|min:0',
            'initial_diesel_amount' => 'nullable|numeric|min:0',
            'driver_commission' => 'nullable|numeric|min:0',
            'broker_price' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $tripNo = 'TRIP-' . date('Ymd') . '-' . rand(100, 999);

            $trip = Trip::create(array_merge($validated, [
                'trip_no' => $tripNo,
                'status' => 'planned',
            ]));

            if (!empty($validated['initial_diesel_amount']) && $validated['initial_diesel_amount'] > 0) {
                $trip->diesels()->create([
                    'amount' => $validated['initial_diesel_amount'],
                    'notes' => 'ديزل أولي عند بدء الرحلة',
                ]);
            }

            // Sync sub‑clients if any
            if (!empty($validated['sub_clients'])) {
                $pivotData = [];
                foreach ($validated['sub_clients'] as $sc) {
                    $pivotData[$sc['contact_id']] = ['price' => $sc['price']];
                }
                $trip->subClients()->sync($pivotData);
            }

            // Update Vehicle Status
            Vehicle::find($validated['vehicle_id'])->update(['status' => 'in_trip']);

            DB::commit();
            return redirect()->route('logistics.trips.index')->with('success', 'تم بدء الرحلة وتحديث حالة الشاحنة.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    public function show(Trip $trip)
    {
        $trip->load(['vehicle', 'driver', 'broker', 'events', 'invoice', 'diesels']);
        return Inertia::render('Logistics/Trips/Show', [
            'trip' => $trip,
            'total_diesel' => $trip->total_diesel,
            'net_trip' => $trip->net_trip
        ]);
    }

    public function updateStatus(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'status' => 'required|in:planned,loading,transit,at_destination,completed,cancelled',
            'actual_arrival' => 'nullable|date',
            'actual_loading_start' => 'nullable|date',
            'actual_loading_end' => 'nullable|date',
            'actual_unloading_start' => 'nullable|date',
            'actual_unloading_end' => 'nullable|date',
            'end_km' => 'nullable|numeric',
            'fuel_amount' => 'nullable|numeric',
            'fuel_cost' => 'nullable|numeric',
            'diesel_liters' => 'nullable|numeric',
            'driver_commission' => 'nullable|numeric|min:0',
        ]);

        // Auto calculate commission if budget and fuel cost are present
        if ($validated['status'] === 'completed' || isset($validated['fuel_cost'])) {
            $budget = $validated['total_trip_budget'] ?? $trip->total_trip_budget;
            $fuel = $validated['fuel_cost'] ?? $trip->fuel_cost;
            
            if ($budget > 0 && $fuel > 0) {
                $validated['driver_commission'] = $budget - $fuel;
            }
        }

        $trip->update($validated);

        if ($validated['status'] === 'completed') {
            $trip->vehicle->update([
                'status' => 'available',
                'odometer' => $validated['end_km'] ?? $trip->vehicle->odometer
            ]);
        }

        return redirect()->back()->with('success', 'تم تحديث حالة الرحلة واحتساب عمولة السائق.');
    }

    public function addStop(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'reason' => 'required|string|in:rest,saher,breakdown,fuel,other',
            'location' => 'nullable|string',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $trip->stops()->create($validated);
        $trip->increment('stop_count');

        return redirect()->back()->with('success', 'تم تسجيل التوقف في مسار الرحلة.');
    }
    public function addDiesel(Request $request, Trip $trip)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $trip->diesels()->create($validated);

        return redirect()->back()->with('success', 'تمت إضافة كمية الديزل بنجاح وتحديث المستحقات.');
    }

    public function monthlyBilling(Request $request)
    {
        $brokerId = $request->query('broker_id');
        $vehicleId = $request->query('vehicle_id');
        $month = $request->query('month');
        $year = $request->query('year');

        $trips = [];
        if ($brokerId && $vehicleId && $month && $year) {
            $trips = Trip::with(['vehicle', 'driver', 'route'])
                ->where('status', 'completed')
                ->whereNull('invoice_id')
                ->where('broker_id', $brokerId)
                ->where('vehicle_id', $vehicleId)
                ->whereMonth('created_at', $month)
                ->whereYear('created_at', $year)
                ->get();
        }

        $brokers = Contact::where('is_customer', true)->get(['id', 'name']);
        $vehicles = Vehicle::where('is_active', true)->get(['id', 'plate_no']);
        
        $paymentAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '111%')
                  ->orWhere('code', 'like', '112%')
                  ->orWhere('code', 'like', '1101%')
                  ->orWhere('code', 'like', '1102%')
                  ->orWhere(function($sub) {
                      $sub->where('code', 'like', '1%')
                          ->where(function($sub2) {
                              $sub2->where('name', 'like', '%صندوق%')
                                   ->orWhere('name', 'like', '%بنك%');
                          });
                  });
            })->get(['id', 'code', 'name']);

        return Inertia::render('Logistics/Trips/MonthlyBilling', [
            'trips' => $trips,
            'brokers' => $brokers,
            'vehicles' => $vehicles,
            'paymentAccounts' => $paymentAccounts,
            'filters' => [
                'broker_id' => $brokerId ? intval($brokerId) : '',
                'vehicle_id' => $vehicleId ? intval($vehicleId) : '',
                'month' => $month ? intval($month) : '',
                'year' => $year ? intval($year) : '',
            ]
        ]);
    }

    public function generateMonthlyInvoice(Request $request)
    {
        $validated = $request->validate([
            'trip_ids' => 'required|array|min:1',
            'trip_ids.*' => 'exists:trips,id',
            'broker_id' => 'required|exists:contacts,id',
            'invoice_date' => 'required|date',
            'payment_mode' => 'required|in:cash,credit,bank',
            'payment_account_id' => 'required_if:payment_mode,cash,bank|nullable|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $trips = Trip::whereIn('id', $validated['trip_ids'])
                ->whereNull('invoice_id')
                ->where('status', 'completed')
                ->get();

            if ($trips->isEmpty()) {
                throw new \Exception('لا توجد رحلات صالحة ومكتملة غير مفוترة لإصدار الفاتورة لها.');
            }

            // 1. Get or Create Transport Service Item
            $item = Item::where('name', 'خدمات نقل')->first();
            if (!$item) {
                $unit = \App\Models\Unit::first();
                $category = \App\Models\ItemCategory::first();
                $item = Item::create([
                    'name' => 'خدمات نقل',
                    'type' => 'service',
                    'tax_rate' => 15.00,
                    'is_active' => true,
                    'track_inventory' => false,
                    'price' => 0.00,
                    'cost_price' => 0.00,
                    'unit_id' => $unit ? $unit->id : 1,
                    'category_id' => $category ? $category->id : 1,
                ]);
            }

            // 2. Sum up total base
            $totalBase = $trips->sum('broker_price');
            $totalTax = $totalBase * 0.15;
            $totalAmount = $totalBase + $totalTax;

            // Resolve System Accounts for Sales Invoice
            $baseAccount = Account::where('code', '4101')->first() ?? Account::where('name', 'المبيعات')->first();
            $taxAccount = Account::where('code', '2103')->first() ?? Account::where('name', 'like', '%ضريبة%')->first();
            
            if (!$baseAccount || !$taxAccount) {
                throw new \Exception('الحسابات المحاسبية الأساسية للمبيعات والضريبة غير معرفة في شجرة الحسابات.');
            }

            // Generate Invoice No
            $lastInvoice = Invoice::latest('id')->first();
            $nextNum = $lastInvoice ? intval(preg_replace('/[^0-9]/', '', $lastInvoice->invoice_no)) + 1 : 1000;
            $invoiceNo = 'INV-' . date('Y') . '-' . str_pad($nextNum, 4, '0', STR_PAD_LEFT);

            // Create Invoice
            $invoice = Invoice::create([
                'type' => 'sale',
                'contact_id' => $validated['broker_id'],
                'invoice_no' => $invoiceNo,
                'invoice_date' => $validated['invoice_date'],
                'due_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'],
                'payment_account_id' => $validated['payment_account_id'] ?? null,
                'notes' => $validated['notes'],
                'total_base' => $totalBase,
                'total_tax' => $totalTax,
                'total_amount' => $totalAmount,
                'base_account_id' => $baseAccount->id,
                'tax_account_id' => $taxAccount->id,
                'created_by' => auth()->id() ?? 1,
            ]);

            // Create Invoice Lines per Trip
            foreach ($trips as $trip) {
                $subtotal = floatval($trip->broker_price);
                $taxAmount = $subtotal * 0.15;
                $total = $subtotal + $taxAmount;

                $plateNo = $trip->vehicle ? $trip->vehicle->plate_no : '';
                $lineDesc = "رحلة رقم {$trip->trip_no} من {$trip->origin} إلى {$trip->destination} - شاحنة: {$plateNo} - {$trip->end_customer_name}";

                $invoice->lines()->create([
                    'item_id' => $item->id,
                    'item_name' => $lineDesc, // Save full description into item_name since we don't have description column
                    'quantity' => 1,
                    'unit_price' => $subtotal,
                    'tax_rate' => 15.00,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                ]);

                // Update Trip
                $trip->update(['invoice_id' => $invoice->id]);
            }

            // ZATCA QR Code & XML
            $zatca = new ZatcaService();
            $qrCodeBase64 = $zatca->generateQrCodeBase64(
                'شركة التفاؤل العربية للخدمات اللوجستية',
                '312253166400003',
                $invoice->invoice_date->format('Y-m-d\TH:i:s\Z'),
                $totalAmount,
                $totalTax
            );
            $xmlContent = $zatca->generateInvoiceXml($invoice);
            $xmlHash = $zatca->generateXmlHash($xmlContent);
            $xmlUuid = (string) \Illuminate\Support\Str::uuid();

            $invoice->update([
                'qr_code_base64' => $qrCodeBase64,
                'xml_content' => $xmlContent,
                'xml_uuid' => $xmlUuid,
                'zatca_hash' => $xmlHash,
                'zatca_status' => 'cleared'
            ]);

            // Generate Journal Entries
            $journalService = app(JournalEntryService::class);
            $contact = Contact::findOrFail($invoice->contact_id);
            $fiscalYear = FiscalYear::where('is_closed', false)->first();

            if (!$fiscalYear) {
                throw new \Exception('لا توجد سنة مالية مفتوحة.');
            }

            // For cash/bank: use the payment account. For credit: use partner's receivable account.
            $partnerReceivableId = $contact->receivable_account_id;
            $contactOrCashAccount = ($invoice->payment_mode === 'cash' || $invoice->payment_mode === 'bank') 
                ? $invoice->payment_account_id 
                : $partnerReceivableId;

            if (!$contactOrCashAccount) {
                throw new \Exception('حساب جهة الاتصال (المدينين) غير معرف. يرجى تعيين حساب المدينين للعميل من صفحة جهات الاتصال.');
            }

            $description = "فاتورة مبيعات مجمعة للرحلات رقم " . $invoice->invoice_no . ' - ' . $contact->name;
            $lines = [
                ['account_id' => $contactOrCashAccount, 'debit' => $invoice->total_amount, 'credit' => 0, 'description' => $description],
                ['account_id' => $invoice->base_account_id, 'debit' => 0, 'credit' => $invoice->total_base, 'description' => $description],
            ];
            if ($invoice->total_tax > 0) {
                $lines[] = ['account_id' => $invoice->tax_account_id, 'debit' => 0, 'credit' => $invoice->total_tax, 'description' => $description];
            }

            $entry = $journalService->create([
                'entry_date' => $invoice->invoice_date,
                'description' => $description,
                'fiscal_year_id' => $fiscalYear->id,
                'transaction_type' => 'invoice',
                'reference_id' => $invoice->id,
                'status' => 'posted',
                'lines' => $lines
            ]);

            $invoice->update(['journal_entry_id' => $entry->id]);

            DB::commit();
            return redirect()->route('invoices.show', $invoice->id)
                ->with('success', 'تم إنشاء الفاتورة المجمعة وترحيل القيد المحاسبي بنجاح!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'حدث خطأ أثناء إصدار الفاتورة: ' . $e->getMessage()]);
        }
    }
}
