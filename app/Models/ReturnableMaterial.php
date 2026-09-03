<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnableMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'return_number',
        'customer_id',
        'user_id',
        'return_date',
        'project_name',
        'return_reason',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'grand_total',
        'status',
        'notes',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnableMaterialItem::class);
    }
}
