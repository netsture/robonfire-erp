@extends('layouts.app')

@section('title', 'Detailed Inventory Report')
@section('page_title', 'Detailed Inventory Valuation & Asset Report')
@section('page_subtitle', 'Comprehensive analysis of warehouse stock, asset valuation, profit margins, and inventory health')

@section('header_actions')
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium me-2">
        <i class="bi bi-printer me-1"></i> Print Report
    </button>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Detailed Inventory Valuation & Stock Health Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Products</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">{{ number_format($totalProducts) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Units</span>
            <h3 class="fw-bold font-outfit text-primary mt-2 mb-0">{{ number_format($totalUnitsInStock) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-dark text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Stock Cost Value</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($totalCostValue, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-primary text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Retail Valuation</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($totalRetailValue, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-success text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Potential Profit</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($potentialProfit, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Stock Alerts</span>
            <div class="mt-2">
                @if($outOfStockCount > 0)
                    <span class="badge bg-danger rounded-pill px-2 py-1 me-1" title="Out of Stock">{{ $outOfStockCount }} Out</span>
                @endif
                @if($lowStockCount > 0)
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1" title="Low Stock">{{ $lowStockCount }} Low</span>
                @endif
                @if($outOfStockCount == 0 && $lowStockCount == 0)
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Healthy</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.inventory') }}" class="row g-2 align-items-end">
        @if(auth()->user()->isSuperAdmin() && count($firms) > 0)
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Firm</label>
                <select name="firm_id" class="form-select form-select-sm">
                    <option value="">All Firms</option>
                    @foreach($firms as $firm)
                        <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 col-md-3">
            <label class="form-label small fw-semibold text-muted mb-1">Search Product / HSN</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Category</label>
            <select name="category_id" class="form-select form-select-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Brand</label>
            <select name="brand_id" class="form-select form-select-sm">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Stock Status</label>
            <select name="stock_status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="in_stock" {{ request('stock_status') == 'in_stock' ? 'selected' : '' }}>In Stock</option>
                <option value="low_stock" {{ request('stock_status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                <option value="out_of_stock" {{ request('stock_status') == 'out_of_stock' ? 'selected' : '' }}>Out of Stock</option>
            </select>
        </div>

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('reports.inventory') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Detailed Inventory Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Product Name & Details</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th class="text-center">Stock Quantity</th>
                    <th class="text-end">Unit Cost (₹)</th>
                    <th class="text-end">Selling Price (₹)</th>
                    <th class="text-end">Total Cost Value (₹)</th>
                    <th class="text-end">Total Retail Value (₹)</th>
                    <th class="text-end pe-4">Potential Profit (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $idx => $product)
                    @php
                        $itemCostVal = $product->stock_quantity * $product->cost_price;
                        $itemRetailVal = $product->stock_quantity * $product->selling_price;
                        $itemProfit = $itemRetailVal - $itemCostVal;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted small">{{ $idx + 1 }}</td>
                        @if(auth()->user()->isSuperAdmin())
                            <td><span class="badge bg-dark-subtle text-dark border">{{ $product->firm->name ?? 'N/A' }}</span></td>
                        @endif
                        <td>
                            <div class="fw-semibold text-dark">{{ $product->name }}</div>
                            <div class="small text-muted font-monospace">
                                HSN: {{ $product->hsn_code ?? 'N/A' }}
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $product->category->name ?? 'N/A' }}</span></td>
                        <td><span class="badge bg-secondary-subtle text-secondary border">{{ $product->brand->name ?? 'N/A' }}</span></td>
                        <td><span class="badge bg-light text-secondary border px-2">{{ $product->unit ?? 'Pcs' }}</span></td>
                        <td class="text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1 rounded-pill">0 {{ $product->unit }} (OUT)</span>
                            @elseif($product->stock_quantity <= $product->alert_quantity)
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-1 rounded-pill">{{ $product->stock_quantity }} {{ $product->unit }} (LOW)</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 rounded-pill">{{ $product->stock_quantity }} {{ $product->unit }}</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace">₹{{ number_format($product->cost_price, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($product->selling_price, 2) }}</td>
                        <td class="text-end font-monospace fw-semibold text-dark">₹{{ number_format($itemCostVal, 2) }}</td>
                        <td class="text-end font-monospace fw-semibold text-primary">₹{{ number_format($itemRetailVal, 2) }}</td>
                        <td class="text-end pe-4 font-monospace fw-bold text-success">
                            ₹{{ number_format($itemProfit, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 12 : 11 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            No inventory products match the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($products) > 0)
                <tfoot class="table-light border-top">
                    <tr class="fw-bold">
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 6 : 5 }}" class="ps-4 text-end">TOTALS:</td>
                        <td class="text-center text-primary fs-6">{{ number_format($totalUnitsInStock) }}</td>
                        <td colspan="2"></td>
                        <td class="text-end font-monospace text-dark">₹{{ number_format($totalCostValue, 2) }}</td>
                        <td class="text-end font-monospace text-primary">₹{{ number_format($totalRetailValue, 2) }}</td>
                        <td class="text-end pe-4 font-monospace text-success fs-6">₹{{ number_format($potentialProfit, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>

<style>
@media print {
    .no-print, nav, sidebar, header, .btn, .header_actions { display: none !important; }
    body { background: #fff !important; padding: 0 !important; color: #000 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .table { width: 100% !important; border: 1px solid #dee2e6 !important; }
}
</style>

@endsection
