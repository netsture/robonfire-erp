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
        'name',
        'email',
        'phone',
        'company_name',
        'tax_number',
        'address',
        'city',
        'current_balance',
        'status',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}
