<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Category;
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
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('project_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhere('purchase_date', 'like', "%{$search}%")
                  ->orWhereRaw("DATE_FORMAT(purchase_date, '%m-%d-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(purchase_date, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(purchase_date, '%m/%d/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(purchase_date, '%d/%m/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });

                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $search, $matches)) {
                    $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $day   = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year  = $matches[3];
                    $dateFormatted = "{$year}-{$month}-{$day}";
                    $q->orWhere('purchase_date', 'like', "%{$dateFormatted}%");
                }
            });
        }

        $purchases = $query->latest()->paginate(10);
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('purchases.index', compact('purchases', 'firms'));
    }

    public function create()
    {
        $user = auth()->user();
        $firmId = $user->firm_id ?? 1;

        $suppliersQuery = Supplier::where('status', 'active');
        $productsQuery = Product::with(['category', 'brand'])->where('status', 'active');
        $categoriesQuery = Category::query();

        if (!$user->isSuperAdmin()) {
            $suppliersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
            $categoriesQuery->where('firm_id', $firmId);
        }

        $suppliers = $suppliersQuery->get();
        $products = $productsQuery->get();
        $categories = $categoriesQuery->get();

        // Calculate next firm-wise sequence for auto IDs
        $firmSeq = Purchase::where('firm_id', $firmId)->count() + 1;
        $autoInv = 'PINV-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);

        return view('purchases.create', compact('suppliers', 'products', 'categories', 'autoInv', 'firmId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'supplier_id'            => ['required', 'exists:suppliers,id'],
            'project_name'           => ['required', 'string'],
            'invoice_number'         => ['nullable', 'string'],
            'purchase_date'          => ['required', 'date'],
            'products'               => ['required', 'array', 'min:1'],
            'products.*.id'          => ['required', 'exists:products,id'],
            'products.*.qty'         => ['required', 'integer', 'min:1'],
            'products.*.cost'        => ['required', 'numeric', 'gt:0'],
            'products.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'          => ['nullable', 'numeric', 'min:0'],
            'payment_status'         => ['nullable', 'in:pending,paid,unpaid,due,partial'],
            'paid_amount'            => ['required', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string'],
        ], [
            'products.*.cost.gt'       => 'Unit Cost (₹) must be greater than 0 for all product items.',
            'products.*.cost.required' => 'Unit Cost (₹) is required for all product items.',
        ]);

        if (empty($validated['invoice_number'])) {
            $firmSeq = Purchase::where('firm_id', $firmId)->count() + 1;
            $validated['invoice_number'] = 'PINV-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);
        }

        // Firm-wise uniqueness check
        $existsProj = Purchase::where('firm_id', $firmId)->where('project_name', $validated['project_name'])->exists();
        if ($existsProj) {
            return back()->withErrors(['project_name' => 'Project Name already exists for this firm.'])->withInput();
        }

        $existsInv = Purchase::where('firm_id', $firmId)->where('invoice_number', $validated['invoice_number'])->exists();
        if ($existsInv) {
            return back()->withErrors(['invoice_number' => 'Invoice number already exists for this firm.'])->withInput();
        }

        DB::transaction(function () use ($validated, $firmId, $user, &$purchase) {
            $subtotal = 0;
            $totalTax = 0;
            $itemsData = [];

            foreach ($validated['products'] as $item) {
                $itemSubtotal = $item['qty'] * $item['cost'];
                $taxPercent = $item['tax_percent'] ?? 0;
                $itemTax = ($itemSubtotal * $taxPercent) / 100;

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $itemsData[] = [
                    'product_id' => $item['id'],
                    'unit_cost'  => $item['cost'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = $subtotal + $totalTax - $discount + $shipping;

            $statusInput = $validated['payment_status'] ?? 'pending';
            if ($statusInput === 'paid') {
                $paid = $grandTotal;
                $paymentStatus = 'paid';
            } elseif ($statusInput === 'unpaid') {
                $paid = 0.00;
                $paymentStatus = 'unpaid';
            } else {
                $paid = 0.00;
                $paymentStatus = 'pending';
            }

            $purchase = Purchase::create([
                'firm_id'         => $firmId,
                'project_name'    => $validated['project_name'],
                'invoice_number'  => $validated['invoice_number'],
                'supplier_id'     => $validated['supplier_id'],
                'user_id'         => $user->id,
                'purchase_date'   => $validated['purchase_date'],
                'subtotal'        => $subtotal,
                'tax_amount'      => $totalTax,
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
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order created & product stock updated!');
    }

    public function updatePaymentStatus(Request $request, Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $purchase->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validated = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,unpaid,due,partial'],
            'paid_amount'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $status = $validated['payment_status'];
        if ($status === 'paid') {
            $newPaid = (float)$purchase->grand_total;
        } else {
            $newPaid = 0.00;
        }

        DB::transaction(function () use ($purchase, $status, $newPaid) {
            $purchase->update([
                'payment_status' => $status,
                'paid_amount'    => $newPaid,
            ]);
        });

        return back()->with('success', 'Payment status updated successfully.');
    }

    public function edit(Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isAdmin() || $user->isSuperAdmin() || ($purchase->firm_id !== $user->firm_id && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access to edit purchase order.');
        }

        $firmId = $purchase->firm_id;
        $suppliers = Supplier::where('status', 'active')->where('firm_id', $firmId)->get();
        $products = Product::with(['category', 'brand'])->where('status', 'active')->where('firm_id', $firmId)->get();
        $categories = Category::where('firm_id', $firmId)->get();
        $purchase->load('items.product');

        return view('purchases.edit', compact('purchase', 'suppliers', 'products', 'categories', 'firmId'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isAdmin() || $user->isSuperAdmin() || ($purchase->firm_id !== $user->firm_id && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access to update purchase order.');
        }

        $validated = $request->validate([
            'supplier_id'            => ['required', 'exists:suppliers,id'],
            'project_name'           => ['required', 'string'],
            'invoice_number'         => ['required', 'string'],
            'purchase_date'          => ['required', 'date'],
            'products'               => ['required', 'array', 'min:1'],
            'products.*.id'          => ['required', 'exists:products,id'],
            'products.*.qty'         => ['required', 'integer', 'min:1'],
            'products.*.cost'        => ['required', 'numeric', 'gt:0'],
            'products.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'          => ['nullable', 'numeric', 'min:0'],
            'payment_status'         => ['nullable', 'in:pending,paid,unpaid,due,partial'],
            'paid_amount'            => ['required', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string'],
        ], [
            'products.*.cost.gt'       => 'Unit Cost (₹) must be greater than 0 for all product items.',
            'products.*.cost.required' => 'Unit Cost (₹) is required for all product items.',
        ]);

        $firmId = $purchase->firm_id;

        $existsProj = Purchase::where('firm_id', $firmId)->where('project_name', $validated['project_name'])->where('id', '!=', $purchase->id)->exists();
        if ($existsProj) {
            return back()->withErrors(['project_name' => 'Project Name already exists for this firm.'])->withInput();
        }

        $existsInv = Purchase::where('firm_id', $firmId)->where('invoice_number', $validated['invoice_number'])->where('id', '!=', $purchase->id)->exists();
        if ($existsInv) {
            return back()->withErrors(['invoice_number' => 'Invoice number already exists for this firm.'])->withInput();
        }

        DB::transaction(function () use ($validated, $purchase) {
            // Revert old inventory stock additions
            foreach ($purchase->items as $oldItem) {
                Product::where('id', $oldItem->product_id)->decrement('stock_quantity', $oldItem->quantity);
            }
            $purchase->items()->delete();

            $subtotal = 0;
            $totalTax = 0;
            $itemsData = [];

            foreach ($validated['products'] as $item) {
                $itemSubtotal = $item['qty'] * $item['cost'];
                $taxPercent = $item['tax_percent'] ?? 0;
                $itemTax = ($itemSubtotal * $taxPercent) / 100;

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $itemsData[] = [
                    'product_id' => $item['id'],
                    'unit_cost'  => $item['cost'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = $subtotal + $totalTax - $discount + $shipping;

            $statusInput = $validated['payment_status'] ?? 'pending';
            if ($statusInput === 'paid') {
                $paid = $grandTotal;
                $paymentStatus = 'paid';
            } elseif ($statusInput === 'unpaid') {
                $paid = 0.00;
                $paymentStatus = 'unpaid';
            } else {
                $paid = (float)$validated['paid_amount'];
                $paymentStatus = $statusInput;
            }

            $purchase->update([
                'project_name'    => $validated['project_name'],
                'invoice_number'  => $validated['invoice_number'],
                'supplier_id'     => $validated['supplier_id'],
                'purchase_date'   => $validated['purchase_date'],
                'subtotal'        => $subtotal,
                'tax_amount'      => $totalTax,
                'discount_amount' => $discount,
                'shipping_cost'   => $shipping,
                'grand_total'     => $grandTotal,
                'paid_amount'     => $paid,
                'payment_status'  => $paymentStatus,
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $iData) {
                $purchase->items()->create($iData);

                // Increment new stock
                $product = Product::find($iData['product_id']);
                $product->increment('stock_quantity', $iData['quantity']);
                $product->update(['cost_price' => $iData['unit_cost']]);
            }
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase order updated successfully & product stock recalculated!');
    }

    public function show(Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $purchase->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $purchase->load('supplier', 'user', 'items.product.category', 'items.product.brand', 'firm');
        return view('purchases.show', compact('purchase'));
    }

    public function printInvoice(Purchase $purchase)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $purchase->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $purchase->load('supplier', 'user', 'items.product.category', 'items.product.brand', 'firm');
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
