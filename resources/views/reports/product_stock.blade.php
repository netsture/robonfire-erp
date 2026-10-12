@extends('layouts.app')

@section('title', 'Product Stock Details Report')
@section('page_title', 'Product Stock Ledger & Movement Report')
@section('page_subtitle', 'Comprehensive history covering purchases, sales, returns, and manual adjustments for any selected product')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        border-radius: 0.375rem !important;
        padding: 0.45rem 0.75rem !important;
        border-color: #dee2e6 !important;
        font-size: 0.9rem;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        border: 1px solid #e2e8f0 !important;
        z-index: 1055 !important;
    }
</style>
@endpush

@section('header_actions')
    @if($selectedProduct)
        <a href="{{ route('reports.product-stock.print', request()->query()) }}" target="_blank" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
            <i class="bi bi-printer me-1"></i> Print Report
        </a>
    @endif
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Product Stock Movement & Ledger Report</h5>
    @if($selectedProduct)
        <h6 class="fw-bold text-dark mt-2 mb-0">{{ $selectedProduct->name }} (Code: {{ $selectedProduct->product_identifier ?? 'N/A' }})</h6>
    @endif
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- Summary Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Purchase Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Purchased</span>
                <i class="bi bi-cart-plus-fill fs-4" style="color: #ffffff !important;"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">+{{ number_format($summary['total_purchased_qty']) }} {{ $selectedProduct->unit ?? 'Units' }}</h3>
            <span class="small mt-1" style="color: #ffffff !important; opacity: 0.9;">Valuation: ₹{{ number_format($summary['total_purchased_amount'], 2) }}</span>
        </div>
    </div>

    <!-- Sales Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Sold</span>
                <i class="bi bi-bag-check-fill fs-4" style="color: #ffffff !important;"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">-{{ number_format($summary['total_sold_qty']) }} {{ $selectedProduct->unit ?? 'Units' }}</h3>
            <span class="small mt-1" style="color: #ffffff !important; opacity: 0.9;">Revenue: ₹{{ number_format($summary['total_sold_amount'], 2) }}</span>
        </div>
    </div>

    <!-- Returns Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Returned</span>
                <i class="bi bi-box-arrow-in-left fs-4" style="color: #ffffff !important;"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">+{{ number_format($summary['total_returned_qty']) }} {{ $selectedProduct->unit ?? 'Units' }}</h3>
            <span class="small mt-1" style="color: #ffffff !important; opacity: 0.9;">Valuation: ₹{{ number_format($summary['total_returned_amount'], 2) }}</span>
        </div>
    </div>

    <!-- In-Stock Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 bg-dark text-white h-100 justify-content-center">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white small fw-semibold text-uppercase">CURRENT IN-STOCK</span>
                <i class="bi bi-boxes fs-4 text-warning"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0 {{ ($selectedProduct && $selectedProduct->isLowStock()) ? 'text-danger' : 'text-success' }}">
                {{ number_format($summary['current_stock']) }} {{ $selectedProduct->unit ?? 'Units' }}
            </h3>
            <span class="small text-white-50 mt-1">Cost: ₹{{ number_format($summary['cost_price'], 2) }} | Selling: ₹{{ number_format($summary['selling_price'], 2) }}</span>
        </div>
    </div>
</div>

