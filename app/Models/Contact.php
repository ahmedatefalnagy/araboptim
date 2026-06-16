<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    protected $fillable = [
        'type',
        'is_customer',
        'is_supplier',
        'is_related_party',
        'is_main_company',
        'is_sub_client',
        'main_company_id',
        'name',
        'email',
        'phone',
        'tax_number',
        'account_id',
        'receivable_account_id',
        'payable_account_id',
        'notes',
        'is_active',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function receivableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'receivable_account_id');
    }

    public function payableAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'payable_account_id');
    }

    public function mainCompany(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'main_company_id');
    }

    public function subClients()
    {
        return $this->hasMany(Contact::class, 'main_company_id');
    }
}
