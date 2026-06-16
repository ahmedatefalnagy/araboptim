<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSettlementLine extends Model
{
    protected $fillable = [
        'settlement_id',
        'type',
        'invoice_no',
        'invoice_date',
        'vendor_name',
        'description',
        'amount',
        'is_taxable',
        'tax_rate',
        'tax_amount',
        'total_amount',
        'expense_account_id',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(AdvanceSettlement::class, 'settlement_id');
    }

    public function expenseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }
}
