<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'name',
        'slug',
        'description',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get counts of existing child records associated with this category.
     *
     * @return array<string, int>
     */
    public function getChildRelationsCount(): array
    {
        $counts = [
            'product' => $this->products()->count(),
        ];

        return array_filter($counts, fn ($count) => $count > 0);
    }

    /**
     * Check if category has any child records.
     */
    public function hasChildRecords(): bool
    {
        return !empty($this->getChildRelationsCount());
    }
}
