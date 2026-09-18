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
    <h5 class="text-muted font-outfit mb-0">Product Stock Movement & Ledger Report</h5>
    @if($selectedProduct)
        <h6 class="fw-bold text-dark mt-2 mb-0">{{ $selectedProduct->name }} (Code: {{ $selectedProduct->product_identifier ?? 'N/A' }})</h6>
    @endif
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
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
            <select name="product_id" id="product_id_select" class="form-select form-select-sm" required>
                <option value="">Search or Select Product...</option>
                @foreach($products as $prod)
                    <option value="{{ $prod->id }}" {{ ($selectedProduct && $selectedProduct->id == $prod->id) ? 'selected' : '' }}>
                        {{ $prod->name }} {{ $prod->product_identifier ? '['.$prod->product_identifier.']' : '' }}
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

@if($selectedProduct)
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

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Purchase Card -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Purchased</span>
                    <i class="bi bi-cart-plus-fill fs-4" style="color: #ffffff !important;"></i>
                </div>
                <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">+{{ number_format($summary['total_purchased_qty']) }} {{ $selectedProduct->unit }}</h3>
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
                <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">-{{ number_format($summary['total_sold_qty']) }} {{ $selectedProduct->unit }}</h3>
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
                <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">+{{ number_format($summary['total_returned_qty']) }} {{ $selectedProduct->unit }}</h3>
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
                <h3 class="fw-bold font-outfit mt-1 mb-0 {{ $selectedProduct->isLowStock() ? 'text-danger' : 'text-success' }}">
                    {{ number_format($summary['current_stock']) }} {{ $selectedProduct->unit }}
                </h3>
                <span class="small text-white-50 mt-1">Cost: ₹{{ number_format($summary['cost_price'], 2) }} | Selling: ₹{{ number_format($summary['selling_price'], 2) }}</span>
            </div>
        </div>
    </div>

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
                    <tr>
                        <th class="ps-4">Date</th>
                        <th>Transaction Type</th>
                        <th>Invoice / Ref No.</th>
                        <th>Party / Supplier / Customer</th>
                        <th>Movement</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Rate (₹)</th>
                        <th class="text-end pe-4">Total Amount (₹)</th>
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
                                <div class="fw-semibold text-dark">{{ $row['party'] }}</div>
                                <span class="badge bg-light text-muted border py-0.5 px-2 small">{{ $row['party_type'] }}</span>
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
                            <td class="text-center fw-bold font-monospace">
                                {{ $row['quantity'] }} {{ $selectedProduct->unit }}
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
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                                <h6>No stock movements recorded for this product yet.</h6>
                                <p class="small mb-0">When purchases, sales, returns, or adjustments occur, they will appear here in chronological order.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@else
    <div class="card card-custom border-0 p-5 text-center">
        <i class="bi bi-box-seam fs-1 text-muted mb-3"></i>
        <h5 class="fw-bold text-dark">No Products Found</h5>
        <p class="text-muted">Please add products to your catalog to view stock movement reports.</p>
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
