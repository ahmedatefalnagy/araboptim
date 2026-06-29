<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_no',
        'type',
        'contact_id',
        'invoice_date',
        'due_date',
        'total_base',
        'total_tax',
        'total_amount',
        'base_account_id',
        'tax_account_id',
        'journal_entry_id',
        'notes',
        'attachment_path',
        'created_by',
        'parent_document_id',
        'cost_center_id',
        'payment_mode',
        'payment_account_id',
        'qr_code_base64',
        'xml_content',
        'xml_uuid',
        'zatca_hash',
        'zatca_status',
        'previous_hash',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function parentDocument(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'parent_document_id');
    }

    public function childDocuments()
    {
        return $this->hasMany(Invoice::class, 'parent_document_id');
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function baseAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'base_account_id');
    }

    public function taxAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'tax_account_id');
    }

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lines()
    {
        return $this->hasMany(InvoiceLine::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
