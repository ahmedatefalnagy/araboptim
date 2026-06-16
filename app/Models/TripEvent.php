<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'event_type', 'reason', 'event_time', 'location', 'notes'
    ];

    public function trip() { return $this->belongsTo(Trip::class); }
}
