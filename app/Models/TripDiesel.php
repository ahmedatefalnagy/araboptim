<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripDiesel extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id', 'amount', 'location', 'notes'];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }
}
