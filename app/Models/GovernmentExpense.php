<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GovernmentExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'type',
        'reference_no',
        'expense_date',
        'expiry_date',
        'amount',
        'provider',
        'status',
        'notes',
        'expense_account_id',
        'payment_account_id',
        'journal_entry_id',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function expenseAccount()
    {
        return $this->belongsTo(Account::class, 'expense_account_id');
    }

    public function paymentAccount()
    {
        return $this->belongsTo(Account::class, 'payment_account_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class);
    }
}
