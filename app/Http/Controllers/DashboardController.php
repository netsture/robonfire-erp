<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Party;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        // If super admin, show tenant statistics
        if (auth()->user()->hasRole('Super Admin')) {
            $totalTenants = Tenant::count();
            $activeTenants = Tenant::where('status', 'Active')->count();
            $inactiveTenants = Tenant::where('status', 'Inactive')->count();
            
            return view('admin.dashboard', compact('totalTenants', 'activeTenants', 'inactiveTenants'));
        }

        // Else, show tenant-wise dashboard
        $totalInwardsVal = Purchase::where('type', 'inward')->sum('grand_total_with_tax');
        $inwardCount = Purchase::where('type', 'inward')->count();
        $outwardCount = Purchase::where('type', 'outward')->count();
        $activeParties = Party::count();

        // Prepare chart data: Inwards total with tax by month for the current year
        $currentYear = date('Y');
        $monthlyTotals = Purchase::where('type', 'inward')
            ->whereYear('date', $currentYear)
            ->selectRaw('MONTH(date) as month, SUM(grand_total_with_tax) as total')
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->all();

        $chartData = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartData[] = (float)($monthlyTotals[$m] ?? 0.00);
        }

        return view('dashboard', compact(
            'totalInwardsVal',
            'inwardCount',
            'outwardCount',
            'activeParties',
            'chartData'
        ));
    }
}
