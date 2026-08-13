<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with('supplier', 'user');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('reference_no', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $purchases = $query->latest()->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::where('status', 'active')->get();
        $products = Product::where('status', 'active')->get();
        $autoRef = 'PO-' . date('Ymd') . '-' . rand(100, 999);

        return view('purchases.create', compact('suppliers', 'products', 'autoRef'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_id'     => ['required', 'exists:suppliers,id'],
            'reference_no'    => ['required', 'string', 'unique:purchases'],
            'purchase_date'   => ['required', 'date'],
            'products'        => ['required', 'array', 'min:1'],
            'products.*.id'   => ['required', 'exists:products,id'],
            'products.*.qty'  => ['required', 'integer', 'min:1'],
            'products.*.cost' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'   => ['nullable', 'numeric', 'min:0'],
            'paid_amount'     => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, &$purchase) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['products'] as $item) {
                $itemSubtotal = $item['qty'] * $item['cost'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $item['id'],
                    'unit_cost'  => $item['cost'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = $subtotal - $discount + $shipping;

            $paid = $validated['paid_amount'];
            $paymentStatus = 'paid';
            if ($paid < $grandTotal) {
                $paymentStatus = $paid > 0 ? 'partial' : 'due';
            }

            $purchase = Purchase::create([
                'reference_no'    => $validated['reference_no'],
                'supplier_id'     => $validated['supplier_id'],
                'user_id'         => auth()->id(),
                'purchase_date'   => $validated['purchase_date'],
                'subtotal'        => $subtotal,
                'discount_amount' => $discount,
                'shipping_cost'   => $shipping,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paid,
                'payment_status'  => $paymentStatus,
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $iData) {
                $purchase->items()->create($iData);

                // Auto-increment stock quantity
                $product = Product::find($iData['product_id']);
                $product->increment('stock_quantity', $iData['quantity']);
                // Update cost price to latest
                $product->update(['cost_price' => $iData['unit_cost']]);
            }

            // Update Supplier Balance Due
            $due = $grandTotal - $paid;
            if ($due > 0) {
                Supplier::find($validated['supplier_id'])->increment('current_balance', $due);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order created & product stock updated!');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('supplier', 'user', 'items.product');
        return view('purchases.show', compact('purchase'));
    }

    public function printInvoice(Purchase $purchase)
    {
        $purchase->load('supplier', 'user', 'items.product');
        return view('purchases.print', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
            }
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase record deleted and stock decremented.');
    }
}
