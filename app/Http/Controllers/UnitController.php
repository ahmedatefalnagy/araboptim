<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::withCount('items')->latest()->get();
        return Inertia::render('Units/Index', [
            'units' => $units
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units',
            'short_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        Unit::create($validated);

        return redirect()->route('units.index')->with('message', 'تم إضافة وحدة القياس بنجاح');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'short_name' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $unit->update($validated);

        return redirect()->route('units.index')->with('message', 'تم تحديث وحدة القياس بنجاح');
    }

    public function destroy(Unit $unit)
    {
        if ($unit->items()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف وحدة القياس لارتباطها بأصناف مخزنية.');
        }

        $unit->delete();

        return redirect()->route('units.index')->with('message', 'تم حذف وحدة القياس بنجاح');
    }
}
