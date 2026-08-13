@extends('layouts.app')

@section('title', 'Customer Ledger')
@section('page_title', 'Customer Ledger: ' . $customer->name)
@section('page_subtitle', 'Customer summary, credit limits, and sales order history')

@section('header_actions')
    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-pencil me-1"></i> Edit Profile
    </a>
    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <!-- Info Card -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-building fs-3"></i>
                </div>
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">{{ $customer->name }}</h4>
                    <span class="text-muted small">{{ $customer->company_name ?? 'Individual Customer' }}</span>
                </div>
            </div>

            <hr class="my-2 text-muted">

            <div class="d-flex flex-column gap-2 my-2">
                <div class="small"><i class="bi bi-telephone text-muted me-2"></i>{{ $customer->phone }}</div>
                <div class="small"><i class="bi bi-envelope text-muted me-2"></i>{{ $customer->email ?? 'No email on file' }}</div>
                <div class="small"><i class="bi bi-geo-alt text-muted me-2"></i>{{ $customer->address ?? 'N/A' }} {{ $customer->city ? '('.$customer->city.')' : '' }}</div>
                <div class="small"><i class="bi bi-receipt text-muted me-2"></i>Tax ID: {{ $customer->tax_number ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Balance Metrics -->
    <div class="col-12 col-md-8">
        <div class="row g-3 h-100">
            <div class="col-6">
                <div class="card card-custom border-0 p-4 bg-primary text-white h-100">
                    <span class="text-white-50 small fw-semibold">CREDIT LIMIT</span>
                    <h2 class="fw-bold font-outfit mt-2 mb-0">${{ number_format($customer->credit_limit, 2) }}</h2>
                    <span class="small mt-2 text-white-50">Allowed credit ceiling</span>
                </div>
            </div>
            <div class="col-6">
                <div class="card card-custom border-0 p-4 bg-dark text-white h-100">
                    <span class="text-white-50 small fw-semibold">CURRENT BALANCE DUE</span>
                    <h2 class="fw-bold font-outfit mt-2 mb-0 text-warning">${{ number_format($customer->current_balance, 2) }}</h2>
                    <span class="small mt-2 text-white-50">Outstanding total</span>
                </div>
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
                    <th>Invoice #</th>
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
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">{{ strtoupper($sale->payment_status) }}</span>
                        </td>
                        <td class="fw-bold text-dark">${{ number_format($sale->grand_total, 2) }}</td>
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
