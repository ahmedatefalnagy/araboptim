<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CostCenterController extends Controller
{
    public function index()
    {
        $costCenters = CostCenter::with('parent')->latest()->get();
        return Inertia::render('CostCenters/Index', [
            'costCenters' => $costCenters
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:cost_centers,code',
            'name' => 'required|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'is_active' => 'boolean'
        ]);

        CostCenter::create($validated);
        return redirect()->back()->with('success', 'تم إضافة مركز التكلفة بنجاح.');
    }

    public function update(Request $request, CostCenter $costCenter)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:cost_centers,code,' . $costCenter->id,
            'name' => 'required|string',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:cost_centers,id',
            'is_active' => 'boolean'
        ]);

        $costCenter->update($validated);
        return redirect()->back()->with('success', 'تم تصحيح مركز التكلفة بنجاح.');
    }

    public function destroy(CostCenter $costCenter)
    {
        try {
            $costCenter->delete();
            return redirect()->back()->with('success', 'تم حذف مركز التكلفة.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'لا يمكن حذف مركز التكلفة لارتباطه بقيود أو فواتير في النظام.');
        }
    }
}
