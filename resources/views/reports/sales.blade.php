@extends('layouts.app')

@section('title', 'Detailed Sales Report')
@section('page_title', 'Detailed Sales & Revenue Report')
@section('page_subtitle', 'Comprehensive analysis of sales revenue, collections, outstanding balances, and order performance')

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
    <h5 class="text-muted font-outfit mb-0">Detailed Sales Performance & Revenue Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Sales Orders</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalOrdersCount) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 text-white" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Gross Sales (Subtotal)</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($totalSubtotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 text-white" style="background: linear-gradient(135deg, #0891b2 0%, #0e7490 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Tax Collected</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">+₹{{ number_format($totalTaxAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-primary text-white">
            <span class="text-white small fw-semibold text-uppercase" style="color: #ffffff !important;">Grand Total Revenue</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($totalGrandTotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-success text-white">
            <span class="text-white small fw-semibold text-uppercase" style="color: #ffffff !important;">Amount Received</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($totalPaidAmount, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-danger text-white">
            <span class="text-white small fw-semibold text-uppercase" style="color: #ffffff !important;">Balance Due</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0" style="color: #ffffff !important;">₹{{ number_format($totalPendingAmount, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-end">
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
            <label class="form-label small fw-semibold text-muted mb-1">Search Challan No/Project/Customer</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search challan, project, customer..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Start Date (dd-mm-yyyy)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                <input type="text" name="start_date" class="form-control form-control-sm datepicker-ddmmyyyy" placeholder="dd-mm-yyyy" value="{{ request('start_date') ? (\Carbon\Carbon::canBeCreatedFromFormat(request('start_date'), 'd-m-Y') ? request('start_date') : \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y')) : '' }}" autocomplete="off">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">End Date (dd-mm-yyyy)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                <input type="text" name="end_date" class="form-control form-control-sm datepicker-ddmmyyyy" placeholder="dd-mm-yyyy" value="{{ request('end_date') ? (\Carbon\Carbon::canBeCreatedFromFormat(request('end_date'), 'd-m-Y') ? request('end_date') : \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y')) : '' }}" autocomplete="off">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Search Customer Name</label>
            <select name="customer_id" class="form-select form-select-sm">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->company_name }}</option>
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
            <a href="{{ route('reports.sales') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Detailed Sales Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Challan No</th>
                    <th>Challan Date</th>
                    <th>Customer Name</th>
                    <th>Project Name</th>
                    <th>Status</th>
                    <th class="text-end">Subtotal (₹)</th>
                    <th class="text-end">Tax (₹)</th>
                    <th class="text-end">Discount (₹)</th>
                    <th class="text-end">Shipping Cost (₹)</th>
                    <th class="text-end">Grand Total (₹)</th>
                    <th class="text-end">Amount Received (₹)</th>
                    <th class="text-end">Balance Due (₹)</th>
                    <th class="text-center pe-4 no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $idx => $sale)
                    @php
                        $balanceDue = $sale->grand_total - $sale->paid_amount;
                    @endphp
                    <tr>
                        <td class="ps-4 text-muted small">{{ ($sales->currentPage() - 1) * $sales->perPage() + $idx + 1 }}</td>
                        @if(auth()->user()->isSuperAdmin())
                            <td><span class="badge bg-dark-subtle text-dark border">{{ $sale->firm->name ?? 'N/A' }}</span></td>
                        @endif
                        <td class="fw-bold font-monospace text-primary">#{{ $sale->invoice_number }}</td>
                        <td class="small text-muted font-monospace">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>
                        <td class="fw-semibold text-dark">{{ $sale->customer->company_name ?? 'N/A' }}</td>
                        <td>
                            @if($sale->project_name)
                                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #4f46e5;"><i class="bi bi-folder-check me-1"></i>{{ $sale->project_name }}</span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($sale->payment_status === 'paid')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">PAID</span>
                            @elseif($sale->payment_status === 'unpaid')
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1">UNPAID</span>
                            @elseif($sale->payment_status === 'partial')
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1">PARTIAL</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">PENDING</span>
                            @endif
                        </td>
                        <td class="text-end font-monospace">₹{{ number_format($sale->subtotal, 2) }}</td>
                        <td class="text-end font-monospace text-muted">+₹{{ number_format($sale->tax_amount, 2) }}</td>
                        <td class="text-end font-monospace text-muted">-₹{{ number_format($sale->discount_amount, 2) }}</td>
                        <td class="text-end font-monospace text-muted">+₹{{ number_format($sale->shipping_cost ?? 0, 2) }}</td>
                        <td class="text-end font-monospace fw-bold text-dark">₹{{ number_format($sale->grand_total, 2) }}</td>
                        <td class="text-end font-monospace fw-semibold text-success">₹{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="text-end font-monospace fw-semibold {{ $balanceDue > 0 ? 'text-danger' : 'text-muted' }}">
                            ₹{{ number_format($balanceDue, 2) }}
                        </td>
                        <td class="text-center pe-4 no-print">
                            <div class="btn-group">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-light border" target="_blank" title="View Order Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.challan', $sale) }}" class="btn btn-sm btn-light border text-info" target="_blank" title="Delivery Challan View">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 15 : 14 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                            No sales records match the selected filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($sales) > 0)
                <tfoot class="table-light border-top">
                    <tr class="fw-bold">
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 7 : 6 }}" class="ps-4 text-end">TOTALS:</td>
                        <td class="text-end font-monospace">₹{{ number_format($totalSubtotal, 2) }}</td>
                        <td class="text-end font-monospace text-muted">+₹{{ number_format($totalTaxAmount, 2) }}</td>
                        <td class="text-end font-monospace text-muted">-₹{{ number_format($totalDiscountAmount, 2) }}</td>
                        <td class="text-end font-monospace text-muted">+₹{{ number_format($totalShippingCost, 2) }}</td>
                        <td class="text-end font-monospace text-dark fs-6">₹{{ number_format($totalGrandTotal, 2) }}</td>
                        <td class="text-end font-monospace text-success fs-6">₹{{ number_format($totalPaidAmount, 2) }}</td>
                        <td class="text-end font-monospace text-danger fs-6">₹{{ number_format($totalPendingAmount, 2) }}</td>
                        <td class="no-print"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
    @if($sales->hasPages())
        <div class="p-3 border-top no-print">
            {{ $sales->links() }}
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
