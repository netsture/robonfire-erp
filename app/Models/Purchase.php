<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'project_name',
        'invoice_number',
        'supplier_id',
        'user_id',
        'purchase_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'shipping_cost',
        'grand_total',
        'paid_amount',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class)->orderBy('payment_date', 'asc')->orderBy('id', 'asc');
    }

    public function recalculatePaymentStatus(): void
    {
        $totalPaid = (float) $this->payments()->sum('amount');
        $grandTotal = (float) $this->grand_total;

        if ($totalPaid >= $grandTotal && $grandTotal > 0) {
            $status = 'paid';
            $paid = $grandTotal;
        } elseif ($totalPaid > 0) {
            $status = 'partial';
            $paid = $totalPaid;
        } else {
            $status = 'unpaid';
            $paid = 0.00;
        }

        $this->update([
            'paid_amount'    => $paid,
            'payment_status' => $status,
        ]);
    }
}
