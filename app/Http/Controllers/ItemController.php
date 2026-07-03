<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::with(['category', 'unit'])->latest()->get();
        return Inertia::render('Items/Index', [
            'items' => $items
        ]);
    }

    public function create()
    {
        $categories = ItemCategory::all();
        $units = Unit::all();
        return Inertia::render('Items/Create', [
            'categories' => $categories,
            'units' => $units
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'sku' => 'nullable|string|max:255|unique:items',
            'barcode' => 'nullable|string|max:255|unique:items',
            'type' => 'required|in:product,service',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:item_categories,id',
            'track_inventory' => 'boolean',
            'alert_quantity' => 'numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        Item::create($validated);

        return redirect()->route('items.index')->with('message', 'تم إضافة الصنف بنجاح');
    }

    public function quickStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name',
            'sku' => 'nullable|string|max:255|unique:items',
            'barcode' => 'nullable|string|max:255|unique:items',
            'type' => 'required|in:product,service',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:item_categories,id',
            'track_inventory' => 'boolean',
            'alert_quantity' => 'numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $item = Item::create($validated);

        return response()->json([
            'success' => true,
            'item' => $item
        ]);
    }

    public function edit(Item $item)
    {
        $categories = ItemCategory::all();
        $units = Unit::all();
        return Inertia::render('Items/Edit', [
            'item' => $item,
            'categories' => $categories,
            'units' => $units
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:items,name,' . $item->id,
            'sku' => 'nullable|string|max:255|unique:items,sku,' . $item->id,
            'barcode' => 'nullable|string|max:255|unique:items,barcode,' . $item->id,
            'type' => 'required|in:product,service',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'required|numeric|min:0',
            'tax_rate' => 'required|numeric|min:0',
            'unit_id' => 'required|exists:units,id',
            'category_id' => 'required|exists:item_categories,id',
            'track_inventory' => 'boolean',
            'alert_quantity' => 'numeric|min:0',
            'is_active' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $item->update($validated);

        return redirect()->route('items.index')->with('message', 'تم تحديث الصنف بنجاح');
    }

    public function destroy(Item $item)
    {
        // Check if there is any stock quantity in any warehouse
        $stockQty = $item->stocks()->sum('quantity');
        if ($stockQty > 0) {
            return redirect()->back()->withErrors(['message' => 'لا يمكن حذف الصنف لوجود كميات متوفرة منه في المستودع.']);
        }

        // Also check if the item is used in any transactions to avoid foreign key violations or historical loss
        $hasMovements = $item->movements()->exists();
        $hasInvoices = \App\Models\InvoiceLine::where('item_id', $item->id)->exists();

        if ($hasMovements || $hasInvoices) {
            return redirect()->back()->withErrors(['message' => 'لا يمكن حذف الصنف لوجود حركات مخزنية أو فواتير مسجلة عليه. يمكنك إلغاء تفعيله بدلاً من ذلك.']);
        }

        try {
            // Delete stocks first (if quantity is 0)
            $item->stocks()->delete();
            $item->delete();
            return redirect()->route('items.index')->with('message', 'تم حذف الصنف بنجاح');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['message' => 'حدث خطأ أثناء محاولة الحذف: ' . $e->getMessage()]);
        }
    }
}
