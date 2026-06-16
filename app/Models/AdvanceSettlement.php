<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdvanceSettlement extends Model
{
    protected $fillable = [
        'advance_id',
        'settlement_no',
        'settlement_date',
        'status',
        'total_expenses',
        'total_tax',
        'total_amount',
        'refund_amount',
        'additional_amount',
        'journal_entry_id',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'settlement_date' => 'date',
        'total_expenses' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'refund_amount' => 'decimal:2',
        'additional_amount' => 'decimal:2',
    ];

    public function advance(): BelongsTo
    {
        return $this->belongsTo(EmployeeAdvance::class, 'advance_id');
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AdvanceSettlementLine::class, 'settlement_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