<!-- Product Selection & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.product-stock') }}" class="row g-2 align-items-end" id="stockReportForm">
        @if(auth()->user()->isSuperAdmin() && count($firms) > 0)
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Firm</label>
                <select name="firm_id" class="form-select form-select-sm" onchange="document.getElementById('stockReportForm').submit()">
                    <option value="">All Firms</option>
                    @foreach($firms as $firm)
                        <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-4' : 'col-md-5' }}">
            <label class="form-label small fw-semibold text-dark mb-1">Select Product <span class="text-danger">*</span></label>
            <select name="product_id" id="product_id_select" class="form-select form-select-sm">
                <option value="">Search or Select Product...</option>
                @foreach($products as $prod)
                    @php
                        $catStr = $prod->category ? '[' . $prod->category->name . ']' : '';
                        $brandStr = $prod->brand ? '[' . $prod->brand->name . ']' : '';
                        $idStr = $prod->product_identifier ? '[' . $prod->product_identifier . ']' : '';
                        $label = trim("{$prod->name} {$catStr} {$brandStr} {$idStr}");
                    @endphp
                    <option value="{{ $prod->id }}" {{ ($selectedProduct && $selectedProduct->id == $prod->id) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Start Date</label>
            <input type="text" name="start_date" class="form-control form-control-sm datepicker-ddmmyyyy" value="{{ request('start_date') }}" placeholder="DD-MM-YYYY">
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">End Date</label>
            <input type="text" name="end_date" class="form-control form-control-sm datepicker-ddmmyyyy" value="{{ request('end_date') }}" placeholder="DD-MM-YYYY">
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm w-100 rounded-3"><i class="bi bi-filter me-1"></i>Filter</button>
            <a href="{{ route('reports.product-stock') }}" class="btn btn-light border btn-sm w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

@if(!$selectedProduct)
    <!-- No Product Found Error Message Display -->
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-3 p-4 mb-4 rounded-3" role="alert">
        <i class="bi bi-exclamation-octagon-fill fs-2 text-danger"></i>
        <div>
            <h5 class="fw-bold mb-1 text-danger">No Product Records Found</h5>
            <p class="mb-0 text-muted small">
                @if(count($products) === 0)
                    No products exist in the catalog for this firm. Please add products in the inventory section first to view stock movement reports.
                @elseif(request('product_id'))
                    The requested product was not found or does not belong to the selected firm.
                @else
                    No product is selected. Please select a product from the dropdown above to view its stock movement report.
                @endif
            </p>
        </div>
    </div>
@else
    <!-- Product Header Card -->
    <div class="card card-custom border-0 p-4 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle dark-symbol-avatar dark-symbol-product" style="width: 54px; height: 54px;">
                    @if(!empty($selectedProduct->image) && file_exists(public_path('storage/' . $selectedProduct->image)))
                        <img src="{{ asset('storage/' . $selectedProduct->image) }}" alt="{{ $selectedProduct->name }}" class="rounded-circle w-100 h-100 object-fit-cover">
                    @else
                        <i class="bi bi-box-seam fs-3"></i>
                    @endif
                </div>
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-1">{{ $selectedProduct->name }}</h4>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($selectedProduct->product_identifier)
                            <span class="badge text-dark fw-bold border border-warning font-monospace px-2.5 py-1" style="background-color: #fef08a; color: #713f12 !important;">
                                <i class="bi bi-qr-code me-1"></i>ID: {{ $selectedProduct->product_identifier }}
                            </span>
                        @endif
                        <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-tag-fill me-1"></i>{{ $selectedProduct->category->name ?? 'N/A' }}</span>
                        @if($selectedProduct->brand)
                            <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #6f42c1;"><i class="bi bi-award-fill me-1"></i>{{ $selectedProduct->brand->name }}</span>
                        @endif
                        <span class="badge bg-info text-white rounded-pill px-2.5 py-1 fw-semibold"><i class="bi bi-rulers me-1"></i>Unit: {{ $selectedProduct->unit }}</span>
                        @if($selectedProduct->hsn_code)
                            <span class="badge bg-dark text-white rounded-pill px-2.5 py-1 font-monospace fw-semibold"><i class="bi bi-hash me-1 text-warning"></i>HSN: {{ $selectedProduct->hsn_code }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="{{ route('products.show', $selectedProduct) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 no-print">
                    <i class="bi bi-eye me-1"></i> View Item Details
                </a>
            </div>
        </div>
    </div>

    @if($movementHistory->isEmpty())
        <!-- Empty Movement Alert Message Display -->
        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center gap-3 p-3 mb-4 rounded-3" role="alert">
            <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
            <div>
                <h6 class="fw-bold mb-1 text-dark">No Stock Movement Records Found</h6>
                <p class="mb-0 text-muted small">
                    No purchases, sales, returns, or adjustments were recorded for <strong>{{ $selectedProduct->name }}</strong>
                    @if(request('start_date') || request('end_date'))
                        between <strong>{{ request('start_date') ?: 'Beginning' }}</strong> and <strong>{{ request('end_date') ?: 'Today' }}</strong>.
                    @else
                        in the system yet.
                    @endif
                </p>
            </div>
        </div>
    @endif

    <!-- Movement History Ledger Table -->
    <div class="card card-custom border-0 overflow-hidden">
        <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold font-outfit text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Stock Movement Ledger</h5>
                <span class="small text-muted">Complete breakdown of all purchases, sales, returns, and inventory adjustments</span>
            </div>
            <span class="badge bg-light text-dark border font-monospace px-3 py-1.5 fw-bold">
                Total Transactions: {{ number_format(count($movementHistory)) }}
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    @php
                        $sort = request('sort_by', 'raw_date');
                        $order = request('sort_order', 'desc');
                    @endphp
                    <tr>
                        <th class="ps-4">
                            @php $nextOrder = ($sort === 'raw_date' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'raw_date', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Date
                                @if($sort === 'raw_date')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php $nextOrder = ($sort === 'type' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'type', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Transaction Type
                                @if($sort === 'type')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php $nextOrder = ($sort === 'ref_no' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'ref_no', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Invoice / Ref No.
                                @if($sort === 'ref_no')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>
                            @php $nextOrder = ($sort === 'party' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'party', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Party / Supplier / Customer
                                @if($sort === 'party')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th>Movement</th>
                        <th class="text-center">
                            @php $nextOrder = ($sort === 'quantity' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'quantity', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Qty
                                @if($sort === 'quantity')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-end">
                            @php $nextOrder = ($sort === 'rate' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'rate', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center justify-content-end">
                                Unit Rate (₹)
                                @if($sort === 'rate')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                        <th class="text-end pe-4">
                            @php $nextOrder = ($sort === 'amount' && $order === 'asc') ? 'desc' : 'asc'; @endphp
                            <a href="{{ route('reports.product-stock', array_merge(request()->query(), ['sort_by' => 'amount', 'sort_order' => $nextOrder])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center justify-content-end">
                                Total Amount (₹)
                                @if($sort === 'amount')
                                    <i class="bi bi-arrow-{{ $order === 'asc' ? 'up' : 'down' }} ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted ms-1 small opacity-50"></i>
                                @endif
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movementHistory as $row)
                        <tr>
                            <td class="ps-4 small font-monospace fw-medium text-dark">
                                {{ \Carbon\Carbon::parse($row['raw_date'])->format('d-m-Y') }}
                            </td>
                            <td>
                                <span class="badge {{ $row['badge_class'] }} rounded-pill px-3 py-1 fw-semibold">
                                    {{ $row['type'] }}
                                </span>
                            </td>
                            <td class="font-monospace">
                                @if($row['ref_route'])
                                    <a href="{{ $row['ref_route'] }}" class="fw-bold text-decoration-none hover-primary" target="_blank">
                                        {{ $row['ref_no'] }} <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                    </a>
                                @else
                                    <span class="fw-bold text-dark">{{ $row['ref_no'] }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark mb-1">
                                    @if($row['party_type'] === 'Supplier')
                                        <i class="bi bi-building me-1 text-primary"></i>
                                    @elseif($row['party_type'] === 'Customer')
                                        <i class="bi bi-person-circle me-1 text-success"></i>
                                    @else
                                        <i class="bi bi-person-gear me-1 text-warning"></i>
                                    @endif
                                    {{ $row['party'] }}
                                </div>
                                @if($row['party_type'] === 'Supplier')
                                    <span class="badge rounded-pill px-2 py-0.5 small fw-semibold" style="background-color: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe;">
                                        <i class="bi bi-truck me-1"></i>Supplier
                                    </span>
                                @elseif($row['party_type'] === 'Customer')
                                    <span class="badge rounded-pill px-2 py-0.5 small fw-semibold" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                        <i class="bi bi-person-check me-1"></i>Customer
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2 py-0.5 small fw-semibold" style="background-color: #fef3c7; color: #b45309; border: 1px solid #fde68a;">
                                        <i class="bi bi-person-gear me-1"></i>{{ $row['party_type'] }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($row['direction'] === 'IN')
                                    <span class="badge bg-success-subtle text-success border border-success border-opacity-20 font-monospace fw-bold px-2.5 py-1">
                                        <i class="bi bi-arrow-down-left-circle-fill me-1"></i>STOCK IN (+{{ $row['quantity'] }})
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger border-opacity-20 font-monospace fw-bold px-2.5 py-1">
                                        <i class="bi bi-arrow-up-right-circle-fill me-1"></i>STOCK OUT (-{{ $row['quantity'] }})
                                    </span>
                                @endif
                            </td>
                            <td class="text-center font-monospace">
                                @if($row['direction'] === 'IN')
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold fs-7" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0;">
                                        +{{ number_format($row['quantity']) }} {{ $selectedProduct->unit ?? 'Units' }}
                                    </span>
                                @else
                                    <span class="badge rounded-pill px-2.5 py-1 fw-bold fs-7" style="background-color: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                                        -{{ number_format($row['quantity']) }} {{ $selectedProduct->unit ?? 'Units' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-end small font-monospace text-dark">
                                ₹{{ number_format($row['rate'], 2) }}
                            </td>
                            <td class="text-end pe-4 fw-bold font-monospace text-dark">
                                ₹{{ number_format($row['amount'], 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="text-danger mb-2">
                                    <i class="bi bi-exclamation-circle fs-1"></i>
                                </div>
                                <h6 class="fw-bold text-danger mb-1">No Stock Movement Records Found</h6>
                                <p class="text-muted small mb-0">No purchases, sales, returns, or adjustments recorded for <strong>{{ $selectedProduct->name }}</strong> matching the selected criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const productSelect = document.getElementById('product_id_select');
    if (productSelect) {
        new TomSelect('#product_id_select', {
            create: false,
            placeholder: "Search or Select Product...",
            plugins: ['dropdown_input'],
            maxOptions: null,
            onChange: function(val) {
                if (val) {
                    document.getElementById('stockReportForm').submit();
                }
            }
        });
    }
});
</script>
@endpush
