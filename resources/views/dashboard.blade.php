@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Welcome back, ' . Auth::user()->name)
@section('page_subtitle', 'Real-time overview of business operations, sales performance, and stock status')

@section('header_actions')
    @if(auth()->user()->isSuperAdmin())
        <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
            <select name="firm_id" class="form-select bg-light rounded-pill font-outfit" onchange="this.form.submit()">
                <option value="">All Firms (Global Overview)</option>
                @foreach($firms as $firm)
                    <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                @endforeach
            </select>
            @if(request('firm_id'))
                <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light border rounded-circle" title="Clear filter"><i class="bi bi-x"></i></a>
            @endif
        </form>
    @else
        <a href="{{ route('sales.index') }}" class="btn btn-primary rounded-pill px-3 shadow-sm font-outfit fw-medium">
            <i class="bi bi-cart-plus me-1"></i> New Sale Order
        </a>
        <a href="{{ route('purchases.index') }}" class="btn btn-warning text-dark rounded-pill px-3 shadow-sm font-outfit fw-medium">
            <i class="bi bi-bag-plus me-1"></i> Record Purchase
        </a>
    @endif
@endsection

@section('content')

<!-- Metric Cards Row -->
<div class="row g-3 mb-4">
    <!-- Total Purchases -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 h-100 p-3 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-bold small text-uppercase tracking-wider" style="color: #ffffff !important;">Total Purchases</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-1" style="color: #ffffff !important;">₹{{ number_format($totalPurchasesAmount, 2) }}</h3>
                    <span class="badge bg-white fw-bold rounded-pill" style="color: #0284c7;">
                        <i class="bi bi-cart-check me-1"></i>Active Procurement
                    </span>
                </div>
                <div class="metric-icon dark-symbol-avatar shadow-sm" style="background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                    <i class="bi bi-truck fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 h-100 p-3 text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-bold small text-uppercase tracking-wider" style="color: #ffffff !important;">Total Sales Revenue</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-1" style="color: #ffffff !important;">₹{{ number_format($totalSalesAmount, 2) }}</h3>
                    <span class="badge bg-white text-success fw-bold rounded-pill">
                        <i class="bi bi-arrow-up-right me-1"></i>+14.2% MoM
                    </span>
                </div>
                <div class="metric-icon dark-symbol-avatar shadow-sm" style="background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                    <i class="bi bi-currency-rupee fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Products -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 h-100 p-3 text-white" style="background: linear-gradient(135deg, #6f42c1 0%, #4c1d95 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-bold small text-uppercase tracking-wider" style="color: #ffffff !important;">Total Products</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-1" style="color: #ffffff !important;">{{ number_format($totalProductsCount) }}</h3>
                    <span class="badge bg-white text-dark fw-bold rounded-pill"><i class="bi bi-box-seam me-1"></i>In Catalog</span>
                </div>
                <div class="metric-icon dark-symbol-avatar shadow-sm" style="background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                    <i class="bi bi-boxes fs-3"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Warnings -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 h-100 p-3 text-white" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="fw-bold small text-uppercase tracking-wider" style="color: #ffffff !important;">Low Stock Warnings</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-1" style="color: #ffffff !important;">{{ $lowStockCount }}</h3>
                    @if($lowStockCount > 0)
                        <span class="badge bg-white text-danger fw-bold rounded-pill">
                            <i class="bi bi-exclamation-triangle me-1"></i>Requires Restock
                        </span>
                    @else
                        <span class="badge bg-white text-success fw-bold rounded-pill">
                            <i class="bi bi-shield-check me-1"></i>Inventory Healthy
                        </span>
                    @endif
                </div>
                <div class="metric-icon dark-symbol-avatar shadow-sm" style="background: rgba(15, 23, 42, 0.4) !important; border: 1px solid rgba(255,255,255,0.2) !important; color: #ffffff !important;">
                    <i class="bi bi-exclamation-diamond fs-3"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts & Quick Actions Row -->
<div class="row g-3 mb-4 align-items-stretch">
    <!-- Revenue & Expense Analytics Graph -->
    <div class="col-12 col-lg-8">
        <div class="card card-custom border-0 p-4 h-100 d-flex flex-column">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold font-outfit mb-0 text-dark">Revenue & Expense Analytics</h5>
                    <span class="text-muted small">Monthly Comparison (Sales vs. Purchases)</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge px-3 py-2 rounded-pill fw-semibold" style="background-color: #0f172a !important; color: #ffffff !important;">
                        <i class="bi bi-calendar3 me-1 text-warning"></i> Last 6 Months
                    </span>
                    <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-primary text-white rounded-pill px-3 fw-medium shadow-sm">
                        <i class="bi bi-bar-chart-line me-1"></i> Full Report
                    </a>
                </div>
            </div>
            <div class="flex-grow-1 w-100 position-relative" style="min-height: 380px;">
                <canvas id="salesAnalyticsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- ERP Shortcuts Quick Links -->
    <div class="col-12 col-lg-4">
        <div class="card card-custom border-0 p-4 h-100 d-flex flex-column">
            <h5 class="fw-bold font-outfit mb-3 text-dark">ERP Shortcuts</h5>
            
            <div class="list-group list-group-flush gap-2 border-0 flex-grow-1 d-flex flex-column justify-content-between">
                <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-customer me-3" style="width: 42px; height: 42px;">
                            <i class="bi bi-people fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Manage Customers</div>
                            <div class="text-muted small">View client ledgers & directory</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('suppliers.index') }}" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-supplier me-3" style="width: 42px; height: 42px;">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Manage Suppliers</div>
                            <div class="text-muted small">Vendor directory & balance ledgers</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('products.index') }}" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-product me-3" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-seam fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Add New Product</div>
                            <div class="text-muted small">Update price & alert thresholds</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('sales.index') }}" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-customer me-3" style="width: 42px; height: 42px;">
                            <i class="bi bi-cart-plus fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Create Sales Order</div>
                            <div class="text-muted small">Process customer orders & print invoice</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>

                <a href="{{ route('purchases.index') }}" class="list-group-item list-group-item-action border rounded-3 p-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-supplier me-3" style="width: 42px; height: 42px;">
                            <i class="bi bi-receipt-cutoff fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-semibold text-dark">Record Purchase</div>
                            <div class="text-muted small">Log vendor purchase orders & inventory</div>
                        </div>
                    </div>
                    <i class="bi bi-chevron-right text-muted"></i>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- System Status Bar -->
