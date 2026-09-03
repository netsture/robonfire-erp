<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnableMaterialItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'returnable_material_id',
        'product_id',
        'unit_price',
        'quantity',
        'subtotal',
    ];

    public function returnableMaterial(): BelongsTo
    {
        return $this->belongsTo(ReturnableMaterial::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
