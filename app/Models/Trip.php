<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_no', 'route_id', 'waybill_no', 'vehicle_id', 'driver_id', 'broker_id', 'main_company_id', 'end_customer_name',
        'cargo_type', 'weight', 'container_no',
        'origin', 'destination', 'loading_site', 'discharge_site', 'doc_no',
        'status', 'etd', 'eta', 'eta_unloading', 'actual_arrival', 
        'actual_loading_start', 'actual_loading_end', 'actual_unloading_start', 'actual_unloading_end',
        'start_km', 'end_km', 'fuel_amount', 'fuel_cost', 'diesel_liters', 
        'broker_price', 'driver_commission', 'invoice_id', 'notes',
        'total_trip_budget', 'initial_diesel_amount', 'stop_count',
        'loading_invoice_path', 'delivery_invoice_path', 'is_commission_paid'
    ];

    public function route() { return $this->belongsTo(TripRoute::class, 'route_id'); }
    public function vehicle() { return $this->belongsTo(Vehicle::class); }
    public function driver() { return $this->belongsTo(Employee::class); }
    public function broker() { return $this->belongsTo(Contact::class, 'broker_id'); }
    public function mainCompany() { return $this->belongsTo(Contact::class, 'main_company_id'); }
    public function subClients() { return $this->belongsToMany(Contact::class, 'trip_sub_clients'); }
    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function events() { return $this->hasMany(TripEvent::class); }
    public function stops() { return $this->hasMany(TripStop::class); }
    public function locations() { return $this->hasMany(DriverLocation::class); }

    public function diesels()
    {
        return $this->hasMany(TripDiesel::class);
    }

    public function getTotalDieselAttribute()
    {
        return $this->diesels->sum('amount');
    }

    public function getNetTripAttribute()
    {
        return $this->total_trip_budget - $this->total_diesel;
    }
}
