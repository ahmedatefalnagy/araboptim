<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleMaintenanceLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'maintenance_type', 'maintenance_date', 
        'odometer_reading', 'description', 'cost', 'voucher_id'
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function voucher() { return $this->belongsTo(Voucher::class); }
}
