<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpeningBalance extends Model
{
    protected $fillable = [
        'fiscal_year_id',
        'account_id',
        'debit',
        'credit',
    ];

    public function fiscalYear(): BelongsTo
    {
        return $this->belongsTo(FiscalYear::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}