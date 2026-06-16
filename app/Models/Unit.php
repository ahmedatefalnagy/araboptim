<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'short_name', 'is_active'];

    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
