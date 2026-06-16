<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripStop extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id', 'location', 'reason', 'start_time', 'end_time', 'notes'
    ];

    public function trip() { return $this->belongsTo(Trip::class); }
}
