<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'sku',
        'barcode',
        'type',
        'price',
        'cost_price',
        'tax_rate',
        'unit_id',
        'category_id',
        'track_inventory',
        'alert_quantity',
        'is_active',
        'description',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function category()
    {
        return $this->belongsTo(ItemCategory::class, 'category_id');
    }

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
