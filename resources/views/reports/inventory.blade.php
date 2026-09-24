@extends('layouts.app')

@section('title', 'Detailed Inventory Report')
@section('page_title', 'Detailed Inventory Valuation & Asset Report')
@section('page_subtitle', 'Comprehensive analysis of warehouse stock, asset valuation, profit margins, and inventory health')

@section('header_actions')
    <a href="{{ route('reports.inventory.print', request()->query()) }}" target="_blank" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
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
        <div class="card card-custom border-0 p-3 text-white" style="background: linear-gradient(135deg, #6f42c1 0%, #4c1d95 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Products</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalProducts) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Units</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalUnitsInStock) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-dark text-white">
            <span class="text-white small fw-semibold text-uppercase" style="color: #ffffff !important;">Stock Cost Value</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($totalCostValue, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-primary text-white">
            <span class="text-white small fw-semibold text-uppercase" style="color: #ffffff !important;">Retail Valuation</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($totalRetailValue, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-success text-white">
            <span class="text-white small fw-semibold text-uppercase" style="color: #ffffff !important;">Potential Profit</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($potentialProfit, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        @php
            if ($outOfStockCount > 0) {
                $alertCardClass = 'bg-danger text-white';
                $alertTextClass = 'text-white';
                $badgeOutClass = 'bg-white text-danger';
                $badgeLowClass = 'bg-warning text-dark';
            } elseif ($lowStockCount > 0) {
                $alertCardClass = 'bg-warning text-dark';
                $alertTextClass = 'text-dark';
                $badgeOutClass = 'bg-danger text-white';
                $badgeLowClass = 'bg-dark text-white';
            } else {
                $alertCardClass = 'bg-success text-white';
                $alertTextClass = 'text-white';
                $badgeOutClass = 'bg-white text-danger';
                $badgeLowClass = 'bg-white text-warning';
            }
        @endphp
        <div class="card card-custom border-0 p-3 {{ $alertCardClass }}">
            <span class="small fw-semibold text-uppercase {{ $alertTextClass }}" style="{{ $alertCardClass !== 'bg-warning text-dark' ? 'color: #ffffff !important;' : '' }}">Stock Alerts</span>
            <div class="mt-2">
                @if($outOfStockCount > 0)
                    <span class="badge {{ $badgeOutClass }} rounded-pill px-2.5 py-1 me-1 fw-bold" title="Out of Stock">{{ $outOfStockCount }} Out</span>
                @endif
                @if($lowStockCount > 0)
                    <span class="badge {{ $badgeLowClass }} rounded-pill px-2.5 py-1 fw-bold" title="Low Stock">{{ $lowStockCount }} Low</span>
                @endif
                @if($outOfStockCount == 0 && $lowStockCount == 0)
                    <span class="badge bg-white text-success rounded-pill px-2.5 py-1 fw-bold"><i class="bi bi-shield-check me-1"></i>Healthy</span>
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
            <label class="form-label small fw-semibold text-muted mb-1">Search Product Name / HSN Code</label>
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
                    <th>
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Product Name & Details
                            @if(request('sort_by') === 'name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'hsn_code', 'sort_order' => request('sort_by') === 'hsn_code' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            HSN Code
                            @if(request('sort_by') === 'hsn_code')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'category_name', 'sort_order' => request('sort_by') === 'category_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Category
                            @if(request('sort_by') === 'category_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'brand_name', 'sort_order' => request('sort_by') === 'brand_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Brand
                            @if(request('sort_by') === 'brand_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'unit', 'sort_order' => request('sort_by') === 'unit' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Type
                            @if(request('sort_by') === 'unit')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'stock_quantity', 'sort_order' => request('sort_by') === 'stock_quantity' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Stock Quantity
                            @if(request('sort_by') === 'stock_quantity')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'cost_price', 'sort_order' => request('sort_by') === 'cost_price' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Unit Cost (₹)
                            @if(request('sort_by') === 'cost_price')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'selling_price', 'sort_order' => request('sort_by') === 'selling_price' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Selling Price (₹)
                            @if(request('sort_by') === 'selling_price')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'total_cost_value', 'sort_order' => request('sort_by') === 'total_cost_value' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Total Cost Value (₹)
                            @if(request('sort_by') === 'total_cost_value')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'total_retail_value', 'sort_order' => request('sort_by') === 'total_retail_value' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Total Retail Value (₹)
                            @if(request('sort_by') === 'total_retail_value')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end pe-4">
                        <a href="{{ route('reports.inventory', array_merge(request()->query(), ['sort_by' => 'potential_profit', 'sort_order' => request('sort_by') === 'potential_profit' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Potential Profit (₹)
                            @if(request('sort_by') === 'potential_profit')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
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
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $product->name }}</div>
                                    @if($product->product_identifier)
                                        <div class="small font-monospace mt-0.5">
                                            <span class="badge text-dark fw-bold border border-warning py-0.5 px-1.5" style="background-color: #fef08a; color: #713f12 !important;">ID: {{ $product->product_identifier }}</span>
                                        </div>
                                    @endif
                                </div>
                                <a href="{{ route('reports.product-stock', ['product_id' => $product->id]) }}" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-0.5 small no-print ms-2" title="View Product Movement Ledger">
                                    <i class="bi bi-clock-history me-1"></i>Ledger
                                </a>
                            </div>
                        </td>
                        <td>
                            @if($product->hsn_code)
                                <span class="badge font-monospace px-2.5 py-1 fs-6 fw-semibold" style="background-color: #e0e7ff; color: #3730a3 !important; border: 1px solid #c7d2fe !important;">
                                    {{ $product->hsn_code }}
                                </span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td><span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-tag-fill me-1"></i>{{ $product->category->name ?? 'N/A' }}</span></td>
                        <td>
                            @if($product->brand)
                                <span class="badge text-white rounded-pill px-2.5 py-1 small" style="background-color: #6f42c1;"><i class="bi bi-award-fill me-1"></i>{{ $product->brand->name }}</span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td><span class="badge bg-info text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-rulers me-1"></i>{{ $product->unit ?? 'Pcs' }}</span></td>
                        <td class="text-center">
                            @if($product->stock_quantity <= 0)
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold"><i class="bi bi-x-circle-fill me-1"></i>0 {{ $product->unit }} (OUT)</span>
                            @elseif($product->stock_quantity <= $product->alert_quantity)
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $product->stock_quantity }} {{ $product->unit }} (LOW)</span>
                            @else
                                <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>{{ $product->stock_quantity }} {{ $product->unit }}</span>
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
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 13 : 12 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            No inventory products match the selected criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($products) > 0)
                <tfoot class="table-light border-top">
                    <tr class="fw-bold">
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 7 : 6 }}" class="ps-4 text-end">TOTALS:</td>
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
