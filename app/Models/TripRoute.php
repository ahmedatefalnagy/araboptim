<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripRoute extends Model
{
    protected $fillable = [
        'name', 'origin', 'destination', 'distance_km', 
        'standard_budget', 'standard_diesel_budget', 'standard_driver_commission', 
        'is_active'
    ];

    public function trips()
    {
        return $this->hasMany(Trip::class, 'route_id');
    }
}
