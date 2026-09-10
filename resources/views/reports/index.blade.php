@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page_title', 'Financial & Operational Reports')
@section('page_subtitle', 'Comprehensive business intelligence, inventory valuation, and P&L statements')

@section('content')

<div class="row g-3 mb-4">
    <!-- Purchases Report Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="rounded-circle dark-symbol-avatar shadow-sm mb-3" style="width: 52px; height: 52px; background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                <i class="bi bi-cart-check-fill fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-white mb-1" style="color: #ffffff !important;">Purchases Reports</h5>
            <p class="text-white small mb-3" style="color: #ffffff !important; opacity: 0.95;">Track procurement expenses, supplier payables, and stock restocking history.</p>
            <a href="{{ route('reports.purchases') }}" class="btn btn-white rounded-pill w-100 mt-auto fw-bold shadow-sm" target="_blank" style="background-color: #ffffff; color: #0369a1 !important;">View Purchase Report</a>
        </div>
    </div>

    <!-- Sales Report Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100 text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="rounded-circle dark-symbol-avatar shadow-sm mb-3" style="width: 52px; height: 52px; background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                <i class="bi bi-bar-chart-fill fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-white mb-1" style="color: #ffffff !important;">Sales Reports</h5>
            <p class="text-white small mb-3" style="color: #ffffff !important; opacity: 0.95;">Itemized revenue by date range, customer ledgers, and payment statuses.</p>
            <a href="{{ route('reports.sales') }}" class="btn btn-white rounded-pill w-100 mt-auto fw-bold shadow-sm" target="_blank" style="background-color: #ffffff; color: #047857 !important;">View Sales Report</a>
        </div>
    </div>
    
    <!-- Inventory Valuation Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100 text-white" style="background: linear-gradient(135deg, #6f42c1 0%, #4c1d95 100%);">
            <div class="rounded-circle dark-symbol-avatar shadow-sm mb-3" style="width: 52px; height: 52px; background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                <i class="bi bi-boxes fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-white mb-1" style="color: #ffffff !important;">Stock Valuation</h5>
            <p class="text-white small mb-3" style="color: #ffffff !important; opacity: 0.95;">Calculate total cost value of warehouse stock versus potential retail turnover.</p>
            <a href="{{ route('reports.inventory') }}" class="btn btn-white rounded-pill w-100 mt-auto fw-bold shadow-sm" target="_blank" style="background-color: #ffffff; color: #4c1d95 !important;">Stock Valuation</a>
        </div>
    </div>

    <!-- Return Product Report Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100 text-white" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="rounded-circle dark-symbol-avatar shadow-sm mb-3" style="width: 52px; height: 52px; background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                <i class="bi bi-box-arrow-in-left fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-white mb-1" style="color: #ffffff !important;">Return Product Report</h5>
            <p class="text-white small mb-3" style="color: #ffffff !important; opacity: 0.95;">Itemized returned materials history by date range, customer, and valuation.</p>
            <a href="{{ route('reports.return-products') }}" class="btn btn-white rounded-pill w-100 mt-auto fw-bold shadow-sm" target="_blank" style="background-color: #ffffff; color: #d97706 !important;">View Return Report</a>
        </div>
    </div>

    <!-- Profit & Loss Card -->
    <!--<div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-pie-chart-fill fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-dark mb-1">Profit & Loss</h5>
            <p class="text-muted small mb-3">Generate income statements subtracting Cost of Goods Sold from total sales revenue.</p>
            <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-warning rounded-pill w-100 mt-auto fw-semibold">P&L Statement</a>
        </div>
    </div>-->
</div>

<!-- Stock Valuation Summary Banner -->
<!--<div class="card card-custom border-0 p-4 bg-dark text-white">
    <div class="row align-items-center text-center text-md-start">
        <div class="col-md-4 mb-3 mb-md-0 border-end border-secondary border-opacity-20">
            <span class="text-white-50 small fw-semibold">CURRENT INVENTORY COST VALUE</span>
            <h2 class="fw-bold font-outfit mt-1 mb-0">₹{{ number_format($inventoryCostValue, 2) }}</h2>
        </div>
        <div class="col-md-4 mb-3 mb-md-0 border-end border-secondary border-opacity-20">
            <span class="text-white-50 small fw-semibold">RETAIL TURNOVER POTENTIAL</span>
            <h2 class="fw-bold font-outfit mt-1 mb-0 text-info">₹{{ number_format($inventoryRetailValue, 2) }}</h2>
        </div>
        <div class="col-md-4">
            <span class="text-white-50 small fw-semibold">POTENTIAL GROSS MARGIN</span>
            <h2 class="fw-bold font-outfit mt-1 mb-0 text-success">₹{{ number_format($potentialProfit, 2) }}</h2>
        </div>
    </div>
</div>-->

@endsection
