<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name', 'trade_name', 'nif', 'address', 'city', 'province',
        'postal_code', 'country', 'phone', 'email', 'website',
        'iban', 'swift', 'logo_path',
        'invoice_series', 'rectification_series', 'quote_series',
        'invoice_counter', 'invoice_year',
        'default_vat_rate', 'irpf_applicable', 'irpf_rate',
        'invoice_template', 'primary_color', 'accent_color',
        'invoice_footer_text', 'invoice_header_notes', 'show_bank_details',
    ];

    protected function casts(): array
    {
        return [
            'irpf_applicable'   => 'boolean',
            'show_bank_details' => 'boolean',
            'default_vat_rate'  => 'decimal:2',
            'irpf_rate'         => 'decimal:2',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'company_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo_path
            ? asset('storage/' . $this->logo_path)
            : null;
    }
}
