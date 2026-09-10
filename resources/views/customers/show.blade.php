@extends('layouts.app')

@section('title', 'Customer Ledger')
@section('page_title', 'Customer Ledger: ' . $customer->company_name)
@section('page_subtitle', 'Customer summary and sales order history')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-pencil me-1"></i> Edit Profile
        </a>
    @endif
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <!-- Info Card -->
    <div class="col-12">
        <div class="card card-custom border-0 p-4">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle dark-symbol-avatar dark-symbol-customer" style="width: 54px; height: 54px;">
                    <i class="bi bi-person-circle fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">{{ $customer->company_name }}</h4>
                    <span class="text-muted small">Customer Account</span>
                </div>
            </div>

            <hr class="my-2 text-muted">

            <div class="row g-2 my-2">
                <div class="col-12 col-md-3 small"><i class="bi bi-telephone text-muted me-2"></i><strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}</div>
                <div class="col-12 col-md-3 small"><i class="bi bi-envelope text-muted me-2"></i><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</div>
                <div class="col-12 col-md-3 small"><i class="bi bi-receipt text-muted me-2"></i><strong>GST Number:</strong> {{ $customer->gst_number ?? 'N/A' }}</div>
                <div class="col-12 col-md-3 small"><i class="bi bi-geo-alt text-muted me-2"></i><strong>Address:</strong> {{ $customer->address ?? 'N/A' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Orders History -->
<div class="card card-custom border-0 p-4">
    <h5 class="fw-bold font-outfit mb-3 text-dark">Order History & Invoices</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Invoice No</th>
                    <th>Date</th>
                    <th>Payment Status</th>
                    <th>Total Amount</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->sales as $sale)
                    <tr>
                        <td class="fw-bold text-dark">#{{ $sale->invoice_number }}</td>
                        <td class="small text-muted">{{ $sale->created_at->format('M d, Y') }}</td>
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
                        <td class="fw-bold text-dark">₹{{ number_format($sale->grand_total, 2) }}</td>
                        <td class="text-end">
                            <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-light border" target="_blank">
                                <i class="bi bi-printer"></i> Invoice
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No past sales records for this customer.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
