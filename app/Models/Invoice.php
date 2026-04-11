<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'company_id', 'client_id',
        'series', 'number', 'full_number', 'type', 'status',
        'issue_date', 'due_date', 'service_date',
        'client_name', 'client_nif', 'client_address', 'client_city',
        'client_postal_code', 'client_country', 'company_snapshot',
        'subtotal', 'vat_amount', 'irpf_amount', 'total', 'vat_breakdown',
        'payment_method', 'payment_terms', 'paid_at',
        'notes', 'internal_notes', 'currency', 'pdf_path',
        'related_invoice_id',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'       => 'date',
            'due_date'         => 'date',
            'service_date'     => 'date',
            'paid_at'          => 'datetime',
            'subtotal'         => 'decimal:2',
            'vat_amount'       => 'decimal:2',
            'irpf_amount'      => 'decimal:2',
            'total'            => 'decimal:2',
            'vat_breakdown'    => 'array',
            'company_snapshot' => 'array',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(InvoiceLine::class)->orderBy('position');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function relatedInvoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'related_invoice_id');
    }

    public function creditNotes(): HasMany
    {
        return $this->hasMany(Invoice::class, 'related_invoice_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft'     => 'Borrador',
            'sent'      => 'Enviada',
            'paid'      => 'Pagada',
            'overdue'   => 'Vencida',
            'cancelled' => 'Cancelada',
            default     => $this->status,
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'invoice'     => 'Factura',
            'proforma'    => 'Proforma',
            'quote'       => 'Presupuesto',
            'credit_note' => 'Factura Rectificativa',
            default       => $this->type,
        };
    }
}
