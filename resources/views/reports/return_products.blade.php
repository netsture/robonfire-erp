@extends('layouts.app')

@section('title', 'Return Product Report')
@section('page_title', 'Return Product & Material Report')
@section('page_subtitle', 'Comprehensive history and valuation of returnable materials and customer product returns')

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
    <h5 class="text-muted font-outfit mb-0">Return Product & Material Entry Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Return Entries</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">{{ number_format($totalReturnCount) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Units Returned</span>
            <h3 class="fw-bold font-outfit text-success mt-2 mb-0">+{{ number_format($totalUnitsReturned) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Subtotal Value</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">₹{{ number_format($totalSubtotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 bg-primary text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Grand Total Returned Value</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($totalGrandTotal, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.return-products') }}" class="row g-2 align-items-end">
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
            <label class="form-label small fw-semibold text-muted mb-1">Search Return # / Customer / Project</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small fw-semibold text-muted mb-1">Customer</label>
            <select name="customer_id" class="form-select form-select-sm">
                <option value="">All Customers</option>
                @foreach($customers as $cust)
                    <option value="{{ $cust->id }}" {{ request('customer_id') == $cust->id ? 'selected' : '' }}>{{ $cust->company_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-dark btn-sm px-4 rounded-3"><i class="bi bi-filter me-1"></i> Apply Filters</button>
            <a href="{{ route('reports.return-products') }}" class="btn btn-light border btn-sm px-3 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Return Entries Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Return No #</th>
                    <th>Customer Name</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Return Date</th>
                    <th>Returned Items</th>
                    <th>Subtotal (₹)</th>
                    <th>Tax (₹)</th>
                    <th>Grand Total (₹)</th>
                    <th class="text-end pe-4 no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold font-monospace text-dark">#{{ $ret->return_number }}</div>
                            @if($ret->project_name)
                                <div class="small text-muted"><i class="bi bi-folder2-open me-1"></i>{{ $ret->project_name }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $ret->customer->company_name ?? 'N/A' }}</div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $ret->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-muted">{{ date('d M Y', strtotime($ret->return_date)) }}</td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                {{ $ret->items->sum('quantity') }} units ({{ $ret->items->count() }} items)
                            </span>
                        </td>
                        <td class="small">₹{{ number_format($ret->subtotal, 2) }}</td>
                        <td class="small text-muted">₹{{ number_format($ret->tax_amount, 2) }}</td>
                        <td class="fw-bold text-primary">₹{{ number_format($ret->grand_total, 2) }}</td>
                        <td class="text-end pe-4 no-print">
                            <a href="{{ route('returnable.show', $ret) }}" class="btn btn-sm btn-light border" title="View Details">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('returnable.print', $ret) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Receipt">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-box-arrow-in-left fs-1 d-block mb-2 text-secondary"></i>
                            No return material entries matching the filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($returns->hasPages())
        <div class="p-3 border-top no-print">
            {{ $returns->links() }}
        </div>
    @endif
</div>

@endsection
