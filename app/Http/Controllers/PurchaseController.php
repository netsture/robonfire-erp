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
        $user = auth()->user();
        $query = Purchase::with('supplier', 'user', 'firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $purchases = $query->latest()->paginate(10);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $user = auth()->user();
        $firmId = $user->firm_id ?? 1;

        $suppliersQuery = Supplier::where('status', 'active');
        $productsQuery = Product::where('status', 'active');

        if (!$user->isSuperAdmin()) {
            $suppliersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
        }

        $suppliers = $suppliersQuery->get();
        $products = $productsQuery->get();

        // Calculate next firm-wise sequence for auto IDs
        $firmSeq = Purchase::where('firm_id', $firmId)->count() + 1;
        $autoRef = 'REF-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);
        $autoInv = 'PINV-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);

        return view('purchases.create', compact('suppliers', 'products', 'autoRef', 'autoInv', 'firmId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'supplier_id'     => ['required', 'exists:suppliers,id'],
            'reference_no'    => ['required', 'string'],
            'invoice_number'  => ['required', 'string'],
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

        // Firm-wise uniqueness check
        $existsRef = Purchase::where('firm_id', $firmId)->where('reference_no', $validated['reference_no'])->exists();
        if ($existsRef) {
            return back()->withErrors(['reference_no' => 'Reference number already exists for this firm.'])->withInput();
        }

        $existsInv = Purchase::where('firm_id', $firmId)->where('invoice_number', $validated['invoice_number'])->exists();
        if ($existsInv) {
            return back()->withErrors(['invoice_number' => 'Invoice number already exists for this firm.'])->withInput();
        }

        DB::transaction(function () use ($validated, $firmId, $user, &$purchase) {
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
                'firm_id'         => $firmId,
                'reference_no'    => $validated['reference_no'],
                'invoice_number'  => $validated['invoice_number'],
                'supplier_id'     => $validated['supplier_id'],
                'user_id'         => $user->id,
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
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $purchase->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $purchase->load('supplier', 'user', 'items.product', 'firm');
        return view('purchases.show', compact('purchase'));
    }

    public function printInvoice(Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $purchase->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $purchase->load('supplier', 'user', 'items.product', 'firm');
        return view('purchases.print', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $purchase->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
            }
            $purchase->delete();
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase record deleted and stock decremented.');
    }
}
