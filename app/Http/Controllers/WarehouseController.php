<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function quickStore(Request $request)
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

        $warehouse = Warehouse::create($validated);

        return response()->json([
            'success' => true,
            'warehouse' => $warehouse
        ]);
    }
}
