<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_order_id', 'item_id', 'quantity', 'unit_price'
    ];

    public function order() { return $this->belongsTo(MaintenanceOrder::class, 'maintenance_order_id'); }
    public function item() { return $this->belongsTo(Item::class); }
}
