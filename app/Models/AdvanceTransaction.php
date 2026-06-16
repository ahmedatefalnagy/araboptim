<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceTransaction extends Model
{
    protected $table = 'advance_transactions';

    protected $fillable = [
        'advance_id',
        'type',
        'amount',
        'description',
        'reference_type',
        'reference_id',
        'is_taxable',
        'tax_rate',
        'tax_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function advance(): BelongsTo
    {
        return $this->belongsTo(Advance::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}