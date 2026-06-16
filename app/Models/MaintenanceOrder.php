<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'vehicle_id', 'driver_id', 'status', 'type', 
        'current_odometer', 'issue_description', 'total_parts_cost', 'labor_cost'
    ];

    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class, 'driver_id'); }
    public function items() { return $this->hasMany(MaintenanceOrderItem::class); }
}
