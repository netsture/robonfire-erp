<?php

namespace App\Http\Controllers;

use App\Models\ReturnableMaterial;
use App\Models\ReturnableMaterialItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnableMaterialController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ReturnableMaterial::with('customer', 'user', 'firm');

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('return_reason', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $returns = $query->latest()->paginate(10);
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('returnable.index', compact('returns', 'firms'));
    }

    public function create()
    {
        $user = auth()->user();
        $firmId = $user->firm_id ?? 1;

        $customersQuery = Customer::where('status', 'active');
        $productsQuery = Product::with(['category', 'brand'])->where('status', 'active');
        $categoriesQuery = Category::query();

        if (!$user->isSuperAdmin()) {
            $customersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
            $categoriesQuery->where('firm_id', $firmId);
        }

        $customers = $customersQuery->get();
        $products = $productsQuery->get();
        $categories = $categoriesQuery->get();

        $firmSeq = ReturnableMaterial::where('firm_id', $firmId)->count() + 1;
        $autoReturnNumber = 'RET-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);

        return view('returnable.create', compact('customers', 'products', 'categories', 'autoReturnNumber'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'customer_id'     => ['required', 'exists:customers,id'],
            'return_number'   => ['required', 'string'],
            'project_name'    => ['required', 'string'],
            'return_reason'   => ['required', 'string'],
            'return_date'     => ['required', 'date'],
            'products'        => ['required', 'array', 'min:1'],
            'products.*.id'   => ['required', 'exists:products,id'],
            'products.*.qty'  => ['required', 'integer', 'min:1'],
            'products.*.price'=> ['required', 'numeric', 'min:0'],
            'products.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'   => ['nullable', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ], [
            'products.*.price.required' => 'Selling Price (₹) is required for all returned items.',
        ]);

        DB::transaction(function () use ($validated, $firmId, $user, &$returnableMaterial) {
            $subtotal = 0;
            $totalTax = 0;
            $itemsData = [];

            foreach ($validated['products'] as $item) {
                $itemSubtotal = $item['qty'] * $item['price'];
                $taxPercent = $item['tax_percent'] ?? 0;
                $itemTax = ($itemSubtotal * $taxPercent) / 100;

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $itemsData[] = [
                    'product_id' => $item['id'],
                    'unit_price' => $item['price'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $tax = $validated['tax_amount'] ?? $totalTax;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = $subtotal - $discount + $tax + $shipping;

            $returnableMaterial = ReturnableMaterial::create([
                'firm_id'         => $firmId,
                'return_number'   => $validated['return_number'],
                'project_name'    => $validated['project_name'],
                'return_reason'   => $validated['return_reason'],
                'customer_id'     => $validated['customer_id'],
                'user_id'         => $user->id,
                'return_date'     => $validated['return_date'],
                'subtotal'        => $subtotal,
                'tax_amount'      => $tax,
                'discount_amount' => $discount,
                'shipping_cost'   => $shipping,
                'grand_total'     => $grandTotal,
                'status'          => 'returned',
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $iData) {
                $returnableMaterial->items()->create($iData);

                // Re-add / increment inventory stock
                Product::where('id', $iData['product_id'])->increment('stock_quantity', $iData['quantity']);
            }
        });

        return redirect()->route('returnable.index')->with('success', 'Returnable Entry #' . $returnableMaterial->return_number . ' created & inventory stock updated!');
    }

    public function show(ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $returnableMaterial->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $returnableMaterial->load(['customer', 'user', 'firm', 'items.product.brand', 'items.product.category']);

        return view('returnable.show', compact('returnableMaterial'));
    }

    public function challan(ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $returnableMaterial->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $returnableMaterial->load(['customer', 'user', 'firm', 'items.product.brand', 'items.product.category']);

        return view('returnable.challan_view', compact('returnableMaterial'));
    }

    public function printInvoice(ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $returnableMaterial->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $returnableMaterial->load(['customer', 'user', 'firm', 'items.product.brand', 'items.product.category']);

        return view('returnable.invoice', compact('returnableMaterial'));
    }

    public function printChallan(ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $returnableMaterial->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $returnableMaterial->load(['customer', 'user', 'firm', 'items.product.brand', 'items.product.category']);

        return view('returnable.challan', compact('returnableMaterial'));
    }

    public function destroy(ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $returnableMaterial->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        DB::transaction(function () use ($returnableMaterial) {
            foreach ($returnableMaterial->items as $item) {
                // Revert stock quantity (decrement)
                Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
            }
            $returnableMaterial->delete();
        });

        return redirect()->route('returnable.index')->with('success', 'Returnable Entry deleted & stock adjusted.');
    }
}
