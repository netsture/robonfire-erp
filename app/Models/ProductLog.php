<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Brand;

class ProductLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'firm_id',
        'product_id',
        'user_id',
        'action', // 'create', 'update', 'delete'
        'product_name',
        'product_identifier',
        'hsn_code',
        'category_name',
        'brand_name',
        'description',
        'changes',
        'ip_address',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function firm(): BelongsTo
    {
        return $this->belongsTo(Firm::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Record a product activity log entry.
     */
    public static function record(string $action, Product $product, ?array $before = null, ?array $after = null, ?string $customDesc = null): ?self
    {
        try {
            $user = Auth::user();
            $userId = $user?->id;
            $firmId = $product->firm_id ?? $user?->firm_id;

            $product->loadMissing(['category', 'brand']);
            $categoryName = $product->category->name ?? null;
            $brandName = $product->brand->name ?? null;

            $changes = null;
            $description = $customDesc;

            if ($action === 'create') {
                $description = $customDesc ?? "New product '{$product->name}' was added to inventory.";
                $changes = [
                    'attributes' => [
                        'name'               => $product->name,
                        'product_identifier' => $product->product_identifier,
                        'hsn_code'           => $product->hsn_code,
                        'category'           => $categoryName,
                        'brand'              => $brandName,
                        'unit'               => $product->unit,
                        'cost_price'         => (float) $product->cost_price,
                        'selling_price'      => (float) $product->selling_price,
                        'tax_percent'        => (float) $product->tax_percent,
                        'alert_quantity'     => (int) $product->alert_quantity,
                        'stock_quantity'     => (int) $product->stock_quantity,
                        'status'             => $product->status,
                    ]
                ];
            } elseif ($action === 'update' && is_array($before) && is_array($after)) {
                $diffBefore = [];
                $diffAfter = [];
                $changeSummaries = [];

                $fieldLabels = [
                    'name'               => 'Product Name',
                    'product_identifier' => 'Product Code',
                    'hsn_code'           => 'HSN Code',
                    'category_id'        => 'Category',
                    'brand_id'           => 'Brand',
                    'unit'               => 'Unit / Type',
                    'cost_price'         => 'Cost Price',
                    'selling_price'      => 'Selling Price',
                    'tax_percent'        => 'Tax (%)',
                    'alert_quantity'     => 'Alert Qty',
                    'stock_quantity'     => 'Stock Qty',
                    'description'        => 'Description',
                    'status'             => 'Status',
                ];

                foreach ($before as $key => $oldVal) {
                    $newVal = $after[$key] ?? null;
                    $isDifferent = false;
                    if (is_numeric($oldVal) && is_numeric($newVal)) {
                        $isDifferent = (float)$oldVal != (float)$newVal;
                    } else {
                        $isDifferent = (string)$oldVal !== (string)$newVal;
                    }

                    if ($isDifferent) {
                        $label = $fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key));

                        $displayOld = $oldVal;
                        $displayNew = $newVal;

                        if ($key === 'category_id') {
                            $oldCat = Category::find($oldVal);
                            $newCat = Category::find($newVal);
                            $displayOld = $oldCat->name ?? $oldVal;
                            $displayNew = $newCat->name ?? $newVal;
                        } elseif ($key === 'brand_id') {
                            $oldBrand = Brand::find($oldVal);
                            $newBrand = Brand::find($newVal);
                            $displayOld = $oldBrand->name ?? $oldVal;
                            $displayNew = $newBrand->name ?? $newVal;
                        } elseif (in_array($key, ['cost_price', 'selling_price'])) {
                            $displayOld = '₹' . number_format((float)$oldVal, 2);
                            $displayNew = '₹' . number_format((float)$newVal, 2);
                        } elseif ($key === 'tax_percent') {
                            $displayOld = (float)$oldVal . '%';
                            $displayNew = (float)$newVal . '%';
                        }

                        $diffBefore[$key] = [
                            'label' => $label,
                            'value' => $displayOld,
                        ];
                        $diffAfter[$key] = [
                            'label' => $label,
                            'value' => $displayNew,
                        ];
                        $changeSummaries[] = "{$label}: {$displayOld} → {$displayNew}";
                    }
                }

                if (empty($changeSummaries)) {
                    $description = "Product '{$product->name}' details updated.";
                } else {
                    $description = $customDesc ?? implode(', ', $changeSummaries);
                }

                $changes = [
                    'before' => $diffBefore,
                    'after'  => $diffAfter,
                ];
            } elseif ($action === 'delete') {
                $description = $customDesc ?? "Product '{$product->name}' was deleted from inventory.";
                $changes = [
                    'attributes' => [
                        'name'               => $product->name,
                        'product_identifier' => $product->product_identifier,
                        'hsn_code'           => $product->hsn_code,
                        'category'           => $categoryName,
                        'brand'              => $brandName,
                        'cost_price'         => (float) $product->cost_price,
                        'selling_price'      => (float) $product->selling_price,
                        'tax_percent'        => (float) $product->tax_percent,
                        'stock_quantity'     => (int) $product->stock_quantity,
                        'status'             => $product->status,
                    ]
                ];
            }

            return self::create([
                'firm_id'            => $firmId,
                'product_id'         => $action === 'delete' ? null : $product->id,
                'user_id'            => $userId,
                'action'             => $action,
                'product_name'       => $product->name,
                'product_identifier' => $product->product_identifier,
                'hsn_code'           => $product->hsn_code,
                'category_name'      => $categoryName,
                'brand_name'         => $brandName,
                'description'        => $description,
                'changes'            => $changes,
                'ip_address'         => request()?->ip(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Failed to record ProductLog: ' . $e->getMessage());
            return null;
        }
    }
}
