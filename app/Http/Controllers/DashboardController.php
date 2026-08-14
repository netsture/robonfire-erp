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
        $user = auth()->user();
        $isSuper = $user->isSuperAdmin();
        $firmId = $user->firm_id;

        // Safe checks for models created in subsequent phases
        $usersQuery = User::query();
        $customersQuery = Customer::query();
        $suppliersQuery = Supplier::query();
        $productsQuery = Product::query();
        $salesQuery = Sale::query();
        $purchasesQuery = Purchase::query();

        if (!$isSuper) {
            $usersQuery->where('firm_id', $firmId)
                       ->where(function ($q) {
                           $q->whereNull('role')
                             ->orWhere(function ($subQ) {
                                 $subQ->where('role', '!=', 'superadmin')
                                      ->where('role', '!=', 'Superadmin');
                             });
                       })
                       ->whereDoesntHave('roles', function ($q) {
                           $q->where('slug', 'superadmin');
                       });
            $customersQuery->where('firm_id', $firmId);
            $suppliersQuery->where('firm_id', $firmId);
            $productsQuery->where('firm_id', $firmId);
            $salesQuery->where('firm_id', $firmId);
            $purchasesQuery->where('firm_id', $firmId);
        }

        $totalUsersCount = $usersQuery->count();
        $totalCustomersCount = Schema::hasTable('customers') ? $customersQuery->count() : 0;
        $totalSuppliersCount = Schema::hasTable('suppliers') ? $suppliersQuery->count() : 0;
        $totalProductsCount = Schema::hasTable('products') ? $productsQuery->count() : 0;
        
        $totalSalesAmount = Schema::hasTable('sales') ? $salesQuery->sum('grand_total') : 0;
        $totalPurchasesAmount = Schema::hasTable('purchases') ? $purchasesQuery->sum('grand_total') : 0;
        
        $lowStockQuery = Product::whereColumn('stock_quantity', '<=', 'alert_quantity');
        if (!$isSuper) {
            $lowStockQuery->where('firm_id', $firmId);
        }
        $lowStockCount = Schema::hasTable('products') ? $lowStockQuery->count() : 0;

        $recentSalesQuery = Sale::with('customer');
        $recentPurchasesQuery = Purchase::with('supplier');
        if (!$isSuper) {
            $recentSalesQuery->where('firm_id', $firmId);
            $recentPurchasesQuery->where('firm_id', $firmId);
        }

        $recentSales = Schema::hasTable('sales') ? $recentSalesQuery->latest()->take(5)->get() : collect();
        $recentPurchases = Schema::hasTable('purchases') ? $recentPurchasesQuery->latest()->take(5)->get() : collect();

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
