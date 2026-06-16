<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'account_type_id',
        'level',
        'is_postable',
        'is_active',
        'report_group',
    ];

    protected $casts = [
        'is_postable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Account::class, 'parent_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AccountType::class, 'account_type_id');
    }
}