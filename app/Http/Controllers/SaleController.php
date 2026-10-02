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
        $query = Sale::with(['customer', 'user', 'firm', 'payments']);

        if (!$user->isSuperAdmin()) {
            $query->where('sales.firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('sales.firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('sales.invoice_number', 'like', "%{$search}%")
                  ->orWhere('sales.project_name', 'like', "%{$search}%")
                  ->orWhere('sales.vehicle_number', 'like', "%{$search}%")
                  ->orWhere('sales.sale_date', 'like', "%{$search}%")
                  ->orWhereRaw("DATE_FORMAT(sales.sale_date, '%m-%d-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(sales.sale_date, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(sales.sale_date, '%m/%d/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(sales.sale_date, '%d/%m/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });

                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $search, $matches)) {
                    $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $day   = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year  = $matches[3];
                    $dateFormatted = "{$year}-{$month}-{$day}";
                    $q->orWhere('sales.sale_date', 'like', "%{$dateFormatted}%");
                }
            });
        }

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'invoice_number') {
                $query->orderBy('sales.invoice_number', $sortOrder);
            } elseif ($sortBy === 'customer_name') {
                $query->leftJoin('customers', 'sales.customer_id', '=', 'customers.id')
                      ->select('sales.*')
                      ->orderBy('customers.company_name', $sortOrder);
            } elseif ($sortBy === 'firm_name') {
                $query->leftJoin('firms', 'sales.firm_id', '=', 'firms.id')
                      ->select('sales.*')
                      ->orderBy('firms.name', $sortOrder);
            } elseif (in_array($sortBy, ['sale_date', 'vehicle_number', 'grand_total', 'paid_amount', 'payment_status', 'created_at'])) {
                $query->orderBy("sales.{$sortBy}", $sortOrder);
            } else {
                $query->latest('sales.created_at');
            }
        } else {
            $query->latest('sales.created_at');
        }

        $sales = $query->paginate(10)->withQueryString();
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

        $autoInvoice = $this->generateNextInvoiceNumber($firmId);

        return view('sales.create', compact('customers', 'products', 'categories', 'autoInvoice'));
    }

    private function generateNextInvoiceNumber($firmId)
    {
        $lastSale = Sale::where('firm_id', $firmId)
            ->where('invoice_number', 'LIKE', 'CHN-FRM' . $firmId . '-%')
            ->orderByRaw('CAST(SUBSTRING_INDEX(invoice_number, "-", -1) AS UNSIGNED) DESC')
            ->first();

        $maxSeq = 0;
        if ($lastSale && preg_match('/(\d+)$/', $lastSale->invoice_number, $matches)) {
            $maxSeq = (int) $matches[1];
        }

        $countSeq = Sale::where('firm_id', $firmId)->count();
        $nextSeq = max($maxSeq, $countSeq) + 1;

        do {
            $candidate = 'CHN-FRM' . $firmId . '-' . str_pad($nextSeq, 4, '0', STR_PAD_LEFT);
            $exists = Sale::where('firm_id', $firmId)->where('invoice_number', $candidate)->exists();
            if ($exists) {
                $nextSeq++;
            }
        } while ($exists);

        return $candidate;
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $firmId = $user->isSuperAdmin() ? ($request->input('firm_id') ?? 1) : $user->firm_id;

        $validated = $request->validate([
            'customer_id'     => ['required', 'exists:customers,id'],
            'invoice_number'  => ['nullable', 'string'],
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

        if (empty($validated['invoice_number'])) {
            $validated['invoice_number'] = $this->generateNextInvoiceNumber($firmId);
        } else {
            $existsInv = Sale::where('firm_id', $firmId)->where('invoice_number', $validated['invoice_number'])->exists();
            if ($existsInv) {
                $validated['invoice_number'] = $this->generateNextInvoiceNumber($firmId);
            }
        }

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

        try {
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
                } elseif ($statusInput === 'unpaid' || $statusInput === 'pending') {
                    $paid = 0.00;
                    $paymentStatus = $statusInput;
                } else {
                    $paid = (float)($validated['paid_amount'] ?? 0);
                    $paymentStatus = $statusInput;
                }

                if ($paid > ($grandTotal + 0.001)) {
                    throw new \InvalidArgumentException('Amount Received (₹' . number_format($paid, 2) . ') cannot be greater than Grand Total (₹' . number_format($grandTotal, 2) . ').');
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

                if ($paid > 0) {
                    $sale->payments()->create([
                        'payment_date' => $validated['sale_date'],
                        'amount'       => $paid,
                        'notes'        => 'Initial payment upon sales order creation',
                    ]);
                }

                foreach ($itemsData as $iData) {
                    $sale->items()->create($iData);

                    // Auto-deduct stock quantity & update product selling price
                    $product = Product::find($iData['product_id']);
                    $product->decrement('stock_quantity', $iData['quantity']);
                    $product->update(['selling_price' => $iData['unit_price']]);
                }
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['paid_amount' => $e->getMessage()])->withInput();
        }

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
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'paid_amount'    => ['nullable', 'numeric', 'min:0'],
            'payment_date'   => ['nullable', 'date'],
            'notes'          => ['nullable', 'string', 'max:500'],
        ]);

        $status = $validated['payment_status'];
        $amountInput = $validated['payment_amount'] ?? $validated['paid_amount'] ?? null;
        $amount = ($amountInput !== null && $amountInput !== '') ? (float)$amountInput : null;
        $date = $validated['payment_date'] ?? date('Y-m-d');
        $notes = $validated['notes'] ?? null;

        $currentPaid = (float) $sale->payments()->sum('amount');
        $grandTotal  = (float) $sale->grand_total;
        $remainingDue = max(0, $grandTotal - $currentPaid);

        if ($status === 'paid') {
            if ($remainingDue > 0) {
                $sale->payments()->create([
                    'payment_date' => $date,
                    'amount'       => $remainingDue,
                    'notes'        => $notes ?? 'Full payment completed',
                ]);
            }
            $sale->recalculatePaymentStatus();
            return back()->with('success', 'Payment status updated to Paid.');
        }

        if ($status === 'unpaid') {
            $sale->payments()->delete();
            $sale->recalculatePaymentStatus();
            return back()->with('success', 'Payment status reset to Unpaid.');
        }

        // Partial or custom payment entry
        if ($amount !== null && $amount > 0) {
            if ($amount > ($remainingDue + 0.001)) {
                return back()->withErrors([
                    'payment_amount' => 'Payment amount (₹' . number_format($amount, 2) . ') cannot exceed remaining balance of ₹' . number_format($remainingDue, 2) . '.'
                ])->withInput();
            }

            $sale->payments()->create([
                'payment_date' => $date,
                'amount'       => $amount,
                'notes'        => $notes ?? 'Partial payment',
            ]);
            $sale->recalculatePaymentStatus();
            return back()->with('success', 'Partial payment of ₹' . number_format($amount, 2) . ' recorded successfully.');
        }

        $sale->recalculatePaymentStatus();
        return back()->with('success', 'Payment status updated successfully.');
    }

    public function destroyPayment(Sale $sale, \App\Models\SalePayment $payment)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        if ($payment->sale_id !== $sale->id) {
            abort(400, 'Invalid payment record.');
        }

        $payment->delete();
        $sale->recalculatePaymentStatus();

        return back()->with('success', 'Payment entry deleted and total recalculated successfully.');
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

        try {
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
                } elseif ($statusInput === 'unpaid' || $statusInput === 'pending') {
                    $paid = 0.00;
                    $paymentStatus = $statusInput;
                } else {
                    $paid = (float)$validated['paid_amount'];
                    $paymentStatus = $statusInput;
                }

                if ($paid > ($grandTotal + 0.001)) {
                    throw new \InvalidArgumentException('Amount Received (₹' . number_format($paid, 2) . ') cannot be greater than Grand Total (₹' . number_format($grandTotal, 2) . ').');
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

                    // Deduct stock for new items & update product selling price
                    $product = Product::find($iData['product_id']);
                    $product->decrement('stock_quantity', $iData['quantity']);
                    $product->update(['selling_price' => $iData['unit_price']]);
                }
            });
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['paid_amount' => $e->getMessage()])->withInput();
        }

        return redirect()->route('sales.index')->with('success', 'Sales order updated successfully & inventory recalculated!');
    }

    public function show(Sale $sale)
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() && $sale->firm_id !== $user->firm_id) {
            abort(403, 'Unauthorized access to firm record.');
        }

        $sale->load('customer', 'user', 'items.product.category', 'items.product.brand', 'firm', 'payments');
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
