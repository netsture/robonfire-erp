@extends('layouts.app')

@section('title', 'Purchases Report')
@section('page_title', 'Procurement Expenses Report')
@section('page_subtitle', 'Filter supplier purchase orders and procurement costs')

@section('header_actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('reports.purchases') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-3">
            <label class="form-label small mb-1 fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small mb-1 fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-12 col-md-3">
            <label class="form-label small mb-1 fw-semibold">Supplier</label>
            <select name="supplier_id" class="form-select form-select-sm">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex align-items-end gap-2 pt-4">
            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">Apply Filter</button>
            <a href="{{ route('reports.purchases') }}" class="btn btn-light btn-sm border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Summary Metrics -->
<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card card-custom border-0 p-3 bg-info text-white">
            <span class="text-white-50 small fw-semibold">TOTAL PROCUREMENT EXPENSE</span>
            <h3 class="fw-bold font-outfit mt-1 mb-0">₹{{ number_format($totalCost, 2) }}</h3>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-custom border-0 p-3 bg-dark text-white">
            <span class="text-white-50 small fw-semibold">TOTAL PAID TO VENDORS</span>
            <h3 class="fw-bold font-outfit mt-1 mb-0 text-success">₹{{ number_format($totalPaid, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Project Name</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td class="ps-4 fw-bold">{{ $purchase->project_name }}</td>
                        <td class="fw-semibold text-dark">{{ $purchase->supplier->name ?? 'N/A' }}</td>
                        <td class="small text-muted">{{ $purchase->purchase_date }}</td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3">{{ strtoupper($purchase->payment_status) }}</span>
                        </td>
                        <td class="text-end pe-4 fw-bold text-dark">₹{{ number_format($purchase->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No purchase records found for selected period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
        <div class="p-3 border-top">
            {{ $purchases->links() }}
        </div>
    @endif
</div>

@endsection
