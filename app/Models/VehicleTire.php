<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleTire extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'position', 'unit_type', 'serial_no', 'brand',
        'purchase_date', 'warranty_months', 'expected_life_km', 
        'installation_km', 'status'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
}
