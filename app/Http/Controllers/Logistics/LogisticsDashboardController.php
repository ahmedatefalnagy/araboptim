<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\Trip;
use App\Models\MaintenanceOrder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class LogisticsDashboardController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::with(['driver', 'currentTrip'])->get();

        // Monthly Operational Financials
        $monthTrips = Trip::where('created_at', '>=', now()->startOfMonth())->get();
        
        $revenue = $monthTrips->sum('broker_price');
        
        $maintenanceCosts = MaintenanceOrder::where('created_at', '>=', now()->startOfMonth())
            ->where('status', 'completed')
            ->sum(DB::raw('total_parts_cost + labor_cost'));

        $costs = $monthTrips->sum('fuel_cost') + $monthTrips->sum('driver_commission') + $maintenanceCosts;
        
        $cargoDistribution = $monthTrips->groupBy('cargo_type')
            ->map(fn($group) => $group->count())
            ->toArray();

        $topRoutes = $monthTrips->groupBy(fn($t) => $t->origin . ' - ' . $t->destination)
            ->map(fn($group) => $group->count())
            ->sortByDesc(fn($count) => $count)
            ->take(3)
            ->toArray();

        // Top Spare Parts (Frequently Purchased/Used)
        $topParts = \DB::table('maintenance_order_items')
            ->join('items', 'maintenance_order_items.item_id', '=', 'items.id')
            ->select('items.name', \DB::raw('SUM(maintenance_order_items.quantity) as total_qty'))
            ->groupBy('items.id', 'items.name')
            ->orderByDesc('total_qty')
            ->take(5)
            ->get();

        $stats = [
            'total' => $vehicles->count(),
            'available' => $vehicles->where('status', 'available')->count(),
            'maintenance' => $vehicles->whereIn('status', ['maintenance', 'breakdown'])->count(),
            'in_trip' => $vehicles->where('status', 'in_trip')->count(),
            
            // Intelligence: Analysis of current active trips
            'loading' => 0,
            'on_road' => 0,
            'stopped' => 0,
            'waiting_unload' => 0,
            'oil_alerts' => 0,
            'tire_alerts' => 0,
            'monthly_completed' => $monthTrips->where('status', 'completed')->count(),
            'monthly_revenue' => $revenue,
            'monthly_costs' => $costs,
            'cargo_distribution' => $cargoDistribution,
            'top_routes' => $topRoutes,
            'top_parts' => $topParts,
        ];

        foreach ($vehicles as $vehicle) {
            // AI Predictive Maintenance Logic
            $daysSinceOil = $vehicle->updated_at->diffInDays(now()) ?: 1;
            $kmSinceOil = $vehicle->odometer - $vehicle->last_oil_change_km;
            $avgKmPerDay = $kmSinceOil / $daysSinceOil;
            
            $remainingKm = ($vehicle->last_oil_change_km + $vehicle->oil_change_interval_km) - $vehicle->odometer;
            $predictedDays = $avgKmPerDay > 0 ? floor($remainingKm / $avgKmPerDay) : 30;
            
            $vehicle->ai_prediction = [
                'days_left' => $predictedDays,
                'status' => $predictedDays < 7 ? 'critical' : ($predictedDays < 15 ? 'warning' : 'healthy'),
                'avg_daily_km' => round($avgKmPerDay, 1)
            ];

            // Maintenance Alerts Logic
            if ($vehicle->odometer >= ($vehicle->last_oil_change_km + $vehicle->oil_change_interval_km - 500)) {
                $stats['oil_alerts']++;
            }
            if ($vehicle->odometer >= ($vehicle->last_tire_change_km + $vehicle->tire_change_interval_km - 2000)) {
                $stats['tire_alerts']++;
            }

            if ($vehicle->status === 'in_trip' && $vehicle->currentTrip) {
                $trip = $vehicle->currentTrip;
                
                if ($trip->status === 'loading') {
                    $stats['loading']++;
                } elseif ($trip->status === 'transit') {
                    $stats['on_road']++;
                    
                    // Check if there are active stop events in the last 12 hours
                    $hasStop = $trip->events()->where('event_type', 'stop')
                        ->where('created_at', '>=', now()->subHours(12))
                        ->exists();
                    if ($hasStop) {
                        $stats['stopped']++;
                    }
                } elseif ($trip->status === 'at_destination') {
                    $stats['waiting_unload']++;
                }
            }
        }

        return Inertia::render('Logistics/Dashboard', [
            'stats' => $stats,
            'vehicles' => $vehicles->map(function($v) {
                return [
                    'id' => $v->id,
                    'plate_no' => $v->plate_no,
                    'status' => $v->status,
                    'driver_name' => $v->driver->name ?? 'بدون سائق',
                    'trip_status' => $v->currentTrip->status ?? null,
                    'origin' => $v->currentTrip->origin ?? null,
                    'destination' => $v->currentTrip->destination ?? null,
                    'trip_id' => $v->currentTrip->id ?? null,
                    'odometer' => $v->odometer,
                    'needs_oil' => $v->odometer >= ($v->last_oil_change_km + $v->oil_change_interval_km - 500),
                    'needs_tires' => $v->odometer >= ($v->last_tire_change_km + $v->tire_change_interval_km - 2000),
                    'ai_prediction' => $v->ai_prediction,
                ];
            })
        ]);
    }
}
