<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_name',
        'brand',
        'category',
        'hsn_code',
        'quantity',
        'type',
        'rate',
        'tax_percent',
        'total',
        'total_with_tax',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate' => 'decimal:2',
        'tax_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'total_with_tax' => 'decimal:2',
    ];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }
}
