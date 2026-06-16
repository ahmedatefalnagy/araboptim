<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Employee;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with('driver')->get();
        $drivers = Employee::where('is_driver', true)->get(['id', 'name', 'employee_no']);
        
        return Inertia::render('Logistics/Vehicles/Index', [
            'vehicles' => $vehicles,
            'drivers' => $drivers
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plate_no' => 'required|string|unique:vehicles',
            'model' => 'nullable|string',
            'type' => 'required|string',
            'driver_id' => 'nullable|exists:employees,id',
            'odometer' => 'nullable|numeric',
            'registration_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'insurance_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('registration_copy')) {
            $validated['registration_copy'] = $request->file('registration_copy')->store('vehicles', 'public');
        }
        if ($request->hasFile('insurance_copy')) {
            $validated['insurance_copy'] = $request->file('insurance_copy')->store('vehicles', 'public');
        }

        Vehicle::create($validated);

        return redirect()->back()->with('success', 'تم إضافة الشاحنة بنجاح.');
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'plate_no' => 'required|string|unique:vehicles,plate_no,' . $vehicle->id,
            'model' => 'nullable|string',
            'type' => 'required|string',
            'driver_id' => 'nullable|exists:employees,id',
            'status' => 'required|string',
            'odometer' => 'nullable|numeric',
            'registration_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'insurance_copy' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->hasFile('registration_copy')) {
            $validated['registration_copy'] = $request->file('registration_copy')->store('vehicles', 'public');
        }
        if ($request->hasFile('insurance_copy')) {
            $validated['insurance_copy'] = $request->file('insurance_copy')->store('vehicles', 'public');
        }

        $vehicle->update($validated);

        return redirect()->back()->with('success', 'تم تحديث بيانات الشاحنة.');
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();
        return redirect()->back()->with('success', 'تم حذف الشاحنة من الأسطول.');
    }

    public function assignments()
    {
        return Inertia::render('Logistics/Vehicles/Assignments', [
            'vehicles' => Vehicle::with('driver')->get(['id', 'plate_no', 'model', 'driver_id']),
            'drivers' => Employee::where('is_driver', true)->get(['id', 'name', 'employee_no']),
        ]);
    }

    public function updateAssignment(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'driver_id' => 'nullable|exists:employees,id',
        ]);

        $vehicle->update($validated);
        return redirect()->back()->with('success', 'تم تحديث إسناد السائق للشاحنة بنجاح.');
    }
}
