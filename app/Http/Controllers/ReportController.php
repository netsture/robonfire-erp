<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ReturnableMaterial;
use App\Models\ReturnableMaterialItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $salesQuery = Sale::query();
        $purchasesQuery = Purchase::query();
        $productsQuery = Product::query();

        if (!$user->isSuperAdmin()) {
            $salesQuery->where('firm_id', $user->firm_id);
            $purchasesQuery->where('firm_id', $user->firm_id);
            $productsQuery->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $salesQuery->where('firm_id', $request->firm_id);
            $purchasesQuery->where('firm_id', $request->firm_id);
            $productsQuery->where('firm_id', $request->firm_id);
        }

        $totalSales = $salesQuery->sum('grand_total');
        $totalPurchases = $purchasesQuery->sum('grand_total');
        $totalProducts = $productsQuery->count();

        // Stock Valuation
        $inventoryCostValue = (clone $productsQuery)->selectRaw('SUM(stock_quantity * cost_price) as cost_val')->value('cost_val') ?? 0;
        $inventoryRetailValue = (clone $productsQuery)->selectRaw('SUM(stock_quantity * selling_price) as retail_val')->value('retail_val') ?? 0;
        $potentialProfit = $inventoryRetailValue - $inventoryCostValue;

        return view('reports.index', compact(
            'totalSales',
            'totalPurchases',
            'totalProducts',
            'inventoryCostValue',
            'inventoryRetailValue',
            'potentialProfit'
        ));
    }

    private function parseDateInput(?string $dateStr): ?string
    {
        if (empty($dateStr)) {
            return null;
        }

        $trimmed = trim($dateStr);
        try {
            if (preg_match('/^\d{1,2}[-\/]\d{1,2}[-\/]\d{4}$/', $trimmed)) {
                $normalized = str_replace('/', '-', $trimmed);
                return \Carbon\Carbon::createFromFormat('d-m-Y', $normalized)->format('Y-m-d');
            }

            if (preg_match('/^\d{4}[-\/]\d{1,2}[-\/]\d{1,2}$/', $trimmed)) {
                $normalized = str_replace('/', '-', $trimmed);
                return \Carbon\Carbon::createFromFormat('Y-m-d', $normalized)->format('Y-m-d');
            }

            return \Carbon\Carbon::parse($trimmed)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function salesReport(Request $request)
    {
        $user = auth()->user();
        $query = Sale::with(['customer', 'firm']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('start_date')) {
            $startDate = $this->parseDateInput($request->start_date);
            if ($startDate) {
                $query->whereDate('sale_date', '>=', $startDate);
            }
        }
        if ($request->filled('end_date')) {
            $endDate = $this->parseDateInput($request->end_date);
            if ($endDate) {
                $query->whereDate('sale_date', '<=', $endDate);
            }
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('vehicle_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cQ) use ($search) {
                      $cQ->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        // Get matching sales list for totals calculation
        $allMatchingSales = (clone $query)->get();

        $totalOrdersCount   = $allMatchingSales->count();
        $totalSubtotal      = $allMatchingSales->sum('subtotal');
        $totalTaxAmount     = $allMatchingSales->sum('tax_amount');
        $totalDiscountAmount= $allMatchingSales->sum('discount_amount');
        $totalShippingCost  = $allMatchingSales->sum('shipping_cost');
        $totalGrandTotal    = $allMatchingSales->sum('grand_total');
        $totalPaidAmount    = $allMatchingSales->sum('paid_amount');
        $totalPendingAmount = $totalGrandTotal - $totalPaidAmount;

        $paidOrdersCount    = $allMatchingSales->where('payment_status', 'paid')->count();
        $unpaidOrdersCount  = $allMatchingSales->where('payment_status', 'unpaid')->count();
        $pendingOrdersCount = $allMatchingSales->filter(fn($s) => in_array($s->payment_status, ['pending', 'due']))->count();

        $sales = $query->latest()->paginate(15)->withQueryString();

        $custQuery = Customer::query();
        if (!$user->isSuperAdmin()) {
            $custQuery->where('firm_id', $user->firm_id);
        }
        $customers = $custQuery->get();

        $firms = [];
        if ($user->isSuperAdmin()) {
            $firms = \App\Models\Firm::all();
        }

        return view('reports.sales', compact(
            'sales',
            'totalOrdersCount',
            'totalSubtotal',
            'totalTaxAmount',
            'totalDiscountAmount',
            'totalShippingCost',
            'totalGrandTotal',
            'totalPaidAmount',
            'totalPendingAmount',
            'paidOrdersCount',
            'unpaidOrdersCount',
            'pendingOrdersCount',
            'customers',
            'firms'
        ));
    }

    public function purchasesReport(Request $request)
    {
        $user = auth()->user();
        $query = Purchase::with(['supplier', 'firm']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('start_date')) {
            $startDate = $this->parseDateInput($request->start_date);
            if ($startDate) {
                $query->whereDate('purchase_date', '>=', $startDate);
            }
        }
        if ($request->filled('end_date')) {
            $endDate = $this->parseDateInput($request->end_date);
            if ($endDate) {
                $query->whereDate('purchase_date', '<=', $endDate);
            }
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sQ) use ($search) {
                      $sQ->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        // Get matching purchases list for totals calculation
        $allMatchingPurchases = (clone $query)->get();

        $totalOrdersCount   = $allMatchingPurchases->count();
        $totalSubtotal      = $allMatchingPurchases->sum('subtotal');
        $totalTaxAmount     = $allMatchingPurchases->sum('tax_amount');
        $totalDiscountAmount= $allMatchingPurchases->sum('discount_amount');
        $totalShippingCost  = $allMatchingPurchases->sum('shipping_cost');
        $totalGrandTotal    = $allMatchingPurchases->sum('grand_total');
        $totalPaidAmount    = $allMatchingPurchases->sum('paid_amount');
        $totalPendingAmount = $totalGrandTotal - $totalPaidAmount;

        $paidOrdersCount    = $allMatchingPurchases->where('payment_status', 'paid')->count();
        $unpaidOrdersCount  = $allMatchingPurchases->where('payment_status', 'unpaid')->count();
        $pendingOrdersCount = $allMatchingPurchases->filter(fn($p) => in_array($p->payment_status, ['pending', 'due']))->count();

        $purchases = $query->latest()->paginate(15)->withQueryString();

        $supQuery = Supplier::query();
        if (!$user->isSuperAdmin()) {
            $supQuery->where('firm_id', $user->firm_id);
        }
        $suppliers = $supQuery->get();

        $firms = [];
        if ($user->isSuperAdmin()) {
            $firms = \App\Models\Firm::all();
        }

        return view('reports.purchases', compact(
            'purchases',
            'totalOrdersCount',
            'totalSubtotal',
            'totalTaxAmount',
            'totalDiscountAmount',
            'totalShippingCost',
            'totalGrandTotal',
            'totalPaidAmount',
            'totalPendingAmount',
            'paidOrdersCount',
            'unpaidOrdersCount',
            'pendingOrdersCount',
            'suppliers',
            'firms'
        ));
    }

    public function inventoryReport(Request $request)
    {
        $user = auth()->user();
        $query = Product::with(['category', 'brand', 'firm']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('hsn_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out_of_stock') {
                $query->where('stock_quantity', '<=', 0);
            } elseif ($request->stock_status === 'low_stock') {
                $query->whereColumn('stock_quantity', '<=', 'alert_quantity')
                      ->where('stock_quantity', '>', 0);
            } elseif ($request->stock_status === 'in_stock') {
                $query->whereColumn('stock_quantity', '>', 'alert_quantity');
            }
        }

        $products = $query->latest()->get();

        $totalProducts = $products->count();
        $totalUnitsInStock = $products->sum('stock_quantity');
        $totalCostValue = $products->sum(fn($p) => $p->stock_quantity * $p->cost_price);
        $totalRetailValue = $products->sum(fn($p) => $p->stock_quantity * $p->selling_price);
        $potentialProfit = $totalRetailValue - $totalCostValue;

        $lowStockCount = $products->filter(fn($p) => $p->stock_quantity <= $p->alert_quantity && $p->stock_quantity > 0)->count();
        $outOfStockCount = $products->filter(fn($p) => $p->stock_quantity <= 0)->count();

        // Categories and Brands for filter dropdowns
        $catQuery = \App\Models\Category::query();
        $brandQuery = \App\Models\Brand::query();
        if (!$user->isSuperAdmin()) {
            $catQuery->where('firm_id', $user->firm_id);
            $brandQuery->where('firm_id', $user->firm_id);
        }
        $categories = $catQuery->get();
        $brands = $brandQuery->get();

        $firms = [];
        if ($user->isSuperAdmin()) {
            $firms = \App\Models\Firm::all();
        }

        return view('reports.inventory', compact(
            'products',
            'totalProducts',
            'totalUnitsInStock',
            'totalCostValue',
            'totalRetailValue',
            'potentialProfit',
            'lowStockCount',
            'outOfStockCount',
            'categories',
            'brands',
            'firms'
        ));
    }

    public function profitLossReport(Request $request)
    {
        $user = auth()->user();
        $salesQuery = Sale::query();
        $purchasesQuery = Purchase::query();

        if (!$user->isSuperAdmin()) {
            $salesQuery->where('firm_id', $user->firm_id);
            $purchasesQuery->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $salesQuery->where('firm_id', $request->firm_id);
            $purchasesQuery->where('firm_id', $request->firm_id);
        }

        if ($request->filled('start_date')) {
            $startDate = $this->parseDateInput($request->start_date);
            if ($startDate) {
                $salesQuery->whereDate('sale_date', '>=', $startDate);
                $purchasesQuery->whereDate('purchase_date', '>=', $startDate);
            }
        }
        if ($request->filled('end_date')) {
            $endDate = $this->parseDateInput($request->end_date);
            if ($endDate) {
                $salesQuery->whereDate('sale_date', '<=', $endDate);
                $purchasesQuery->whereDate('purchase_date', '<=', $endDate);
            }
        }

        $salesCount       = (clone $salesQuery)->count();
        $grossSales       = (clone $salesQuery)->sum('subtotal');
        $salesTaxes       = (clone $salesQuery)->sum('tax_amount');
        $salesShipping    = (clone $salesQuery)->sum('shipping_cost');
        $salesDiscounts   = (clone $salesQuery)->sum('discount_amount');
        $salesGrandTotal  = (clone $salesQuery)->sum('grand_total');
        $salesPaid        = (clone $salesQuery)->sum('paid_amount');
        $salesReceivables = $salesGrandTotal - $salesPaid;
        $netSales         = $grossSales - $salesDiscounts;

        $purchasesCount   = (clone $purchasesQuery)->count();
        $grossPurchases   = (clone $purchasesQuery)->sum('subtotal');
        $purchaseTaxes    = (clone $purchasesQuery)->sum('tax_amount');
        $purchaseShipping = (clone $purchasesQuery)->sum('shipping_cost');
        $purchaseDiscounts= (clone $purchasesQuery)->sum('discount_amount');
        $purchaseGrandTotal= (clone $purchasesQuery)->sum('grand_total');
        $purchasePaid     = (clone $purchasesQuery)->sum('paid_amount');
        $purchasePayables = $purchaseGrandTotal - $purchasePaid;
        $netPurchases     = $grossPurchases - $purchaseDiscounts;

        $grossProfit = $netSales - $netPurchases;
        $netProfit   = $salesGrandTotal - $purchaseGrandTotal;
        $profitMargin= $salesGrandTotal > 0 ? ($netProfit / $salesGrandTotal) * 100 : 0;

        $firms = [];
        if ($user->isSuperAdmin()) {
            $firms = \App\Models\Firm::all();
        }

        return view('reports.profit_loss', compact(
            'salesCount',
            'grossSales',
            'salesTaxes',
            'salesShipping',
            'salesDiscounts',
            'salesGrandTotal',
            'salesPaid',
            'salesReceivables',
            'netSales',
            'purchasesCount',
            'grossPurchases',
            'purchaseTaxes',
            'purchaseShipping',
            'purchaseDiscounts',
            'purchaseGrandTotal',
            'purchasePaid',
            'purchasePayables',
            'netPurchases',
            'grossProfit',
            'netProfit',
            'profitMargin',
            'firms'
        ));
    }

    public function returnProductsReport(Request $request)
    {
        $user = auth()->user();
        $query = ReturnableMaterial::with(['customer', 'firm', 'items.product']);

        if (!$user->isSuperAdmin()) {
            $query->where('firm_id', $user->firm_id);
        } elseif ($request->filled('firm_id')) {
            $query->where('firm_id', $request->firm_id);
        }

        if ($request->filled('start_date')) {
            $startDate = $this->parseDateInput($request->start_date);
            if ($startDate) {
                $query->whereDate('return_date', '>=', $startDate);
            }
        }
        if ($request->filled('end_date')) {
            $endDate = $this->parseDateInput($request->end_date);
            if ($endDate) {
                $query->whereDate('return_date', '<=', $endDate);
            }
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                  ->orWhere('project_name', 'like', "%{$search}%")
                  ->orWhere('return_reason', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cQ) use ($search) {
                      $cQ->where('company_name', 'like', "%{$search}%");
                  });
            });
        }

        $allMatchingReturns = (clone $query)->get();

        $totalReturnCount   = $allMatchingReturns->count();
        $totalSubtotal      = $allMatchingReturns->sum('subtotal');
        $totalTaxAmount     = $allMatchingReturns->sum('tax_amount');
        $totalDiscountAmount= $allMatchingReturns->sum('discount_amount');
        $totalShippingCost  = $allMatchingReturns->sum('shipping_cost');
        $totalGrandTotal    = $allMatchingReturns->sum('grand_total');

        // Sum of all returned item quantities
        $returnIds          = $allMatchingReturns->pluck('id');
        $totalUnitsReturned = ReturnableMaterialItem::whereIn('returnable_material_id', $returnIds)->sum('quantity');

        $returns = $query->latest()->paginate(15)->withQueryString();

        $custQuery = Customer::query();
        if (!$user->isSuperAdmin()) {
            $custQuery->where('firm_id', $user->firm_id);
        }
        $customers = $custQuery->get();

        $firms = [];
        if ($user->isSuperAdmin()) {
            $firms = \App\Models\Firm::all();
        }

        return view('reports.return_products', compact(
            'returns',
            'totalReturnCount',
            'totalUnitsReturned',
            'totalSubtotal',
            'totalTaxAmount',
            'totalDiscountAmount',
            'totalShippingCost',
            'totalGrandTotal',
            'customers',
            'firms'
        ));
    }

    public function productStockReport(Request $request)
    {
        $user = auth()->user();
        $firmId = !$user->isSuperAdmin() ? $user->firm_id : ($request->input('firm_id') ?? null);

        $productsQuery = Product::with(['category', 'brand', 'firm']);
        if (!$user->isSuperAdmin()) {
            $productsQuery->where('firm_id', $user->firm_id);
        } elseif ($firmId) {
            $productsQuery->where('firm_id', $firmId);
        }

        $products = $productsQuery->orderBy('name')->get();

        $selectedProductId = $request->input('product_id');
        $selectedProduct = null;
        if ($selectedProductId) {
            $selectedProduct = $products->firstWhere('id', $selectedProductId);
        }
        if (!$selectedProduct && $products->count() > 0) {
            $selectedProduct = $products->first();
            $selectedProductId = $selectedProduct->id;
        }

        $movementHistory = collect();
        $summary = [
            'total_purchased_qty' => 0,
            'total_purchased_amount' => 0,
            'total_sold_qty' => 0,
            'total_sold_amount' => 0,
            'total_returned_qty' => 0,
            'total_returned_amount' => 0,
            'total_adjusted_add_qty' => 0,
            'total_adjusted_sub_qty' => 0,
            'current_stock' => 0,
            'cost_price' => 0,
            'selling_price' => 0,
        ];

        if ($selectedProduct) {
            $startDate = $this->parseDateInput($request->input('start_date'));
            $endDate   = $this->parseDateInput($request->input('end_date'));

            // 1. Purchases
            $purchaseItemsQuery = \App\Models\PurchaseItem::where('product_id', $selectedProduct->id)
                ->whereHas('purchase', function ($q) use ($firmId, $startDate, $endDate) {
                    if ($firmId) $q->where('firm_id', $firmId);
                    if ($startDate) $q->whereDate('purchase_date', '>=', $startDate);
                    if ($endDate) $q->whereDate('purchase_date', '<=', $endDate);
                })
                ->with(['purchase.supplier', 'purchase.firm']);

            $purchaseItems = $purchaseItemsQuery->get();
            foreach ($purchaseItems as $pi) {
                if (!$pi->purchase) continue;
                $summary['total_purchased_qty'] += $pi->quantity;
                $summary['total_purchased_amount'] += $pi->subtotal;

                $movementHistory->push([
                    'timestamp' => $pi->purchase->purchase_date ? \Carbon\Carbon::parse($pi->purchase->purchase_date)->format('Y-m-d') : $pi->created_at->format('Y-m-d'),
                    'raw_date'  => $pi->purchase->purchase_date ?? $pi->created_at,
                    'type'      => 'Purchase',
                    'badge_class' => 'bg-success text-white',
                    'direction' => 'IN',
                    'ref_no'    => $pi->purchase->invoice_number,
                    'ref_route' => route('purchases.show', $pi->purchase->id),
                    'party'     => $pi->purchase->supplier->company_name ?? 'N/A',
                    'party_type'=> 'Supplier',
                    'quantity'  => $pi->quantity,
                    'rate'      => $pi->unit_cost,
                    'amount'    => $pi->subtotal,
                    'notes'     => 'Project: ' . ($pi->purchase->project_name ?? '-'),
                ]);
            }

            // 2. Sales
            $saleItemsQuery = \App\Models\SaleItem::where('product_id', $selectedProduct->id)
                ->whereHas('sale', function ($q) use ($firmId, $startDate, $endDate) {
                    if ($firmId) $q->where('firm_id', $firmId);
                    if ($startDate) $q->whereDate('sale_date', '>=', $startDate);
                    if ($endDate) $q->whereDate('sale_date', '<=', $endDate);
                })
                ->with(['sale.customer', 'sale.firm']);

            $saleItems = $saleItemsQuery->get();
            foreach ($saleItems as $si) {
                if (!$si->sale) continue;
                $summary['total_sold_qty'] += $si->quantity;
                $summary['total_sold_amount'] += $si->subtotal;

                $movementHistory->push([
                    'timestamp' => $si->sale->sale_date ? \Carbon\Carbon::parse($si->sale->sale_date)->format('Y-m-d') : $si->created_at->format('Y-m-d'),
                    'raw_date'  => $si->sale->sale_date ?? $si->created_at,
                    'type'      => 'Sale',
                    'badge_class' => 'bg-primary text-white',
                    'direction' => 'OUT',
                    'ref_no'    => $si->sale->invoice_number,
                    'ref_route' => route('sales.show', $si->sale->id),
                    'party'     => $si->sale->customer->company_name ?? 'N/A',
                    'party_type'=> 'Customer',
                    'quantity'  => $si->quantity,
                    'rate'      => $si->unit_price,
                    'amount'    => $si->subtotal,
                    'notes'     => 'Project: ' . ($si->sale->project_name ?? '-'),
                ]);
            }

            // 3. Returnable Material Returns
            $returnItemsQuery = \App\Models\ReturnableMaterialItem::where('product_id', $selectedProduct->id)
                ->whereHas('returnableMaterial', function ($q) use ($firmId, $startDate, $endDate) {
                    if ($firmId) $q->where('firm_id', $firmId);
                    if ($startDate) $q->whereDate('return_date', '>=', $startDate);
                    if ($endDate) $q->whereDate('return_date', '<=', $endDate);
                })
                ->with(['returnableMaterial.customer', 'returnableMaterial.firm']);

            $returnItems = $returnItemsQuery->get();
            foreach ($returnItems as $ri) {
                if (!$ri->returnableMaterial) continue;
                $summary['total_returned_qty'] += $ri->quantity;
                $summary['total_returned_amount'] += $ri->subtotal;

                $movementHistory->push([
                    'timestamp' => $ri->returnableMaterial->return_date ? \Carbon\Carbon::parse($ri->returnableMaterial->return_date)->format('Y-m-d') : $ri->created_at->format('Y-m-d'),
                    'raw_date'  => $ri->returnableMaterial->return_date ?? $ri->created_at,
                    'type'      => 'Returnable',
                    'badge_class' => 'bg-warning text-dark',
                    'direction' => 'IN',
                    'ref_no'    => $ri->returnableMaterial->return_number,
                    'ref_route' => route('returnable.show', $ri->returnableMaterial->id),
                    'party'     => $ri->returnableMaterial->customer->company_name ?? 'N/A',
                    'party_type'=> 'Customer',
                    'quantity'  => $ri->quantity,
                    'rate'      => $ri->unit_price,
                    'amount'    => $ri->subtotal,
                    'notes'     => 'Reason: ' . ($ri->returnableMaterial->return_reason ?? '-'),
                ]);
            }

            // 4. Stock Adjustments
            $adjQuery = \App\Models\StockAdjustment::where('product_id', $selectedProduct->id)
                ->with(['user', 'firm']);
            if ($firmId) $adjQuery->where('firm_id', $firmId);
            if ($startDate) $adjQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $adjQuery->whereDate('created_at', '<=', $endDate);

            $adjItems = $adjQuery->get();
            foreach ($adjItems as $adj) {
                if ($adj->type === 'add') {
                    $summary['total_adjusted_add_qty'] += $adj->quantity;
                } else {
                    $summary['total_adjusted_sub_qty'] += $adj->quantity;
                }

                $movementHistory->push([
                    'timestamp' => $adj->created_at->format('Y-m-d'),
                    'raw_date'  => $adj->created_at,
                    'type'      => 'Adjustment (' . strtoupper($adj->type) . ')',
                    'badge_class' => $adj->type === 'add' ? 'bg-info text-white' : 'bg-danger text-white',
                    'direction' => $adj->type === 'add' ? 'IN' : 'OUT',
                    'ref_no'    => 'ADJ-' . $adj->id,
                    'ref_route' => null,
                    'party'     => $adj->user->name ?? 'System',
                    'party_type'=> 'User',
                    'quantity'  => $adj->quantity,
                    'rate'      => $selectedProduct->cost_price,
                    'amount'    => $adj->quantity * $selectedProduct->cost_price,
                    'notes'     => 'Reason: ' . $adj->reason,
                ]);
            }

            $movementHistory = $movementHistory->sortByDesc('raw_date')->values();

            $summary['current_stock'] = $selectedProduct->stock_quantity;
            $summary['cost_price']    = $selectedProduct->cost_price;
            $summary['selling_price'] = $selectedProduct->selling_price;
        }

        $firms = $user->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('reports.product_stock', compact(
            'products',
            'selectedProduct',
            'movementHistory',
            'summary',
            'firms'
        ));
    }

    public function userActivityReport(Request $request)
    {
        $authUser = auth()->user();
        $firmId = !$authUser->isSuperAdmin() ? $authUser->firm_id : ($request->input('firm_id') ?? null);

        $usersQuery = \App\Models\User::query();
        if (!$authUser->isSuperAdmin()) {
            $usersQuery->where('firm_id', $authUser->firm_id);
        } elseif ($firmId) {
            $usersQuery->where('firm_id', $firmId);
        }
        $users = $usersQuery->orderBy('name')->get();

        $selectedUserId = $request->input('user_id');
        $selectedUser = null;
        if ($selectedUserId) {
            $selectedUser = $users->firstWhere('id', $selectedUserId);
        }

        $startDate = $this->parseDateInput($request->input('start_date'));
        $endDate   = $this->parseDateInput($request->input('end_date'));

        // Query Purchases
        $purchasesQuery = Purchase::with(['supplier', 'items.product', 'firm', 'user']);
        if (!$authUser->isSuperAdmin()) {
            $purchasesQuery->where('firm_id', $authUser->firm_id);
        } elseif ($firmId) {
            $purchasesQuery->where('firm_id', $firmId);
        }
        if ($selectedUserId) {
            $purchasesQuery->where('user_id', $selectedUserId);
        }
        if ($startDate) $purchasesQuery->whereDate('purchase_date', '>=', $startDate);
        if ($endDate) $purchasesQuery->whereDate('purchase_date', '<=', $endDate);

        $purchases = $purchasesQuery->get();

        // Query Sales
        $salesQuery = Sale::with(['customer', 'items.product', 'firm', 'user']);
        if (!$authUser->isSuperAdmin()) {
            $salesQuery->where('firm_id', $authUser->firm_id);
        } elseif ($firmId) {
            $salesQuery->where('firm_id', $firmId);
        }
        if ($selectedUserId) {
            $salesQuery->where('user_id', $selectedUserId);
        }
        if ($startDate) $salesQuery->whereDate('sale_date', '>=', $startDate);
        if ($endDate) $salesQuery->whereDate('sale_date', '<=', $endDate);

        $sales = $salesQuery->get();

        // Query Returns
        $returnsQuery = ReturnableMaterial::with(['customer', 'items.product', 'firm', 'user']);
        if (!$authUser->isSuperAdmin()) {
            $returnsQuery->where('firm_id', $authUser->firm_id);
        } elseif ($firmId) {
            $returnsQuery->where('firm_id', $firmId);
        }
        if ($selectedUserId) {
            $returnsQuery->where('user_id', $selectedUserId);
        }
        if ($startDate) $returnsQuery->whereDate('return_date', '>=', $startDate);
        if ($endDate) $returnsQuery->whereDate('return_date', '<=', $endDate);

        $returns = $returnsQuery->get();

        // Query Stock Adjustments
        $adjQuery = \App\Models\StockAdjustment::with(['product', 'firm', 'user']);
        if (!$authUser->isSuperAdmin()) {
            $adjQuery->where('firm_id', $authUser->firm_id);
        } elseif ($firmId) {
            $adjQuery->where('firm_id', $firmId);
        }
        if ($selectedUserId) {
            $adjQuery->where('user_id', $selectedUserId);
        }
        if ($startDate) $adjQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $adjQuery->whereDate('created_at', '<=', $endDate);

        $adjustments = $adjQuery->get();

        // Summary Calculations
        $summary = [
            'purchase_count' => $purchases->count(),
            'purchase_amount' => $purchases->sum('grand_total'),
            'purchase_qty' => $purchases->sum(fn($p) => $p->items->sum('quantity')),

            'sale_count' => $sales->count(),
            'sale_amount' => $sales->sum('grand_total'),
            'sale_qty' => $sales->sum(fn($s) => $s->items->sum('quantity')),

            'return_count' => $returns->count(),
            'return_amount' => $returns->sum('grand_total'),
            'return_qty' => $returns->sum(fn($r) => $r->items->sum('quantity')),

            'adjustment_count' => $adjustments->count(),
            'total_entries' => $purchases->count() + $sales->count() + $returns->count() + $adjustments->count(),
        ];

        // Combined Ledger
        $entriesLedger = collect();

        foreach ($purchases as $p) {
            $prodNames = $p->items->map(fn($item) => $item->product->name ?? 'Product')->take(2)->join(', ');
            if ($p->items->count() > 2) {
                $prodNames .= ' +' . ($p->items->count() - 2) . ' more';
            }

            $entriesLedger->push([
                'raw_date' => $p->purchase_date ?? $p->created_at,
                'entry_type' => 'Purchase',
                'badge_class' => 'bg-success text-white',
                'ref_no' => $p->invoice_number,
                'ref_route' => route('purchases.show', $p->id),
                'party' => $p->supplier->company_name ?? 'N/A',
                'party_type' => 'Supplier',
                'items_count' => $p->items->count(),
                'products_summary' => $prodNames ?: 'No items',
                'amount' => $p->grand_total,
                'status' => ucfirst($p->payment_status ?? 'pending'),
                'entered_by' => $p->user->name ?? 'Unknown',
                'entered_by_role' => $p->user->role ?? 'User',
            ]);
        }

        foreach ($sales as $s) {
            $prodNames = $s->items->map(fn($item) => $item->product->name ?? 'Product')->take(2)->join(', ');
            if ($s->items->count() > 2) {
                $prodNames .= ' +' . ($s->items->count() - 2) . ' more';
            }

            $entriesLedger->push([
                'raw_date' => $s->sale_date ?? $s->created_at,
                'entry_type' => 'Sale',
                'badge_class' => 'bg-primary text-white',
                'ref_no' => $s->invoice_number,
                'ref_route' => route('sales.show', $s->id),
                'party' => $s->customer->company_name ?? 'N/A',
                'party_type' => 'Customer',
                'items_count' => $s->items->count(),
                'products_summary' => $prodNames ?: 'No items',
                'amount' => $s->grand_total,
                'status' => ucfirst($s->payment_status ?? 'pending'),
                'entered_by' => $s->user->name ?? 'Unknown',
                'entered_by_role' => $s->user->role ?? 'User',
            ]);
        }

        foreach ($returns as $r) {
            $prodNames = $r->items->map(fn($item) => $item->product->name ?? 'Product')->take(2)->join(', ');
            if ($r->items->count() > 2) {
                $prodNames .= ' +' . ($r->items->count() - 2) . ' more';
            }

            $entriesLedger->push([
                'raw_date' => $r->return_date ?? $r->created_at,
                'entry_type' => 'Returnable',
                'badge_class' => 'bg-warning text-dark',
                'ref_no' => $r->return_number,
                'ref_route' => route('returnable.show', $r->id),
                'party' => $r->customer->company_name ?? 'N/A',
                'party_type' => 'Customer',
                'items_count' => $r->items->count(),
                'products_summary' => $prodNames ?: 'No items',
                'amount' => $r->grand_total,
                'status' => 'Returned',
                'entered_by' => $r->user->name ?? 'Unknown',
                'entered_by_role' => $r->user->role ?? 'User',
            ]);
        }

        foreach ($adjustments as $adj) {
            $entriesLedger->push([
                'raw_date' => $adj->created_at,
                'entry_type' => 'Adjustment (' . strtoupper($adj->type) . ')',
                'badge_class' => $adj->type === 'add' ? 'bg-info text-white' : 'bg-danger text-white',
                'ref_no' => 'ADJ-' . $adj->id,
                'ref_route' => null,
                'party' => $adj->product->name ?? 'Product',
                'party_type' => 'Product',
                'items_count' => 1,
                'products_summary' => ($adj->product->name ?? 'Product') . ' (' . ($adj->type === 'add' ? '+' : '-') . $adj->quantity . ')',
                'amount' => $adj->quantity * ($adj->product->cost_price ?? 0),
                'status' => 'Adjusted',
                'entered_by' => $adj->user->name ?? 'Unknown',
                'entered_by_role' => $adj->user->role ?? 'User',
            ]);
        }

        $entriesLedger = $entriesLedger->sortByDesc('raw_date')->values();

        $firms = $authUser->isSuperAdmin() ? \App\Models\Firm::all() : collect();

        return view('reports.user_activity', compact(
            'users',
            'selectedUser',
            'summary',
            'entriesLedger',
            'firms'
        ));
    }
}

