<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'status',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function parties()
    {
        return $this->hasMany(Party::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }
}
