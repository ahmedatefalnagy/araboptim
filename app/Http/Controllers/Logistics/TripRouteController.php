<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\TripRoute;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripRouteController extends Controller
{
    public function index()
    {
        return Inertia::render('Logistics/Routes/Index', [
            'routes' => TripRoute::latest()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:trip_routes,name',
            'origin' => 'required|string',
            'destination' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request) {
                    $exists = \App\Models\TripRoute::where('origin', $request->origin)
                        ->where('destination', $value)
                        ->exists();
                    if ($exists) {
                        $fail('يوجد مسار مسجل بالفعل بنفس نقطة التحميل ونقطة التفريغ.');
                    }
                }
            ],
            'distance_km' => 'nullable|numeric|min:0',
            'standard_budget' => 'required|numeric|min:0',
            'standard_diesel_budget' => 'nullable|numeric|min:0',
            'standard_driver_commission' => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'هذا المسار موجود بالفعل. يرجى اختيار اسم مختلف.',
            'name.required' => 'اسم المسار مطلوب.',
            'origin.required' => 'نقطة التحميل مطلوبة.',
            'destination.required' => 'نقطة التفريغ مطلوبة.',
        ]);

        TripRoute::create($validated);
        return redirect()->back()->with('success', 'تم إضافة المسار بنجاح.');
    }

    public function update(Request $request, TripRoute $route)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:trip_routes,name,' . $route->id,
            'origin' => 'required|string',
            'destination' => [
                'required',
                'string',
                function ($attribute, $value, $fail) use ($request, $route) {
                    $exists = \App\Models\TripRoute::where('origin', $request->origin)
                        ->where('destination', $value)
                        ->where('id', '!=', $route->id)
                        ->exists();
                    if ($exists) {
                        $fail('يوجد مسار مسجل بالفعل بنفس نقطة التحميل ونقطة التفريغ.');
                    }
                }
            ],
            'distance_km' => 'nullable|numeric|min:0',
            'standard_budget' => 'required|numeric|min:0',
            'standard_diesel_budget' => 'nullable|numeric|min:0',
            'standard_driver_commission' => 'nullable|numeric|min:0',
        ], [
            'name.unique' => 'هذا المسار موجود بالفعل. يرجى اختيار اسم مختلف.',
        ]);

        $route->update($validated);
        return redirect()->back()->with('success', 'تم تحديث بيانات المسار.');
    }

    public function destroy(TripRoute $route)
    {
        $route->delete();
        return redirect()->back()->with('success', 'تم حذف المسار.');
    }
}
