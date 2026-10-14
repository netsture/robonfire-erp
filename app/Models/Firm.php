<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Firm extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'status',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function brands(): HasMany
    {
        return $this->hasMany(Brand::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function returnableMaterials(): HasMany
    {
        return $this->hasMany(ReturnableMaterial::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    /**
     * Get counts of existing child records associated with this firm.
     *
     * @return array<string, int>
     */
    public function getChildRelationsCount(): array
    {
        $counts = [
            'product' => $this->products()->count(),
            'sale' => $this->sales()->count(),
            'purchase' => $this->purchases()->count(),
            'customer' => $this->customers()->count(),
            'supplier' => $this->suppliers()->count(),
            'category' => $this->categories()->count(),
            'brand' => $this->brands()->count(),
            'project' => $this->projects()->count(),
            'returnable material' => $this->returnableMaterials()->count(),
            'stock adjustment' => $this->stockAdjustments()->count(),
        ];

        return array_filter($counts, fn ($count) => $count > 0);
    }

    /**
     * Check if the firm has any child records.
     */
    public function hasChildRecords(): bool
    {
        return !empty($this->getChildRelationsCount());
    }
}