<!--<div class="card card-custom border-0 p-3 bg-white">
    <div class="row align-items-center text-center text-md-start">
        <div class="col-md-3 mb-2 mb-md-0 border-end border-light-subtle">
            <div class="text-muted small">Active User Session</div>
            <div class="fw-bold text-dark">{{ Auth::user()->name }} ({{ Auth::user()->role }})</div>
        </div>
        <div class="col-md-3 mb-2 mb-md-0 border-end border-light-subtle">
            <div class="text-muted small">Total Registered Users</div>
            <div class="fw-bold text-dark">{{ $totalUsersCount }} User Accounts</div>
        </div>
        <div class="col-md-3 mb-2 mb-md-0 border-end border-light-subtle">
            <div class="text-muted small">Database Engine</div>
            <div class="fw-bold text-dark"><i class="bi bi-database me-1 text-primary"></i>Mysql / Laravel 12</div>
        </div>
        <div class="col-md-3">
            <div class="text-muted small">System Health</div>
            <div class="fw-bold text-success"><i class="bi bi-check-circle-fill me-1"></i>100% Operational</div>
        </div>
    </div>
</div>-->

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('salesAnalyticsChart').getContext('2d');

        // Create Linear Gradients for Sales & Purchases
        const salesGradient = ctx.createLinearGradient(0, 0, 0, 350);
        salesGradient.addColorStop(0, 'rgba(99, 102, 241, 0.22)');
        salesGradient.addColorStop(1, 'rgba(99, 102, 241, 0.0)');

        const purchaseGradient = ctx.createLinearGradient(0, 0, 0, 350);
        purchaseGradient.addColorStop(0, 'rgba(245, 158, 11, 0.18)');
        purchaseGradient.addColorStop(1, 'rgba(245, 158, 11, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [
                    {
                        label: 'Sales Revenue',
                        data: @json($salesData),
                        borderColor: '#6366f1',
                        backgroundColor: salesGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 3,
                        pointRadius: 4,
                        pointBackgroundColor: '#6366f1',
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Purchases / Expenses',
                        data: @json($purchaseData),
                        borderColor: '#f59e0b',
                        backgroundColor: purchaseGradient,
                        fill: true,
                        tension: 0.35,
                        borderWidth: 2.5,
                        pointRadius: 4,
                        pointBackgroundColor: '#f59e0b',
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        align: 'end',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            color: '#1e293b',
                            font: { family: 'Plus Jakarta Sans', size: 12, weight: '600' }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += '₹' + context.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grace: '10%',
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Plus Jakarta Sans', size: 11 },
                            callback: function(value) { return '₹' + value.toLocaleString(); }
                        }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                    }
                }
            }
        });
    });
</script>
@endpush
