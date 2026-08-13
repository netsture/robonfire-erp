@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page_title', 'Procurement & Purchases')
@section('page_subtitle', 'Record inventory restocks from suppliers and manage procurement costs')

@section('header_actions')
    <a href="{{ route('purchases.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-cart-plus-fill me-1"></i> New Purchase Order
    </a>
@endsection

@section('content')

<!-- Search Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('purchases.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search reference # or supplier name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Search</button>
            <a href="{{ route('purchases.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Purchases Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Reference #</th>
                    <th>Supplier</th>
                    <th>Date</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Payment Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td class="ps-4 fw-bold font-monospace text-dark">#{{ $purchase->reference_no }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $purchase->supplier->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $purchase->supplier->company_name ?? '' }}</div>
                        </td>
                        <td class="small text-muted">{{ $purchase->purchase_date }}</td>
                        <td class="fw-bold text-dark">${{ number_format($purchase->grand_total, 2) }}</td>
                        <td class="text-muted small">${{ number_format($purchase->paid_amount, 2) }}</td>
                        <td>
                            @if($purchase->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">PAID</span>
                            @elseif($purchase->payment_status === 'partial')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">PARTIAL</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">DUE</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-light border" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete purchase record and restore stock?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-cart fs-1 d-block mb-2 text-secondary"></i>
                            No purchase orders recorded yet. Click "New Purchase Order" to restock inventory.
                        </td>
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
