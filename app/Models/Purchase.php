<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Purchase extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'party_id',
        'type',
        'date',
        'bill_no',
        'chalan_no',
        'vehicle',
        'reason',
        'project_name',
        'address',
        'grand_total',
        'grand_total_with_tax',
    ];

    protected $casts = [
        'date' => 'date',
        'grand_total' => 'decimal:2',
        'grand_total_with_tax' => 'decimal:2',
    ];

    public function party()
    {
        return $this->belongsTo(Party::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
