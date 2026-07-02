<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WarehouseController extends Controller
{
    public function index()
    {
        $warehouses = Warehouse::withCount('stocks')->latest()->get();
        return Inertia::render('Warehouses/Index', [
            'warehouses' => $warehouses
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses',
            'code' => 'required|string|max:50|unique:warehouses',
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        Warehouse::create($validated);

        return redirect()->route('warehouses.index')->with('message', 'تم إضافة المستودع بنجاح');
    }

    public function update(Request $request, Warehouse $warehouse)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name,' . $warehouse->id,
            'code' => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'location' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $warehouse->update($validated);

        return redirect()->route('warehouses.index')->with('message', 'تم تحديث المستودع بنجاح');
    }

    public function destroy(Warehouse $warehouse)
    {
        if ($warehouse->stocks()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المستودع لوجود أرصدة مخزنية مرتبطة به.');
        }

        $warehouse->delete();

        return redirect()->route('warehouses.index')->with('message', 'تم حذف المستودع بنجاح');
    }
}
