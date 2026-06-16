<?php

namespace App\Http\Controllers\Logistics;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\MaintenanceOrder;
use App\Models\MaintenanceOrderItem;
use App\Models\Item;
use App\Models\StockMovement;
use App\Models\InventoryStock;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class WorkshopController extends Controller
{
    public function index()
    {
        // Vehicles needing oil change (Smart Alerts)
        $oilAlerts = Vehicle::whereRaw('odometer >= (last_oil_change_km + oil_change_interval_km - 500)')->get();
        $tireAlerts = Vehicle::whereRaw('odometer >= (last_tire_change_km + tire_change_interval_km - 2000)')->get();

        return Inertia::render('Logistics/Workshop/Index', [
            'stats' => [
                'pending_orders' => MaintenanceOrder::whereIn('status', ['draft', 'pending_parts'])->count() ?: 0,
                'in_progress' => MaintenanceOrder::where('status', 'in_progress')->count() ?: 0,
                'oil_alerts' => $oilAlerts->count() ?: 0,
                'tire_alerts' => $tireAlerts->count() ?: 0,
            ],
            'recent_orders' => MaintenanceOrder::with(['vehicle', 'driver'])->latest()->take(10)->get() ?: []
        ]);
    }

    public function orders()
    {
        return Inertia::render('Logistics/Workshop/Orders', [
            'orders' => MaintenanceOrder::with(['vehicle', 'driver', 'items.item'])->latest()->get(),
            'vehicles' => Vehicle::all(['id', 'plate_no']),
            'items' => Item::all(['id', 'name']), // For spare parts selection
        ]);
    }

    public function storeOrder(Request $request)
    {
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'type' => 'required|in:routine,emergency,preventive',
            'issue_description' => 'required|string',
            'current_odometer' => 'required|numeric',
            'labor_cost' => 'nullable|numeric',
        ]);

        $vehicle = Vehicle::find($validated['vehicle_id']);
        $validated['order_no'] = 'WORK-' . date('Ymd') . '-' . rand(100, 999);
        $validated['driver_id'] = $vehicle->driver_id;
        
        MaintenanceOrder::create($validated);

        return redirect()->back()->with('success', 'تم فتح أمر الصيانة بنجاح.');
    }

    public function addPart(Request $request, MaintenanceOrder $order)
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'required|numeric|min:0',
            'warehouse_id' => 'required|exists:warehouses,id',
        ]);

        DB::beginTransaction();
        try {
            // 1. Record the part in the maintenance order
            $orderItem = $order->items()->create([
                'item_id' => $validated['item_id'],
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'],
            ]);

            // 2. Issue from Warehouse (Stock Out)
            StockMovement::create([
                'item_id' => $validated['item_id'],
                'warehouse_id' => $validated['warehouse_id'],
                'type' => 'out',
                'quantity' => $validated['quantity'],
                'reference_type' => 'MaintenanceOrder',
                'reference_id' => $order->id,
                'movement_date' => now(),
                'cost_per_unit' => $validated['unit_price'],
                'notes' => 'صرف قطعة غيار لأمر صيانة: ' . $order->order_no,
            ]);

            // 3. Update Inventory Stock (Decrement)
            $stock = InventoryStock::where('item_id', $validated['item_id'])
                ->where('warehouse_id', $validated['warehouse_id'])
                ->first();
            
            if ($stock) {
                $stock->decrement('quantity', $validated['quantity']);
            }

            // 4. Update total cost of order
            $order->increment('total_parts_cost', $validated['quantity'] * $validated['unit_price']);

            DB::commit();
            return redirect()->back()->with('success', 'تم صرف قطعة الغيار وتحديث المخزون بنجاح.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['message' => 'فشل في صرف القطعة: ' . $e->getMessage()]);
        }
    }

    public function completeOrder(MaintenanceOrder $order)
    {
        $order->update(['status' => 'completed']);
        
        // If it was an oil change, update the vehicle milestones
        if (str_contains(strtolower($order->issue_description), 'زيت') || str_contains(strtolower($order->issue_description), 'oil')) {
            $order->vehicle->update([
                'last_oil_change_km' => $order->current_odometer,
                'next_oil_change_km' => $order->current_odometer + $order->vehicle->oil_change_interval_km,
                'odometer' => $order->current_odometer
            ]);
        }

        return redirect()->back()->with('success', 'تم إكمال أمر الصيانة وتحديث سجلات الشاحنة.');
    }
}
