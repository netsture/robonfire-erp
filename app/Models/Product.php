<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'name',
        'product_identifier',
        'hsn_code',
        'category_id',
        'brand_id',
        'unit',
        'cost_price',
        'selling_price',
        'tax_percent',
        'alert_quantity',
        'stock_quantity',
        'description',
        'image',
        'status',
    ];

    public static function generateUniqueIdentifier(): string
    {
        do {
            $code = (string) mt_rand(10000, 99999);
        } while (static::where('product_identifier', $code)->exists());

        return $code;
    }

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->alert_quantity;
    }
}
