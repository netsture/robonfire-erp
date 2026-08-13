<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    /**
     * Display the ERP Admin Dashboard.
     */
    public function index()
    {
        // Safe checks for models created in subsequent phases
        $totalUsersCount = User::count();
        $totalCustomersCount = Schema::hasTable('customers') ? Customer::count() : 0;
        $totalSuppliersCount = Schema::hasTable('suppliers') ? Supplier::count() : 0;
        $totalProductsCount = Schema::hasTable('products') ? Product::count() : 0;
        
        $totalSalesAmount = Schema::hasTable('sales') ? Sale::sum('grand_total') : 0;
        $totalPurchasesAmount = Schema::hasTable('purchases') ? Purchase::sum('grand_total') : 0;
        
        $lowStockCount = Schema::hasTable('products') ? Product::whereColumn('stock_quantity', '<=', 'alert_quantity')->count() : 0;

        $recentSales = Schema::hasTable('sales') ? Sale::with('customer')->latest()->take(5)->get() : collect();
        $recentPurchases = Schema::hasTable('purchases') ? Purchase::with('supplier')->latest()->take(5)->get() : collect();

        // Chart Data (Last 6 Months)
        $chartLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $salesData = [12500, 19200, 15400, 22100, 28500, 34200];
        $purchaseData = [8000, 12000, 9500, 14000, 18000, 21000];

        return view('dashboard', compact(
            'totalUsersCount',
            'totalCustomersCount',
            'totalSuppliersCount',
            'totalProductsCount',
            'totalSalesAmount',
            'totalPurchasesAmount',
            'lowStockCount',
            'recentSales',
            'recentPurchases',
            'chartLabels',
            'salesData',
            'purchaseData'
        ));
    }
}
