<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
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

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }
}
