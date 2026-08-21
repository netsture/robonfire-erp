@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page_title', 'Financial & Operational Reports')
@section('page_subtitle', 'Comprehensive business intelligence, inventory valuation, and P&L statements')

@section('content')

<div class="row g-3 mb-4">
    <!-- Sales Report Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-bar-chart-fill fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-dark mb-1">Sales Reports</h5>
            <p class="text-muted small mb-3">Itemized revenue by date range, customer ledgers, and payment statuses.</p>
            <a href="{{ route('reports.sales') }}" class="btn btn-outline-primary rounded-pill w-100 mt-auto fw-semibold">View Sales Report</a>
        </div>
    </div>

    <!-- Purchases Report Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-cart-check-fill fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-dark mb-1">Purchases Reports</h5>
            <p class="text-muted small mb-3">Track procurement expenses, supplier payables, and stock restocking history.</p>
            <a href="{{ route('reports.purchases') }}" class="btn btn-outline-info rounded-pill w-100 mt-auto fw-semibold">View Purchase Report</a>
        </div>
    </div>

    <!-- Inventory Valuation Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-boxes fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-dark mb-1">Stock Valuation</h5>
            <p class="text-muted small mb-3">Calculate total cost value of warehouse stock versus potential retail turnover.</p>
            <a href="{{ route('reports.inventory') }}" class="btn btn-outline-success rounded-pill w-100 mt-auto fw-semibold">Stock Valuation</a>
        </div>
    </div>

    <!-- Profit & Loss Card -->
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 mb-3 d-inline-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                <i class="bi bi-pie-chart-fill fs-4"></i>
            </div>
            <h5 class="fw-bold font-outfit text-dark mb-1">Profit & Loss</h5>
            <p class="text-muted small mb-3">Generate income statements subtracting Cost of Goods Sold from total sales revenue.</p>
            <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-warning rounded-pill w-100 mt-auto fw-semibold">P&L Statement</a>
        </div>
    </div>
</div>

<!-- Stock Valuation Summary Banner -->
<div class="card card-custom border-0 p-4 bg-dark text-white">
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
</div>

@endsection
