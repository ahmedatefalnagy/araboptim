<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate_no', 'model', 'type', 'driver_id', 'status', 'odometer', 
        'insurance_expiry', 'registration_expiry', 'is_active',
        'registration_copy', 'insurance_copy',
        'last_oil_change_km', 'next_oil_change_km', 'oil_change_interval_km',
        'last_tire_change_km', 'tire_change_interval_km'
    ];

    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }

    public function currentTrip()
    {
        return $this->hasOne(Trip::class)->whereNotIn('status', ['completed', 'cancelled'])->latest();
    }
}
