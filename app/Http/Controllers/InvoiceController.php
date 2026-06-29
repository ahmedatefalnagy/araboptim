<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Contact;
use App\Models\Account;
use App\Models\AccountType;
use App\Models\JournalEntry;
use App\Models\FiscalYear;
use App\Models\Item;
use App\Models\Setting;
use App\Models\Warehouse;
use App\Models\InventoryStock;
use App\Models\StockMovement;
use App\Models\CostCenter;
use App\Services\JournalEntryService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Services\ZatcaService;

class InvoiceController extends Controller
{
    protected $journalService;

    public function __construct(JournalEntryService $journalService)
    {
        $this->journalService = $journalService;
    }

    public function index(Request $request)
    {
        $type = $request->query('type', 'sale');
        
        if (in_array($type, ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }
        
        $invoices = Invoice::with(['contact', 'journalEntry'])
            ->where('type', $type)
            ->latest('invoice_date')
            ->get();
            
        return Inertia::render('Invoices/Index', [
            'type' => $type,
            'invoices' => $invoices
        ]);
    }

    public function create(Request $request)
    {
        $type = $request->query('type', 'sale');
        
        if (in_array($type, ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }
        
        if (in_array($type, ['goods_receipt', 'goods_issue'])) {
            $contacts = Contact::all(['id', 'name', 'account_id']);
        } else {
            $contactType = in_array($type, ['sale', 'sale_return', 'sale_quotation', 'sale_order', 'work_order']) ? 'customer' : 'supplier';
            $contacts = Contact::where('type', $contactType)->get(['id', 'name', 'account_id']);
        }
        $items = Item::where('is_active', true)->get(['id', 'name', 'sku', 'price', 'cost_price', 'tax_rate', 'type', 'track_inventory']);
        $warehouses = Warehouse::where('is_active', true)->get();
        $costCenters = CostCenter::where('is_active', true)->get(['id', 'code', 'name']);
        
        // Fetch Bank/Cash accounts for quick payment
        $paymentAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '111%') // Cash
                  ->orWhere('code', 'like', '112%') // Banks
                  ->orWhere(function($sub) {
                      $sub->where('code', 'like', '1%')
                          ->where(function($sub2) {
                              $sub2->where('name', 'like', '%صندوق%')
                                   ->orWhere('name', 'like', '%بنك%');
                          });
                  });
            })->get(['id', 'code', 'name']);

        // Get default fiscal year from settings
        $defaultYearId = Setting::get('default_fiscal_year_id');
        $fiscalYears = FiscalYear::orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date']);
        
        $parentDocumentId = $request->query('parent_document_id');
        $parentDocument = $parentDocumentId ? Invoice::with('lines.item')->find($parentDocumentId) : null;
        $workOrders = Invoice::where('type', 'work_order')->latest()->get(['id', 'invoice_no']);
        
