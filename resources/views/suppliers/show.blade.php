@extends('layouts.app')

@section('title', 'Supplier Profile')
@section('page_title', 'Supplier Profile: ' . $supplier->company_name)
@section('page_subtitle', 'Vendor details and procurement history')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-pencil me-1"></i> Edit Supplier
        </a>
    @endif
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <!-- Supplier Info Card -->
    <div class="col-12">
        <div class="card card-custom border-0 p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-truck fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">{{ $supplier->company_name }}</h4>
                    <span class="text-muted small">Vendor Account</span>
                </div>
            </div>

            <hr class="my-2 text-muted">

            <div class="row g-2 my-2">
                <div class="col-12 col-md-3 small"><i class="bi bi-telephone text-muted me-2"></i><strong>Phone:</strong> {{ $supplier->phone ?? 'N/A' }}</div>
                <div class="col-12 col-md-3 small"><i class="bi bi-envelope text-muted me-2"></i><strong>Email:</strong> {{ $supplier->email ?? 'N/A' }}</div>
                <div class="col-12 col-md-3 small"><i class="bi bi-receipt text-muted me-2"></i><strong>GST Number:</strong> {{ $supplier->gst_number ?? 'N/A' }}</div>
                <div class="col-12 col-md-3 small"><i class="bi bi-geo-alt text-muted me-2"></i><strong>Address:</strong> {{ $supplier->address ?? 'N/A' }}</div>
            </div>
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
                    <th>Project Name</th>
                    <th>Date</th>
                    <th>Payment Status</th>
                    <th>Total Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($supplier->purchases as $purchase)
                    <tr>
                        <td class="fw-bold text-dark">{{ $purchase->project_name }}</td>
                        <td class="small text-muted">{{ $purchase->created_at->format('M d, Y') }}</td>
                        <td>
                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3">{{ strtoupper($purchase->payment_status) }}</span>
                        </td>
                        <td class="fw-bold text-dark">₹{{ number_format($purchase->grand_total, 2) }}</td>
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
