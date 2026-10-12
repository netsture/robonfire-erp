<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'company_name',
        'email',
        'phone',
        'gst_number',
        'address',
        'status',
    ];

    public function getNameAttribute()
    {
        return $this->attributes['company_name'] ?? null;
    }

    public function getTaxNumberAttribute()
    {
        return $this->attributes['gst_number'] ?? null;
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function returnableMaterials(): HasMany
    {
        return $this->hasMany(ReturnableMaterial::class);
    }

    /**
     * Get counts of existing child records associated with this customer.
     *
     * @return array<string, int>
     */
    public function getChildRelationsCount(): array
    {
        $counts = [
            'sale' => $this->sales()->count(),
            'returnable material' => $this->returnableMaterials()->count(),
        ];

        return array_filter($counts, fn ($count) => $count > 0);
    }

    /**
     * Check if customer has any child records.
     */
    public function hasChildRecords(): bool
    {
        return !empty($this->getChildRelationsCount());
    }
}
