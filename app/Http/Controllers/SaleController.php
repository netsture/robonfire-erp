<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
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
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_number', 'like', "%{$search}%")
                  ->orWhere('sale_date', 'like', "%{$search}%")
                  ->orWhereRaw("DATE_FORMAT(sale_date, '%m-%d-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(sale_date, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(sale_date, '%m/%d/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(sale_date, '%d/%m/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });

                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $search, $matches)) {
                    $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $day   = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year  = $matches[3];
                    $dateFormatted = "{$year}-{$month}-{$day}";
                    $q->orWhere('sale_date', 'like', "%{$dateFormatted}%");
                }
            });
        }

        $sales = $query->latest()->paginate(10);
        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('sales.index', compact('sales', 'firms'));
    }

    public function create()
    {
        $user = auth()->user();
        $firmId = $user->firm_id ?? 1;

        $customersQuery = Customer::where('status', 'active');
        $productsQuery = Product::with(['category', 'brand'])->where('status', 'active')->where('stock_quantity', '>', 0);
        $categoriesQuery = Category::query();

        if (!$user->isSuperAdmin()) {
            $customersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
            $categoriesQuery->where('firm_id', $firmId);
        }

        $customers = $customersQuery->get();
        $products = $productsQuery->get();
        $categories = $categoriesQuery->get();

        $firmSeq = Sale::where('firm_id', $firmId)->count() + 1;
        $autoInvoice = 'CHN-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);

        return view('sales.create', compact('customers', 'products', 'categories', 'autoInvoice'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'customer_id'     => ['required', 'exists:customers,id'],
            'invoice_number'  => ['required', 'string'],
            'project_name'    => ['required', 'string'],
            'vehicle_number'  => ['nullable', 'string'],
            'sale_date'       => ['required', 'date'],
            'products'        => ['required', 'array', 'min:1'],
            'products.*.id'   => ['required', 'exists:products,id'],
            'products.*.qty'  => ['required', 'integer', 'min:1'],
            'products.*.price'=> ['required', 'numeric', 'gt:0'],
            'products.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'   => ['nullable', 'numeric', 'min:0'],
            'payment_status'  => ['nullable', 'in:pending,paid,unpaid,due,partial'],
            'paid_amount'     => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string'],
        ], [
            'products.*.price.gt'       => 'Selling Price (₹) must be greater than 0 for all product items.',
            'products.*.price.required' => 'Selling Price (₹) is required for all product items.',
        ]);

        // Check stock availability first
        foreach ($validated['products'] as $item) {
            $product = Product::with(['category', 'brand'])->find($item['id']);
            if ($product->stock_quantity < $item['qty']) {
                $details = $product->name;
                $meta = [];
                if ($product->category) {
                    $meta[] = 'Category: ' . $product->category->name;
                }
                if ($product->brand) {
                    $meta[] = 'Brand: ' . $product->brand->name;
                }
                if (!empty($meta)) {
                    $details .= ' (' . implode(', ', $meta) . ')';
                }
                $unitStr = $product->unit ? ' ' . $product->unit : '';

                return back()->withInput()->with('error', 'Insufficient stock for "' . $details . '". Only ' . $product->stock_quantity . $unitStr . ' available.');
            }
        }

        DB::transaction(function () use ($validated, $firmId, $user, &$sale) {
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
            $tax = $totalTax;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = $subtotal + $tax - $discount + $shipping;

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

            $sale = Sale::create([
                'firm_id'         => $firmId,
                'invoice_number'  => $validated['invoice_number'],
                'project_name'    => $validated['project_name'] ?? null,
                'vehicle_number'  => $validated['vehicle_number'] ?? null,
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
        });

        return redirect()->route('sales.index')->with('success', 'Sales Order #' . $sale->invoice_number . ' completed & stock updated!');
    }

    public function updatePaymentStatus(Request $request, Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $validated = $request->validate([
            'payment_status' => ['required', 'in:pending,paid,unpaid,due,partial'],
            'paid_amount'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $status = $validated['payment_status'];
        if ($status === 'paid') {
            $newPaid = (float)$sale->grand_total;
        } else {
            $newPaid = 0.00;
        }

        DB::transaction(function () use ($sale, $status, $newPaid) {
            $sale->update([
                'payment_status' => $status,
                'paid_amount'    => $newPaid,
            ]);
        });

        return back()->with('success', 'Payment status updated successfully.');
    }

    public function edit(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isAdmin() || $user->isSuperAdmin() || ($sale->firm_id !== $user->firm_id && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access to edit sales order.');
        }

        $firmId = $sale->firm_id;
        $customers = Customer::where('status', 'active')->where('firm_id', $firmId)->get();
        $products = Product::with(['category', 'brand'])->where('status', 'active')->where('firm_id', $firmId)->get();
        $categories = Category::where('firm_id', $firmId)->get();
        $sale->load('items.product');

        return view('sales.edit', compact('sale', 'customers', 'products', 'categories', 'firmId'));
    }

    public function update(Request $request, Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isAdmin() || $user->isSuperAdmin() || ($sale->firm_id !== $user->firm_id && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access to update sales order.');
        }

        $validated = $request->validate([
            'customer_id'            => ['required', 'exists:customers,id'],
            'invoice_number'         => ['required', 'string'],
            'project_name'           => ['required', 'string'],
            'vehicle_number'         => ['nullable', 'string'],
            'sale_date'              => ['required', 'date'],
            'products'               => ['required', 'array', 'min:1'],
            'products.*.id'          => ['required', 'exists:products,id'],
            'products.*.qty'         => ['required', 'integer', 'min:1'],
            'products.*.price'       => ['required', 'numeric', 'min:0'],
            'products.*.tax_percent' => ['nullable', 'numeric', 'min:0'],
            'discount_amount'        => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'          => ['nullable', 'numeric', 'min:0'],
            'payment_status'         => ['nullable', 'in:pending,paid,unpaid,due,partial'],
            'paid_amount'            => ['required', 'numeric', 'min:0'],
            'notes'                  => ['nullable', 'string'],
        ]);

        $firmId = $sale->firm_id;

        $existsInv = Sale::where('firm_id', $firmId)->where('invoice_number', $validated['invoice_number'])->where('id', '!=', $sale->id)->exists();
        if ($existsInv) {
            return back()->withErrors(['invoice_number' => 'Invoice number already exists for this firm.'])->withInput();
        }

        DB::transaction(function () use ($validated, $sale) {
            // Revert old inventory stock deductions
            foreach ($sale->items as $oldItem) {
                Product::where('id', $oldItem->product_id)->increment('stock_quantity', $oldItem->quantity);
            }
            $sale->items()->delete();

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

            $sale->update([
                'invoice_number'  => $validated['invoice_number'],
                'project_name'    => $validated['project_name'] ?? null,
                'vehicle_number'  => $validated['vehicle_number'] ?? null,
                'customer_id'     => $validated['customer_id'],
                'sale_date'       => $validated['sale_date'],
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
                $sale->items()->create($iData);

                // Deduct stock for new items
                $product = Product::find($iData['product_id']);
                $product->decrement('stock_quantity', $iData['quantity']);
            }
        });

        return redirect()->route('sales.index')->with('success', 'Sales order updated successfully & inventory recalculated!');
    }

    public function show(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product.category', 'items.product.brand', 'firm');
        return view('sales.show', compact('sale'));
    }

    public function challan(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product.category', 'items.product.brand', 'firm');
        return view('sales.challan', compact('sale'));
    }

    public function printInvoice(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product.category', 'items.product.brand', 'firm');
        return view('sales.invoice', compact('sale'));
    }

    public function printChallan(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product.category', 'items.product.brand', 'firm');
        return view('sales.print_challan', compact('sale'));
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
