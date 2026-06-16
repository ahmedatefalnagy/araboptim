<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\MaintenanceRequest;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverPortalController extends Controller
{
    public function dashboard()
    {
        $employeeId = auth()->user()->employee_id;
        
        if (!$employeeId) {
            return redirect()->route('dashboard')->with('error', 'هذا الحساب غير مرتبط بملف موظف/سائق.');
        }

        $currentTrip = Trip::where('driver_id', $employeeId)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest()
            ->first();

        $stats = [
            'completed_trips' => Trip::where('driver_id', $employeeId)->where('status', 'completed')->count(),
            'total_commissions' => Trip::where('driver_id', $employeeId)->where('status', 'completed')->sum('driver_commission'),
            'pending_maintenance' => MaintenanceRequest::where('driver_id', $employeeId)->where('status', 'pending')->count(),
        ];

        return Inertia::render('Logistics/Driver/Dashboard', [
            'currentTrip' => $currentTrip,
            'stats' => $stats,
        ]);
    }

    public function trips()
    {
        $employeeId = auth()->user()->employee_id;
        $trips = Trip::where('driver_id', $employeeId)->latest()->paginate(10);

        return Inertia::render('Logistics/Driver/Trips', [
            'trips' => $trips
        ]);
    }

    // This can be used as an API endpoint for the Flutter App later
    public function apiData(Request $request)
    {
        $employeeId = auth()->user()->employee_id;
        
        return response()->json([
            'driver_name' => auth()->user()->name,
            'current_trip' => Trip::where('driver_id', $employeeId)->where('status', 'transit')->first(),
            'total_earnings' => Trip::where('driver_id', $employeeId)->where('status', 'completed')->sum('driver_commission'),
            'maintenance_requests' => MaintenanceRequest::where('driver_id', $employeeId)->latest()->get(),
        ]);
    }
}
