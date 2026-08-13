<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $totalSales = Sale::sum('grand_total');
        $totalPurchases = Purchase::sum('grand_total');
        $totalProducts = Product::count();

        // Stock Valuation
        $inventoryCostValue = Product::selectRaw('SUM(stock_quantity * cost_price) as cost_val')->value('cost_val') ?? 0;
        $inventoryRetailValue = Product::selectRaw('SUM(stock_quantity * selling_price) as retail_val')->value('retail_val') ?? 0;
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
        $query = Sale::with('customer');

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

        $sales = $query->latest()->paginate(15);
        $totalRevenue = (clone $query)->sum('grand_total');
        $totalPaid = (clone $query)->sum('paid_amount');
        $customers = Customer::all();

        return view('reports.sales', compact('sales', 'totalRevenue', 'totalPaid', 'customers'));
    }

    public function purchasesReport(Request $request)
    {
        $query = Purchase::with('supplier');

        if ($request->filled('start_date')) {
            $query->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchase_date', '<=', $request->end_date);
        }
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        $purchases = $query->latest()->paginate(15);
        $totalCost = (clone $query)->sum('grand_total');
        $totalPaid = (clone $query)->sum('paid_amount');
        $suppliers = Supplier::all();

        return view('reports.purchases', compact('purchases', 'totalCost', 'totalPaid', 'suppliers'));
    }

    public function inventoryReport()
    {
        $products = Product::with('category')->latest()->get();
        $totalCostValue = Product::selectRaw('SUM(stock_quantity * cost_price) as val')->value('val') ?? 0;
        $totalRetailValue = Product::selectRaw('SUM(stock_quantity * selling_price) as val')->value('val') ?? 0;

        return view('reports.inventory', compact('products', 'totalCostValue', 'totalRetailValue'));
    }

    public function profitLossReport(Request $request)
    {
        $salesQuery = Sale::query();
        $purchasesQuery = Purchase::query();

        if ($request->filled('start_date')) {
            $salesQuery->whereDate('sale_date', '>=', $request->start_date);
            $purchasesQuery->whereDate('purchase_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $salesQuery->whereDate('sale_date', '<=', $request->end_date);
            $purchasesQuery->whereDate('purchase_date', '<=', $request->end_date);
        }

        $grossSales = $salesQuery->sum('subtotal');
        $salesDiscounts = $salesQuery->sum('discount_amount');
        $salesTaxes = $salesQuery->sum('tax_amount');
        $netSales = $grossSales - $salesDiscounts;

        $grossPurchases = $purchasesQuery->sum('subtotal');
        $purchaseDiscounts = $purchasesQuery->sum('discount_amount');
        $netPurchases = $grossPurchases - $purchaseDiscounts;

        $netProfit = $netSales - $netPurchases;

        return view('reports.profit_loss', compact(
            'grossSales',
            'salesDiscounts',
            'salesTaxes',
            'netSales',
            'grossPurchases',
            'purchaseDiscounts',
            'netPurchases',
            'netProfit'
        ));
    }
}