        return Inertia::render('Invoices/Create', [
            'type' => $type,
            'contacts' => $contacts,
            'items' => $items,
            'warehouses' => $warehouses,
            'costCenters' => $costCenters,
            'paymentAccounts' => $paymentAccounts,
            'parentDocument' => $parentDocument,
            'workOrders' => $workOrders,
            'fiscalYears' => $fiscalYears,
            'selectedFiscalYearId' => $defaultYearId,
        ]);
    }

    public function store(Request $request)
    {
        if (in_array($request->input('type'), ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }

        $validated = $request->validate([
            'type' => 'required|in:sale,sale_return,purchase,purchase_return,sale_quotation,sale_order,purchase_quotation,purchase_order,work_order,goods_receipt,goods_issue',
            'contact_id' => 'required|exists:contacts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'parent_document_id' => 'nullable|exists:invoices,id',
            'invoice_no' => 'required|string|unique:invoices,invoice_no',
            'invoice_date' => 'required|date',
            'payment_mode' => 'nullable|in:cash,credit,bank',
            'payment_account_id' => 'required_if:payment_mode,cash,bank|nullable|exists:accounts,id',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => in_array($request->type, ['work_order', 'goods_receipt', 'goods_issue']) ? 'nullable|numeric' : 'required|numeric|min:0',
            'lines.*.cost_center_id' => 'nullable|exists:cost_centers,id',
        ]);

        // Handle File Upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/attachments'), $fileName);
            $attachmentPath = '/uploads/attachments/' . $fileName;
        }

        DB::beginTransaction();
        try {
            $totalBase = 0;
            $totalTax = 0;
            $isFinancial = in_array($validated['type'], ['sale', 'sale_return', 'purchase', 'purchase_return']);
            
            // Auto Resolve System Accounts
            $resolvedAccounts = $isFinancial ? $this->resolveSystemAccounts($validated['type']) : ['base' => null, 'tax' => null];
            
            // Resolve parent_document_id and auto-inherit linked work order if parent is a quotation/order
            $parentDocumentId = $validated['parent_document_id'] ?? null;
            if ($parentDocumentId) {
                $parentDoc = Invoice::find($parentDocumentId);
                if ($parentDoc && in_array($parentDoc->type, ['sale_quotation', 'purchase_quotation', 'sale_order', 'purchase_order'])) {
                    if ($parentDoc->parentDocument && $parentDoc->parentDocument->type === 'work_order') {
                        $parentDocumentId = $parentDoc->parent_document_id;
                    }
                }
            }

            // Create Invoice
            $invoice = Invoice::create([
                'type' => $validated['type'],
                'contact_id' => $validated['contact_id'],
                'invoice_no' => $validated['invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'] ?? 'credit',
                'payment_account_id' => $validated['payment_account_id'] ?? null,
                'notes' => $validated['notes'],
                'attachment_path' => $attachmentPath,
                'total_base' => 0, 
                'total_tax' => 0,
                'total_amount' => 0,
                'base_account_id' => $resolvedAccounts['base'],
                'tax_account_id' => $resolvedAccounts['tax'], 
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'parent_document_id' => $parentDocumentId,
                'created_by' => auth()->id() ?? 1,
            ]);

            foreach ($validated['lines'] as $line) {
                $item = Item::find($line['item_id']);
                
                $quantity = floatval($line['quantity']);
                $unitPrice = floatval($line['unit_price']);
                $subtotal = $quantity * $unitPrice;
                $taxAmount = $subtotal * ($item->tax_rate / 100);
                $total = $subtotal + $taxAmount;

                $totalBase += $subtotal;
                $totalTax += $taxAmount;

                $invoice->lines()->create([
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $item->tax_rate,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                ]);

                // ERP Core: Stock Movement Logic!
                $isStockDoc = in_array($validated['type'], ['goods_receipt', 'goods_issue']);
                if ($item->track_inventory && $validated['warehouse_id'] && ($isFinancial || $isStockDoc)) {
                    $stockType = in_array($validated['type'], ['purchase', 'sale_return', 'goods_receipt']) ? 'in' : 'out';
                    
                    // Create Movement History
                    StockMovement::create([
                        'item_id' => $item->id,
                        'warehouse_id' => $validated['warehouse_id'],
                        'type' => $stockType,
                        'quantity' => $quantity,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'movement_date' => $validated['invoice_date'],
                        'cost_per_unit' => $stockType === 'in' ? $unitPrice : $item->cost_price,
                        'notes' => 'Invoice Ref: ' . $invoice->invoice_no,
                    ]);

                    // Update live stocks
                    $stock = InventoryStock::firstOrCreate(
                        ['item_id' => $item->id, 'warehouse_id' => $validated['warehouse_id']],
                        ['quantity' => 0]
                    );

                    if ($stockType === 'in') {
                        $stock->increment('quantity', $quantity);
                    } else {
                        $stock->decrement('quantity', $quantity);
                    }
                }
            }

            // Execute ZATCA Protocol for Financial Sales Invoices
            $qrCodeBase64 = null;
            $xmlContent = null;
            $xmlUuid = null;
            $xmlHash = null;

            if ($validated['type'] === 'sale' || $validated['type'] === 'sale_return') {
                $zatca = new ZatcaService();
                $qrCodeBase64 = $zatca->generateQrCodeBase64(
                    'شركة الأفق للتجارة والمقاولات (تجريبي)', // Seller Name
                    '300000000000003', // Commercial VAT Number
                    $invoice->invoice_date->format('Y-m-d\TH:i:s\Z'),
                    $totalBase + $totalTax,
                    $totalTax
                );
                
                $xmlContent = $zatca->generateInvoiceXml($invoice);
                $xmlHash = $zatca->generateXmlHash($xmlContent);
                $xmlUuid = (string) Str::uuid();
            }

            $invoice->update([
                'total_base' => $totalBase,
                'total_tax' => $totalTax,
                'total_amount' => $totalBase + $totalTax,
                'tax_account_id' => $totalTax > 0 ? $resolvedAccounts['tax'] : null,
                'qr_code_base64' => $qrCodeBase64,
                'xml_content' => $xmlContent,
                'xml_uuid' => $xmlUuid,
                'zatca_hash' => $xmlHash,
                'zatca_status' => $qrCodeBase64 ? 'cleared' : 'pending' // Simplified for demo
            ]);

            if ($isFinancial) {
                $this->generateJournalEntry($invoice);
            }
            
            DB::commit();
            return redirect()->route('invoices.index', ['type' => $invoice->type])
                ->with('success', 'تم حفظ الفاتورة بنجاح وثم ترحيل القيود وتأثير المخزون!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function edit(Invoice $invoice)
    {
        if (in_array($invoice->type, ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }
        // Invoices or returns cannot be edited if already finalized/cleared
        if (in_array($invoice->type, ['sale', 'sale_return', 'purchase', 'purchase_return']) && 
            in_array($invoice->zatca_status, ['cleared', 'reported'])) {
            // Wait, for demo, let's allow editing unless explicitly locked or if we just want a soft check.
            // But since the user requested "إلا الفواتير تعدل قبل تصديرها", we'll block if cleared/reported.
            return redirect()->back()->withErrors(['message' => 'لا يمكن تعديل الفاتورة بعد اعتمادها وتصديرها.']);
        }

        $invoice->load('lines.item');

        $type = $invoice->type;
        $contactType = in_array($type, ['sale', 'sale_return', 'sale_quotation', 'sale_order', 'work_order']) ? 'customer' : 'supplier';
        $contacts = Contact::where('type', $contactType)->get(['id', 'name', 'account_id']);
        $items = Item::where('is_active', true)->get(['id', 'name', 'sku', 'price', 'cost_price', 'tax_rate', 'type', 'track_inventory']);
        $warehouses = Warehouse::where('is_active', true)->get();
        $costCenters = CostCenter::where('is_active', true)->get(['id', 'code', 'name']);
        
        $paymentAccounts = Account::where('is_postable', true)
            ->where(function($q) {
                $q->where('code', 'like', '111%')
                  ->orWhere('code', 'like', '112%')
                  ->orWhere(function($sub) {
                      $sub->where('code', 'like', '1%')
                          ->where(function($sub2) {
                              $sub2->where('name', 'like', '%صندوق%')
                                   ->orWhere('name', 'like', '%بنك%');
                          });
                  });
            })->get(['id', 'code', 'name']);

        $defaultYearId = Setting::get('default_fiscal_year_id');
        $fiscalYears = FiscalYear::orderByDesc('start_date')
            ->get(['id', 'name', 'start_date', 'end_date']);
        $workOrders = Invoice::where('type', 'work_order')->latest()->get(['id', 'invoice_no']);

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
            'type' => $type,
            'contacts' => $contacts,
            'items' => $items,
            'warehouses' => $warehouses,
            'costCenters' => $costCenters,
            'paymentAccounts' => $paymentAccounts,
            'workOrders' => $workOrders,
            'fiscalYears' => $fiscalYears,
            'selectedFiscalYearId' => $invoice->fiscal_year_id ?? $defaultYearId,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (in_array($invoice->type, ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }
        if (in_array($invoice->type, ['sale', 'sale_return', 'purchase', 'purchase_return']) && 
            in_array($invoice->zatca_status, ['cleared', 'reported'])) {
            return redirect()->back()->withErrors(['message' => 'لا يمكن تعديل الفاتورة بعد اعتمادها وتصديرها.']);
        }

        $validated = $request->validate([
            'type' => 'required|in:sale,sale_return,purchase,purchase_return,sale_quotation,sale_order,purchase_quotation,purchase_order,work_order,goods_receipt,goods_issue',
            'contact_id' => 'required|exists:contacts,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'cost_center_id' => 'nullable|exists:cost_centers,id',
            'parent_document_id' => 'nullable|exists:invoices,id',
            'invoice_no' => 'required|string|unique:invoices,invoice_no,' . $invoice->id,
            'invoice_date' => 'required|date',
            'payment_mode' => 'nullable|in:cash,credit,bank',
            'payment_account_id' => 'required_if:payment_mode,cash,bank|nullable|exists:accounts,id',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'lines' => 'required|array|min:1',
            'lines.*.item_id' => 'required|exists:items,id',
            'lines.*.quantity' => 'required|numeric|min:0.01',
            'lines.*.unit_price' => in_array($request->type, ['work_order', 'goods_receipt', 'goods_issue']) ? 'nullable|numeric' : 'required|numeric|min:0',
            'lines.*.cost_center_id' => 'nullable|exists:cost_centers,id',
        ]);

        // Process File Upload on Update
        $attachmentPath = $invoice->attachment_path;
        if ($request->hasFile('attachment')) {
            if ($invoice->attachment_path && file_exists(public_path($invoice->attachment_path))) {
                @unlink(public_path($invoice->attachment_path));
            }
            $file = $request->file('attachment');
            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/attachments'), $fileName);
            $attachmentPath = '/uploads/attachments/' . $fileName;
        }

        DB::beginTransaction();
        try {
            $isFinancial = in_array($validated['type'], ['sale', 'sale_return', 'purchase', 'purchase_return']);

            // 1. Revert Old Stock Movements
            $oldMovements = StockMovement::where('reference_type', Invoice::class)
                                         ->where('reference_id', $invoice->id)
                                         ->get();
            foreach ($oldMovements as $m) {
                $stock = InventoryStock::where('item_id', $m->item_id)
                                       ->where('warehouse_id', $m->warehouse_id)
                                       ->first();
                if ($stock) {
                    if ($m->type === 'in') {
                        $stock->decrement('quantity', $m->quantity);
                    } else {
                        $stock->increment('quantity', $m->quantity);
                    }
                }
                $m->delete();
            }

            // 2. Delete Old Journal Entry
            if ($invoice->journalEntry) {
                $invoice->journalEntry->lines()->delete();
                $invoice->journalEntry->delete();
                $invoice->update(['journal_entry_id' => null]);
            }

            // 3. Delete Old Lines
            $invoice->lines()->delete();

            // 4. Update Invoice Header & Lines
            $totalBase = 0;
            $totalTax = 0;
            
            $resolvedAccounts = $isFinancial ? $this->resolveSystemAccounts($validated['type']) : ['base' => null, 'tax' => null];

            // Resolve parent_document_id and auto-inherit linked work order if parent is a quotation/order
            $parentDocumentId = $validated['parent_document_id'] ?? $invoice->parent_document_id;
            if ($parentDocumentId) {
                $parentDoc = Invoice::find($parentDocumentId);
                if ($parentDoc && in_array($parentDoc->type, ['sale_quotation', 'purchase_quotation', 'sale_order', 'purchase_order'])) {
                    if ($parentDoc->parentDocument && $parentDoc->parentDocument->type === 'work_order') {
                        $parentDocumentId = $parentDoc->parent_document_id;
                    }
                }
            }

            $invoice->update([
                'type' => $validated['type'],
                'contact_id' => $validated['contact_id'],
                'invoice_no' => $validated['invoice_no'],
                'invoice_date' => $validated['invoice_date'],
                'payment_mode' => $validated['payment_mode'] ?? 'credit',
                'payment_account_id' => $validated['payment_account_id'] ?? null,
                'notes' => $validated['notes'],
                'attachment_path' => $attachmentPath,
                'base_account_id' => $resolvedAccounts['base'],
                'tax_account_id' => $resolvedAccounts['tax'], 
                'cost_center_id' => $validated['cost_center_id'] ?? null,
                'parent_document_id' => $parentDocumentId,
            ]);

            foreach ($validated['lines'] as $line) {
                $item = Item::find($line['item_id']);
                
                $quantity = floatval($line['quantity']);
                $unitPrice = floatval($line['unit_price'] ?? 0);
                $subtotal = $quantity * $unitPrice;
                $taxAmount = $subtotal * ($item->tax_rate / 100);
                $total = $subtotal + $taxAmount;

                $totalBase += $subtotal;
                $totalTax += $taxAmount;

                $invoice->lines()->create([
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_rate' => $item->tax_rate,
                    'subtotal' => $subtotal,
                    'tax_amount' => $taxAmount,
                    'total' => $total,
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                ]);

                // Create Stock Movement History
                $isStockDoc = in_array($validated['type'], ['goods_receipt', 'goods_issue']);
                if ($item->track_inventory && $validated['warehouse_id'] && ($isFinancial || $isStockDoc)) {
                    $stockType = in_array($validated['type'], ['purchase', 'sale_return', 'goods_receipt']) ? 'in' : 'out';
                    
                    StockMovement::create([
                        'item_id' => $item->id,
                        'warehouse_id' => $validated['warehouse_id'],
                        'type' => $stockType,
                        'quantity' => $quantity,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'movement_date' => $validated['invoice_date'],
                        'cost_per_unit' => $stockType === 'in' ? $unitPrice : $item->cost_price,
                        'notes' => 'Invoice Ref (Edit): ' . $invoice->invoice_no,
                    ]);

                    $stock = InventoryStock::firstOrCreate(
                        ['item_id' => $item->id, 'warehouse_id' => $validated['warehouse_id']],
                        ['quantity' => 0]
                    );

                    if ($stockType === 'in') {
                        $stock->increment('quantity', $quantity);
                    } else {
                        $stock->decrement('quantity', $quantity);
                    }
                }
            }

            // Recalculate ZATCA parameters
            $qrCodeBase64 = null;
            $xmlContent = null;
            $xmlUuid = null;
            $xmlHash = null;

            if ($validated['type'] === 'sale' || $validated['type'] === 'sale_return') {
                $zatca = new ZatcaService();
                $qrCodeBase64 = $zatca->generateQrCodeBase64(
                    'شركة الأفق للتجارة والمقاولات (تجريبي)',
                    '300000000000003',
                    $invoice->invoice_date->format('Y-m-d\TH:i:s\Z'),
                    $totalBase + $totalTax,
                    $totalTax
                );
                
                $xmlContent = $zatca->generateInvoiceXml($invoice);
                $xmlHash = $zatca->generateXmlHash($xmlContent);
                $xmlUuid = (string) Str::uuid();
            }

            $invoice->update([
                'total_base' => $totalBase,
                'total_tax' => $totalTax,
                'total_amount' => $totalBase + $totalTax,
                'tax_account_id' => $totalTax > 0 ? $resolvedAccounts['tax'] : null,
                'qr_code_base64' => $qrCodeBase64,
                'xml_content' => $xmlContent,
                'xml_uuid' => $xmlUuid,
                'zatca_hash' => $xmlHash,
                'zatca_status' => $qrCodeBase64 ? 'cleared' : 'pending'
            ]);

            if ($isFinancial) {
                $this->generateJournalEntry($invoice);
            }

            DB::commit();
            return redirect()->route('invoices.index', ['type' => $invoice->type])
                ->with('success', 'تم تعديل المستند بنجاح وتحديث الحسابات والمستودع!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'حدث خطأ: ' . $e->getMessage()]);
        }
    }

    public function show(Invoice $invoice)
    {
        if (in_array($invoice->type, ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }

        $invoice->load(['contact', 'baseAccount', 'taxAccount', 'journalEntry', 'lines.item', 'parentDocument', 'childDocuments']);
        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        if (in_array($invoice->type, ['goods_receipt', 'goods_issue']) && auth()->user()?->role !== 'admin') {
            abort(403, 'غير مصرح للقيام بهذه العملية.');
        }

        DB::beginTransaction();
        try {
            if ($invoice->journalEntry) {
                $invoice->journalEntry->lines()->delete();
                $invoice->journalEntry->delete();
            }
            
            $type = $invoice->type;
            
            // Revert Stock Movements!
            $movements = StockMovement::where('reference_type', Invoice::class)
                                      ->where('reference_id', $invoice->id)
                                      ->get();
            foreach ($movements as $m) {
                $stock = InventoryStock::where('item_id', $m->item_id)
                                       ->where('warehouse_id', $m->warehouse_id)
                                       ->first();
                if ($stock) {
                    if ($m->type === 'in') {
                        $stock->decrement('quantity', $m->quantity);
                    } else {
                        $stock->increment('quantity', $m->quantity);
                    }
                }
                $m->delete();
            }

            $invoice->lines()->delete();
            $invoice->delete();
            DB::commit();
            
            return redirect()->route('invoices.index', ['type' => $type])
                ->with('success', 'تم التراجع عن الفاتورة والمخزون وحذف القيد.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'لا يمكن حذف الفاتورة.']);
        }
    }

    private function generateJournalEntry(Invoice $invoice)
    {
        $contact = Contact::findOrFail($invoice->contact_id);
        $fiscalYear = FiscalYear::where('is_closed', false)->first();

        if (!$fiscalYear) {
            throw new \Exception('لا توجد سنة مالية مفتوحة.');
        }

        if ($invoice->payment_mode === 'credit' && !$contact->account_id) {
            throw new \Exception('جهة الاتصال المحددة غير مربوطة بحساب في الدليل المحاسبي للعمليات الآجلة.');
        }

        $typesLabels = [
            'sale' => 'فاتورة مبيعات ضريبية رقم ',
            'purchase' => 'فاتورة مشتريات وتوريد رقم ',
            'sale_return' => 'مرتجع مبيعات رقم ',
            'purchase_return' => 'مرتجع مشتريات رقم ',
        ];

        $description = ($typesLabels[$invoice->type] ?? 'فاتورة ') . $invoice->invoice_no . ' - ' . $contact->name;
        
        // Resolve dynamic side based on payment mode and contact accounts
        $partnerAccount = null;
        if (in_array($invoice->type, ['sale', 'sale_return'])) {
            $partnerAccount = $contact->receivable_account_id ?? $contact->account_id;
        } else {
            $partnerAccount = $contact->payable_account_id ?? $contact->account_id;
        }

        // الدفع النقدي والبنكي كلاهما يستخدم الحساب المختار (صندوق أو حساب بنكي)، والآجل يستخدم حساب الجهة.
        $isImmediatePayment = in_array($invoice->payment_mode, ['cash', 'bank']);
        $contactOrCashAccount = $isImmediatePayment ? $invoice->payment_account_id : $partnerAccount;

        if (!$contactOrCashAccount) {
            throw new \Exception('لم يتم تحديد حساب محاسبي لهذه الجهة (مدينون/دائنون).');
        }

        $lines = [];
        if ($invoice->type === 'sale') {
            $lines[] = ['account_id' => $contactOrCashAccount, 'debit' => $invoice->total_amount, 'credit' => 0, 'description' => $description];
            $lines[] = ['account_id' => $invoice->base_account_id, 'debit' => 0, 'credit' => $invoice->total_base, 'description' => $description];
            if ($invoice->total_tax > 0) {
                $lines[] = ['account_id' => $invoice->tax_account_id, 'debit' => 0, 'credit' => $invoice->total_tax, 'description' => $description];
            }
        } elseif ($invoice->type === 'purchase') {
            $lines[] = ['account_id' => $invoice->base_account_id, 'debit' => $invoice->total_base, 'credit' => 0, 'description' => $description];
            if ($invoice->total_tax > 0) {
                $lines[] = ['account_id' => $invoice->tax_account_id, 'debit' => $invoice->total_tax, 'credit' => 0, 'description' => $description];
            }
            $lines[] = ['account_id' => $contactOrCashAccount, 'debit' => 0, 'credit' => $invoice->total_amount, 'description' => $description];
        } elseif ($invoice->type === 'sale_return') {
            $lines[] = ['account_id' => $invoice->base_account_id, 'debit' => $invoice->total_base, 'credit' => 0, 'description' => $description];
            if ($invoice->total_tax > 0) {
                $lines[] = ['account_id' => $invoice->tax_account_id, 'debit' => $invoice->total_tax, 'credit' => 0, 'description' => $description];
            }
            $lines[] = ['account_id' => $contactOrCashAccount, 'debit' => 0, 'credit' => $invoice->total_amount, 'description' => $description];
        } elseif ($invoice->type === 'purchase_return') {
            $lines[] = ['account_id' => $contactOrCashAccount, 'debit' => $invoice->total_amount, 'credit' => 0, 'description' => $description];
            $lines[] = ['account_id' => $invoice->base_account_id, 'debit' => 0, 'credit' => $invoice->total_base, 'description' => $description];
            if ($invoice->total_tax > 0) {
                $lines[] = ['account_id' => $invoice->tax_account_id, 'debit' => 0, 'credit' => $invoice->total_tax, 'description' => $description];
            }
        }

        $this->journalService->create([
            'entry_date' => $invoice->invoice_date,
            'description' => $description,
            'fiscal_year_id' => $fiscalYear->id,
            'transaction_type' => 'invoice',
            'reference_id' => $invoice->id,
            'status' => 'posted',
            'lines' => $lines
        ]);
    }

    private function resolveSystemAccounts($type)
    {
        $defs = [
            'sale' => [
                'base' => ['code' => '4101', 'name' => 'المبيعات', 'type' => 'revenue'],
                'tax' => ['code' => '2103', 'name' => 'ضريبة القيمة المضافة مستحقة (مخرجات)', 'type' => 'liability']
            ],
            'sale_return' => [
                'base' => ['code' => '4101', 'name' => 'المبيعات', 'type' => 'revenue'],
                'tax' => ['code' => '2103', 'name' => 'ضريبة القيمة المضافة مستحقة (مخرجات)', 'type' => 'liability']
            ],
            'purchase' => [
                'base' => ['code' => '5101', 'name' => 'تكلفة البضاعة المباعة', 'type' => 'expense'],
                'tax' => ['code' => '1107', 'name' => 'ضريبة القيمة المضافة مدينة (مدخلات)', 'type' => 'asset']
            ],
            'purchase_return' => [
                'base' => ['code' => '5101', 'name' => 'تكلفة البضاعة المباعة', 'type' => 'expense'],
                'tax' => ['code' => '1107', 'name' => 'ضريبة القيمة المضافة مدينة (مدخلات)', 'type' => 'asset']
            ],
        ];

        $getAccount = function($def) {
            // First try to find by code (Standardized)
            $acc = Account::where('code', $def['code'])->first();
            
            // If not found, look by name
            if (!$acc) {
                $acc = Account::where('name', $def['name'])->first();
            }

            // If still not found, throw exception (to show clear error)
            if (!$acc) {
                throw new \Exception("الحساب المحاسبي المطلوب (كود: {$def['code']}) غير موجود في شجرة الحسابات. يرجى إضافته أولاً.");
            }

            // Ensure it is postable
            if (!$acc->is_postable) {
                $acc->update(['is_postable' => true]);
            }

            return $acc->id;
        };

        return [
            'base' => $getAccount($defs[$type]['base']),
            'tax' => $getAccount($defs[$type]['tax']),
        ];
    }
}
