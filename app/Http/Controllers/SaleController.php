<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Sale::with('customer', 'user', 'firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
        }

        $sales = $query->latest()->paginate(10);

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $user = auth()->user();
        $firmId = $user->firm_id ?? 1;

        $customersQuery = Customer::where('status', 'active');
        $productsQuery = Product::where('status', 'active')->where('stock_quantity', '>', 0);

        if (!$user->isSuperAdmin()) {
            $customersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
        }

        $customers = $customersQuery->get();
        $products = $productsQuery->get();

        $firmSeq = Sale::where('firm_id', $firmId)->count() + 1;
        $autoInvoice = 'INV-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);

        return view('sales.create', compact('customers', 'products', 'autoInvoice'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'customer_id'     => ['required', 'exists:customers,id'],
            'invoice_number'  => ['required', 'string'],
            'sale_date'       => ['required', 'date'],
            'products'        => ['required', 'array', 'min:1'],
            'products.*.id'   => ['required', 'exists:products,id'],
            'products.*.qty'  => ['required', 'integer', 'min:1'],
            'products.*.price'=> ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'   => ['nullable', 'numeric', 'min:0'],
            'paid_amount'     => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ]);

        // Check stock availability first
        foreach ($validated['products'] as $item) {
            $product = Product::find($item['id']);
            if ($product->stock_quantity < $item['qty']) {
                return back()->with('error', 'Insufficient stock for "' . $product->name . '". Only ' . $product->stock_quantity . ' available.');
            }
        }

        DB::transaction(function () use ($validated, $firmId, $user, &$sale) {
            $subtotal = 0;
            $itemsData = [];

            foreach ($validated['products'] as $item) {
                $itemSubtotal = $item['qty'] * $item['price'];
                $subtotal += $itemSubtotal;

                $itemsData[] = [
                    'product_id' => $item['id'],
                    'unit_price' => $item['price'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $tax = $validated['tax_amount'] ?? 0;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = $subtotal - $discount + $tax + $shipping;

            $paid = $validated['paid_amount'];
            $paymentStatus = 'paid';
            if ($paid < $grandTotal) {
                $paymentStatus = $paid > 0 ? 'partial' : 'due';
            }

            $sale = Sale::create([
                'firm_id'         => $firmId,
                'invoice_number'  => $validated['invoice_number'],
                'customer_id'     => $validated['customer_id'],
                'user_id'         => $user->id,
                'sale_date'       => $validated['sale_date'],
                'subtotal'        => $subtotal,
                'tax_amount'      => $tax,
                'discount_amount' => $discount,
                'shipping_cost'   => $shipping,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paid,
                'payment_status'  => $paymentStatus,
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $iData) {
                $sale->items()->create($iData);

                // Auto-deduct stock quantity
                Product::where('id', $iData['product_id'])->decrement('stock_quantity', $iData['quantity']);
            }

            // Update Customer Balance Due
            $due = $grandTotal - $paid;
            if ($due > 0) {
                Customer::find($validated['customer_id'])->increment('current_balance', $due);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sales Order #' . $sale->invoice_number . ' completed & stock updated!');
    }

    public function show(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product', 'firm');
        return view('sales.show', compact('sale'));
    }

    public function printInvoice(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product', 'firm');
        return view('sales.invoice', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)->increment('stock_quantity', $item->quantity);
            }
            $sale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Sales order deleted and stock restored.');
    }
}
