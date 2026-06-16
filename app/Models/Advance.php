<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advance extends Model
{
    protected $fillable = [
        'employee_id',
        'amount',
        'spent',
        'remaining',
        'status',
        'issue_date',
        'settlement_date',
        'settled_by',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'spent' => 'decimal:2',
        'remaining' => 'decimal:2',
        'issue_date' => 'date',
        'settlement_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'employee_id');
    }

    public function settledBy(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'settled_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(AdvanceTransaction::class);
    }

    public function updateRemaining()
    {
        $this->remaining = $this->amount - $this->spent;
        $this->save();
    }

    public function settle(?int $settledBy = null)
    {
        $this->status = 'settled';
        $this->settlement_date = now();
        $this->settled_by = $settledBy;
        $this->remaining = 0;
        $this->save();
    }

    public function close()
    {
        $this->status = 'closed';
        $this->save();
    }
}