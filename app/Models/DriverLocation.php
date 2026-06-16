<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
        'latitude',
        'longitude',
        'speed',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'double',
        'longitude' => 'double',
        'speed' => 'double',
        'recorded_at' => 'datetime',
    ];

    public function trip(): BelongsTo
    {
        return $this->belongsTo(Trip::class);
    }
}
