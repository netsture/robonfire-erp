<?php

namespace App\Http\Controllers;

use App\Models\ReturnableMaterial;
use App\Models\ReturnableMaterialItem;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReturnableMaterialController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = ReturnableMaterial::with('customer', 'user', 'firm');

        if (!$user->isSuperAdmin()) {
            $query->where('returnable_materials.firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('returnable_materials.firm_id', $request->firm_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('returnable_materials.return_number', 'like', "%{$search}%")
                  ->orWhere('returnable_materials.project_name', 'like', "%{$search}%")
                  ->orWhere('returnable_materials.return_reason', 'like', "%{$search}%")
                  ->orWhere('returnable_materials.return_date', 'like', "%{$search}%")
                  ->orWhereRaw("DATE_FORMAT(returnable_materials.return_date, '%m-%d-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(returnable_materials.return_date, '%d-%m-%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(returnable_materials.return_date, '%m/%d/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereRaw("DATE_FORMAT(returnable_materials.return_date, '%d/%m/%Y') LIKE ?", ["%{$search}%"])
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('company_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });

                if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $search, $matches)) {
                    $month = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
                    $day   = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
                    $year  = $matches[3];
                    $dateFormatted = "{$year}-{$month}-{$day}";
                    $q->orWhere('returnable_materials.return_date', 'like', "%{$dateFormatted}%");
                }
            });
        }

        // Sorting logic
        $sortBy = $request->get('sort_by');
        $sortOrder = strtolower($request->get('sort_order', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortBy) {
            if ($sortBy === 'return_number') {
                $query->orderBy('returnable_materials.return_number', $sortOrder);
            } elseif ($sortBy === 'customer_name') {
                $query->leftJoin('customers', 'returnable_materials.customer_id', '=', 'customers.id')
                      ->select('returnable_materials.*')
                      ->orderBy('customers.company_name', $sortOrder);
            } elseif ($sortBy === 'firm_name') {
                $query->leftJoin('firms', 'returnable_materials.firm_id', '=', 'firms.id')
                      ->select('returnable_materials.*')
                      ->orderBy('firms.name', $sortOrder);
            } elseif (in_array($sortBy, ['return_date', 'return_reason', 'grand_total', 'created_at'])) {
                $query->orderBy("returnable_materials.{$sortBy}", $sortOrder);
            } else {
                $query->latest('returnable_materials.created_at');
            }
        } else {
            $query->latest('returnable_materials.created_at');
        }

        $returns = $query->paginate(10)->withQueryString();
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
        $salesQuery = \App\Models\Sale::with(['customer', 'items.product.category', 'items.product.brand'])->latest();

        if (!$user->isSuperAdmin()) {
            $customersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
            $categoriesQuery->where('firm_id', $firmId);
            $salesQuery->where('firm_id', $firmId);
        }

        $customers = $customersQuery->get();
        $products = $productsQuery->get();
        $categories = $categoriesQuery->get();
        $sales = $salesQuery->get();

        $salesProjects = \App\Models\Sale::whereNotNull('project_name')->where('project_name', '!=', '');
        $returnsProjects = ReturnableMaterial::whereNotNull('project_name')->where('project_name', '!=', '');

        if (!$user->isSuperAdmin()) {
            $salesProjects->where('firm_id', $firmId);
            $returnsProjects->where('firm_id', $firmId);
        }

        $projectNames = $salesProjects->pluck('project_name')
            ->merge($returnsProjects->pluck('project_name'))
            ->unique()
            ->filter()
            ->values();

        $firmSeq = ReturnableMaterial::where('firm_id', $firmId)->count() + 1;
        $autoReturnNumber = 'RET-FRM' . $firmId . '-' . str_pad($firmSeq, 4, '0', STR_PAD_LEFT);

        return view('returnable.create', compact('customers', 'products', 'categories', 'sales', 'projectNames', 'autoReturnNumber'));
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

    public function edit(ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isAdmin() || $user->isSuperAdmin() || ($returnableMaterial->firm_id !== $user->firm_id && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access to edit returnable material entry.');
        }

        $firmId = $returnableMaterial->firm_id;
        $customers = Customer::where('status', 'active')->where('firm_id', $firmId)->get();
        $products = Product::with(['category', 'brand'])->where('status', 'active')->where('firm_id', $firmId)->get();
        $categories = Category::where('firm_id', $firmId)->get();
        $sales = \App\Models\Sale::with(['customer', 'items.product.category', 'items.product.brand'])->where('firm_id', $firmId)->latest()->get();

        $salesProjects = \App\Models\Sale::where('firm_id', $firmId)->whereNotNull('project_name')->where('project_name', '!=', '')->pluck('project_name');
        $returnsProjects = ReturnableMaterial::where('firm_id', $firmId)->whereNotNull('project_name')->where('project_name', '!=', '')->pluck('project_name');
        $projectNames = $salesProjects->merge($returnsProjects)->unique()->filter()->values();

        $returnableMaterial->load('items.product');

        return view('returnable.edit', compact('returnableMaterial', 'customers', 'products', 'categories', 'sales', 'projectNames', 'firmId'));
    }

    public function update(Request $request, ReturnableMaterial $returnableMaterial)
    {
        $user = auth()->user();
        if (!$user->isAdmin() || $user->isSuperAdmin() || ($returnableMaterial->firm_id !== $user->firm_id && !$user->isSuperAdmin())) {
            abort(403, 'Unauthorized access to update returnable material entry.');
        }

        $validated = $request->validate([
            'customer_id'           => ['required', 'exists:customers,id'],
            'return_number'         => ['required', 'string'],
            'project_name'          => ['required', 'string'],
            'return_reason'         => ['required', 'string'],
            'return_date'           => ['required', 'date'],
            'products'              => ['required', 'array', 'min:1'],
            'products.*.id'         => ['required', 'exists:products,id'],
            'products.*.qty'        => ['required', 'integer', 'min:1'],
            'products.*.unit_price' => ['required', 'numeric', 'min:0'],
            'discount_amount'       => ['nullable', 'numeric', 'min:0'],
            'tax_amount'            => ['nullable', 'numeric', 'min:0'],
            'shipping_cost'         => ['nullable', 'numeric', 'min:0'],
            'notes'                 => ['nullable', 'string'],
        ]);

        $firmId = $returnableMaterial->firm_id;

        $existsNum = ReturnableMaterial::where('firm_id', $firmId)->where('return_number', $validated['return_number'])->where('id', '!=', $returnableMaterial->id)->exists();
        if ($existsNum) {
            return back()->withErrors(['return_number' => 'Return Slip # already exists for this firm.'])->withInput();
        }

        DB::transaction(function () use ($validated, $returnableMaterial) {
            // Revert old inventory stock additions
            foreach ($returnableMaterial->items as $oldItem) {
                Product::where('id', $oldItem->product_id)->decrement('stock_quantity', $oldItem->quantity);
            }
            $returnableMaterial->items()->delete();

            $subtotal = 0;
            $totalTax = 0;
            $itemsData = [];

            foreach ($validated['products'] as $item) {
                $product = Product::find($item['id']);
                $itemSubtotal = $item['qty'] * $item['unit_price'];
                $itemTax = $itemSubtotal * (($product->tax_percent ?? 0) / 100);

                $subtotal += $itemSubtotal;
                $totalTax += $itemTax;

                $itemsData[] = [
                    'product_id' => $item['id'],
                    'unit_price' => $item['unit_price'],
                    'quantity'   => $item['qty'],
                    'subtotal'   => $itemSubtotal,
                ];
            }

            $discount = $validated['discount_amount'] ?? 0;
            $tax = $validated['tax_amount'] ?? $totalTax;
            $shipping = $validated['shipping_cost'] ?? 0;
            $grandTotal = max(0, $subtotal - $discount + $tax + $shipping);

            $returnableMaterial->update([
                'return_number'   => $validated['return_number'],
                'project_name'    => $validated['project_name'],
                'return_reason'   => $validated['return_reason'],
                'customer_id'     => $validated['customer_id'],
                'return_date'     => $validated['return_date'],
                'subtotal'        => $subtotal,
                'tax_amount'      => $tax,
                'discount_amount' => $discount,
                'shipping_cost'   => $shipping,
                'grand_total'     => $grandTotal,
                'notes'           => $validated['notes'] ?? null,
            ]);

            foreach ($itemsData as $iData) {
                $returnableMaterial->items()->create($iData);

                // Re-increment stock for updated items
                Product::where('id', $iData['product_id'])->increment('stock_quantity', $iData['quantity']);
            }
        });

        return redirect()->route('returnable.index')->with('success', 'Returnable entry updated successfully & stock updated!');
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
