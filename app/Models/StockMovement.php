<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'warehouse_id',
        'type',
        'quantity',
        'reference_type',
        'reference_id',
        'movement_date',
        'cost_per_unit',
        'notes',
    ];

    protected $casts = [
        'movement_date' => 'date',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
