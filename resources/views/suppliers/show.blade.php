@extends('layouts.app')

@section('title', 'Supplier Profile')
@section('page_title', 'Supplier Profile: ' . $supplier->name)
@section('page_subtitle', 'Vendor details, procurement history, and balance due')

@section('header_actions')
    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-pencil me-1"></i> Edit Supplier
    </a>
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <!-- Supplier Info Card -->
    <div class="col-12 col-md-5">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-truck fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">{{ $supplier->name }}</h4>
                    <span class="text-muted small">{{ $supplier->company_name ?? 'Vendor Account' }}</span>
                </div>
            </div>

            <hr class="my-2 text-muted">

            <div class="d-flex flex-column gap-2 my-2">
                <div class="small"><i class="bi bi-telephone text-muted me-2"></i>{{ $supplier->phone }}</div>
                <div class="small"><i class="bi bi-envelope text-muted me-2"></i>{{ $supplier->email ?? 'No email on file' }}</div>
                <div class="small"><i class="bi bi-geo-alt text-muted me-2"></i>{{ $supplier->address ?? 'N/A' }} {{ $supplier->city ? '('.$supplier->city.')' : '' }}</div>
                <div class="small"><i class="bi bi-receipt text-muted me-2"></i>Tax ID: {{ $supplier->tax_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Balance Metric -->
    <div class="col-12 col-md-7">
        <div class="card card-custom border-0 p-4 bg-dark text-white h-100 justify-content-center">
            <span class="text-white-50 small fw-semibold uppercase tracking-wider">TOTAL OUTSTANDING PAYABLE</span>
            <h1 class="fw-bold font-outfit mt-2 mb-1 text-warning">${{ number_format($supplier->current_balance, 2) }}</h1>
            <span class="small text-white-50">Current balance owed to vendor for purchase orders</span>
        </div>
    </div>
</div>

<!-- Purchase History -->
<div class="card card-custom border-0 p-4">
    <h5 class="fw-bold font-outfit mb-3 text-dark">Procurement History</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Reference #</th>
                    <th>Date</th>
                    <th>Payment Status</th>
                    <th>Total Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplier->purchases as $purchase)
                    <tr>
                        <td class="fw-bold text-dark">#{{ $purchase->reference_no }}</td>
                        <td class="small text-muted">{{ $purchase->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3">{{ strtoupper($purchase->payment_status) }}</span>
                        </td>
                        <td class="fw-bold text-dark">${{ number_format($purchase->grand_total, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-sm btn-light border" target="_blank">
                                <i class="bi bi-printer"></i> Receipt
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No past purchase orders recorded for this supplier.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
