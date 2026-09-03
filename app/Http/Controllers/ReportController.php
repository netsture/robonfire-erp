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
            $query->whereDate('sale_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sale_date', '<=', $request->end_date);
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
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
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
            $salesQuery->whereDate('sale_date', '>=', $request->start_date);
            $purchasesQuery->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $salesQuery->whereDate('sale_date', '<=', $request->end_date);
            $purchasesQuery->whereDate('purchase_date', '<=', $request->end_date);
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
            $query->whereDate('return_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('return_date', '<=', $request->end_date);
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
}

