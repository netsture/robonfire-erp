@extends('layouts.app')

@section('title', 'Detailed Purchases Report')
@section('page_title', 'Detailed Procurement & Expenses Report')
@section('page_subtitle', 'Comprehensive analysis of purchase expenses, vendor payments, payables, and procurement metrics')

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
    <h5 class="text-muted font-outfit mb-0">Detailed Procurement Expenses & Vendor Payables Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Orders</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">{{ number_format($totalOrdersCount) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Gross Cost (Subtotal)</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">₹{{ number_format($totalSubtotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Tax Paid</span>
            <h3 class="fw-bold font-outfit text-info mt-2 mb-0">+₹{{ number_format($totalTaxAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-dark text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Grand Total Expense</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($totalGrandTotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-success text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Paid to Vendors</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($totalPaidAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-danger text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Vendor Payables</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($totalPendingAmount, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.purchases') }}" class="row g-2 align-items-end">
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

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Search Invoice / Supplier</label>
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

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Supplier</label>
            <select name="supplier_id" class="form-select form-select-sm">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->company_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-1">
            <label class="form-label small fw-semibold text-muted mb-1">Status</label>
            <select name="payment_status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
            </select>
        </div>

        <div class="col-12 col-md-1 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i> Filter</button>
            <a href="{{ route('reports.purchases') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Detailed Purchases Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th class="ps-4">Invoice #</th>
                    <th>Date</th>
                    <th>Supplier / Vendor</th>
                    <th>Project Name</th>
                    <th>Status</th>
                    <th class="text-end">Subtotal (₹)</th>
                    <th class="text-end">Tax (₹)</th>
                    <th class="text-end">Discount (₹)</th>
                    <th class="text-end">Grand Total (₹)</th>
                    <th class="text-end">Amount Paid (₹)</th>
                    <th class="text-end">Balance Payable (₹)</th>
                    <th class="text-center pe-4 no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    @php
                        $balanceDue = $purchase->grand_total - $purchase->paid_amount;
                    @endphp
                    <tr>
                        @if(auth()->user()->isSuperAdmin())
                            <td><span class="badge bg-dark-subtle text-dark border">{{ $purchase->firm->name ?? 'N/A' }}</span></td>
                        @endif
                        <td class="ps-4 fw-bold font-monospace text-primary">#{{ $purchase->invoice_number }}</td>
                        <td class="small text-muted">{{ $purchase->purchase_date }}</td>
                        <td class="fw-semibold text-dark">{{ $purchase->supplier->company_name ?? 'N/A' }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $purchase->project_name ?? 'N/A' }}</span></td>
                        <td>
                            @if($purchase->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">PAID</span>
                            @elseif($purchase->payment_status === 'unpaid')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">UNPAID</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">PENDING</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace">₹{{ number_format($purchase->subtotal, 2) }}</td>
                        <td class="text-end font-monospace text-muted">+₹{{ number_format($purchase->tax_amount, 2) }}</td>
                        <td class="text-end font-monospace text-muted">-₹{{ number_format($purchase->discount_amount, 2) }}</td>
                        <td class="text-end font-monospace fw-bold text-dark">₹{{ number_format($purchase->grand_total, 2) }}</td>
                        <td class="text-end font-monospace fw-semibold text-success">₹{{ number_format($purchase->paid_amount, 2) }}</td>
                        <td class="text-end font-monospace fw-semibold {{ $balanceDue > 0 ? 'text-danger' : 'text-muted' }}">
                            ₹{{ number_format($balanceDue, 2) }}
                        </td>
                        <td class="text-center pe-4 no-print">
                            <div class="btn-group">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-light border" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 13 : 12 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-cart-x fs-1 d-block mb-2 text-secondary"></i>
                            No purchase records match the selected filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($purchases) > 0)
                <tfoot class="table-light border-top">
                    <tr class="fw-bold">
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 6 : 5 }}" class="ps-4 text-end">TOTALS:</td>
                        <td class="text-end font-monospace">₹{{ number_format($totalSubtotal, 2) }}</td>
                        <td class="text-end font-monospace text-muted">+₹{{ number_format($totalTaxAmount, 2) }}</td>
                        <td class="text-end font-monospace text-muted">-₹{{ number_format($totalDiscountAmount, 2) }}</td>
                        <td class="text-end font-monospace text-dark fs-6">₹{{ number_format($totalGrandTotal, 2) }}</td>
                        <td class="text-end font-monospace text-success fs-6">₹{{ number_format($totalPaidAmount, 2) }}</td>
                        <td class="text-end font-monospace text-danger fs-6">₹{{ number_format($totalPendingAmount, 2) }}</td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @if($purchases->hasPages())
        <div class="p-3 border-top no-print">
            {{ $purchases->links() }}
        </div>
    @endif
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
