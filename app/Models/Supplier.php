<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company_name',
        'tax_number',
        'address',
        'city',
        'opening_balance',
        'current_balance',
        'status',
    ];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
