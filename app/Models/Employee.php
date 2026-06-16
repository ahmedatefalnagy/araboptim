<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $guarded = [];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    protected $casts = [
        'is_driver' => 'boolean',
        'hire_date' => 'date',
        'basic_salary' => 'float',
        'commission' => 'float',
        'birth_date' => 'date',
        'iqama_expiry' => 'date',
        'license_expiry' => 'date',
        'authorization_expiry' => 'date',
        'work_card_expiry' => 'date',
        'driver_card_expiry' => 'date',
        'transport_license_expiry' => 'date',
    ];

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    public function advances()
    {
        return $this->hasMany(EmployeeAdvance::class);
    }
}
