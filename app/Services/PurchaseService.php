<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseService
{
    /**
     * Create a new purchase/chalan record with its line items.
     */
    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {
            // 1. Create the purchase document skeleton
            $purchase = Purchase::create([
                'party_id' => $data['party_id'],
                'type' => $data['type'],
                'date' => $data['date'],
                'bill_no' => $data['bill_no'] ?? null,
                'chalan_no' => $data['chalan_no'] ?? null,
                'vehicle' => $data['vehicle'] ?? null,
                'reason' => $data['reason'] ?? null,
                'project_name' => $data['project_name'] ?? null,
                'address' => $data['address'] ?? null,
                'grand_total' => 0.00,
                'grand_total_with_tax' => 0.00,
            ]);

            $grandTotal = 0.00;
            $grandTotalWithTax = 0.00;

            // 2. Loop and save items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = (float)($item['quantity'] ?? 0);
                    $rate = (float)($item['rate'] ?? 0);
                    $taxPercent = (float)($item['tax_percent'] ?? 0);

                    $total = $qty * $rate;
                    $totalWithTax = $total + ($total * $taxPercent / 100.0);

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_name' => $item['product_name'],
                        'brand' => $item['brand'] ?? null,
                        'category' => $item['category'] ?? null,
                        'hsn_code' => $item['hsn_code'] ?? null,
                        'quantity' => $qty,
                        'type' => $item['type'] ?? null,
                        'rate' => $rate,
                        'tax_percent' => $taxPercent,
                        'total' => $total,
                        'total_with_tax' => $totalWithTax,
                    ]);

                    $grandTotal += $total;
                    $grandTotalWithTax += $totalWithTax;
                }
            }

            // 3. Update parent with calculated totals
            $purchase->update([
                'grand_total' => $grandTotal,
                'grand_total_with_tax' => $grandTotalWithTax,
            ]);

            return $purchase;
        });
    }

    /**
     * Update an existing purchase/chalan record and replace/update line items.
     */
    public function updatePurchase(Purchase $purchase, array $data): Purchase
    {
        return DB::transaction(function () use ($purchase, $data) {
            // 1. Update parent details
            $purchase->update([
                'party_id' => $data['party_id'],
                'date' => $data['date'],
                'bill_no' => $data['bill_no'] ?? null,
                'chalan_no' => $data['chalan_no'] ?? null,
                'vehicle' => $data['vehicle'] ?? null,
                'reason' => $data['reason'] ?? null,
                'project_name' => $data['project_name'] ?? null,
                'address' => $data['address'] ?? null,
            ]);

            // 2. Delete old items
            $purchase->items()->delete();

            $grandTotal = 0.00;
            $grandTotalWithTax = 0.00;

            // 3. Re-insert items
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty = (float)($item['quantity'] ?? 0);
                    $rate = (float)($item['rate'] ?? 0);
                    $taxPercent = (float)($item['tax_percent'] ?? 0);

                    $total = $qty * $rate;
                    $totalWithTax = $total + ($total * $taxPercent / 100.0);

                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_name' => $item['product_name'],
                        'brand' => $item['brand'] ?? null,
                        'category' => $item['category'] ?? null,
                        'hsn_code' => $item['hsn_code'] ?? null,
                        'quantity' => $qty,
                        'type' => $item['type'] ?? null,
                        'rate' => $rate,
                        'tax_percent' => $taxPercent,
                        'total' => $total,
                        'total_with_tax' => $totalWithTax,
                    ]);

                    $grandTotal += $total;
                    $grandTotalWithTax += $totalWithTax;
                }
            }

            // 4. Update parent totals
            $purchase->update([
                'grand_total' => $grandTotal,
                'grand_total_with_tax' => $grandTotalWithTax,
            ]);

            return $purchase;
        });
    }

    /**
     * Delete a purchase log and its cascading items.
     */
    public function deletePurchase(Purchase $purchase): bool
    {
        return DB::transaction(function () use ($purchase) {
            // Cascade delete will handle the items in the DB, but we do it explicitly just in case
            $purchase->items()->delete();
            return $purchase->delete();
        });
    }
}
