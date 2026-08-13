<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
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
        'credit_limit',
        'opening_balance',
        'current_balance',
        'status',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
