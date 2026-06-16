<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\Employee;
use App\Models\Contact;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TripReportController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with(['driver', 'vehicle', 'broker']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->driver_id) {
            $query->where('driver_id', $request->driver_id);
        }
        if ($request->broker_id) {
            $query->where('broker_id', $request->broker_id);
        }
        if ($request->from_date) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $trips = $query->latest()->get();

        return Inertia::render('Logistics/Reports/TripsReport', [
            'trips' => $trips,
            'drivers' => Employee::where('is_driver', true)->get(['id', 'name']),
            'brokers' => Contact::where('type', 'customer')->get(['id', 'name']),
            'filters' => $request->only(['status', 'driver_id', 'broker_id', 'from_date', 'to_date']),
        ]);
    }
}
