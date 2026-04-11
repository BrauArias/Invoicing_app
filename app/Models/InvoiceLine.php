<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceLine extends Model
{
    protected $fillable = [
        'invoice_id', 'product_id', 'position',
        'description', 'quantity', 'unit', 'unit_price',
        'discount', 'vat_rate', 'subtotal', 'vat_amount', 'total',
    ];

    protected function casts(): array
    {
        return [
            'quantity'   => 'decimal:3',
            'unit_price' => 'decimal:2',
            'discount'   => 'decimal:2',
            'vat_rate'   => 'decimal:2',
            'subtotal'   => 'decimal:2',
            'vat_amount' => 'decimal:2',
            'total'      => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
