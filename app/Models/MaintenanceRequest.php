<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id', 'driver_id', 'category', 'issue_description', 
        'status', 'stock_status', 'estimated_cost', 'scheduled_at', 
        'completed_at', 'admin_notes'
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }
}
