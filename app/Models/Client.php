<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'type', 'name', 'trade_name', 'nif',
        'email', 'phone', 'website',
        'address', 'city', 'province', 'postal_code', 'country',
        'vat_exempt', 'irpf_applicable', 'irpf_rate', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'vat_exempt'       => 'boolean',
            'irpf_applicable'  => 'boolean',
            'irpf_rate'        => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}
