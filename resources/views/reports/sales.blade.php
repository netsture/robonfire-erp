@extends('layouts.app')

@section('title', 'Sales Report')
@section('page_title', 'Sales Performance Report')
@section('page_subtitle', 'Filter revenue by custom date range, customer, and payment status')

@section('header_actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('reports.sales') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
            <label class="form-label small mb-1 fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small mb-1 fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small mb-1 fw-semibold">Customer</label>
            <select name="customer_id" class="form-select form-select-sm">
                <option value="">All Customers</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end gap-2 pt-4">
            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">Apply Filter</button>
            <a href="{{ route('reports.sales') }}" class="btn btn-light btn-sm border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card card-custom border-0 p-3 bg-primary text-white">
            <span class="text-white-50 small fw-semibold">TOTAL SALES REVENUE</span>
            <h3 class="fw-bold font-outfit mt-1 mb-0">${{ number_format($totalRevenue, 2) }}</h3>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-custom border-0 p-3 bg-success text-white">
            <span class="text-white-50 small fw-semibold">TOTAL AMOUNT RECEIVED</span>
            <h3 class="fw-bold font-outfit mt-1 mb-0">${{ number_format($totalPaid, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Invoice #</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td class="ps-4 fw-bold font-monospace">#{{ $sale->invoice_number }}</td>
                        <td class="fw-semibold text-dark">{{ $sale->customer->name ?? 'N/A' }}</td>
                        <td class="small text-muted">{{ $sale->sale_date }}</td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">{{ strtoupper($sale->payment_status) }}</span>
                        </td>
                        <td class="text-end pe-4 fw-bold text-dark">${{ number_format($sale->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No sales records found for selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
        <div class="p-3 border-top">
            {{ $sales->links() }}
        </div>
    @endif
</div>

@endsection
