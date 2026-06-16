<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeAdvance extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'amount' => 'decimal:2',
        'deducted_amount' => 'decimal:2',
        'remaining' => 'decimal:2',
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(AdvanceSettlement::class, 'advance_id');
    }

    public function getStatusAttribute()
    {
        if ($this->remaining <= 0) {
            return 'settled';
        }
        if ($this->deducted_amount > 0) {
            return 'partially_settled';
        }
        return 'open';
    }

    public function getRemainingAttribute()
    {
        return $this->amount - $this->deducted_amount;
    }
}