@extends('layouts.app')

@section('title', 'Sales Orders')
@section('page_title', 'Sales & POS Invoicing')
@section('page_subtitle', 'Customer sales transactions, POS billing, and invoice generation')

@section('header_actions')
    <a href="{{ route('sales.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-bag-plus-fill me-1"></i> New Sales Order / POS
    </a>
@endsection

@section('content')

<!-- Search Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search invoice # or customer name..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Search</button>
            <a href="{{ route('sales.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Sales Orders Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Invoice #</th>
                    <th>Customer Name</th>
                    <th>Sale Date</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Payment Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td class="ps-4 fw-bold font-monospace text-dark">#{{ $sale->invoice_number }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $sale->customer->name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $sale->customer->phone ?? '' }}</div>
                        </td>
                        <td class="small text-muted">{{ $sale->sale_date }}</td>
                        <td class="fw-bold text-dark">${{ number_format($sale->grand_total, 2) }}</td>
                        <td class="text-muted small">${{ number_format($sale->paid_amount, 2) }}</td>
                        <td>
                            @if($sale->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">PAID</span>
                            @elseif($sale->payment_status === 'partial')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">PARTIAL</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">DUE</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-light border" title="View Order">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel sales order and restore stock?')">
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
                            <i class="bi bi-bag fs-1 d-block mb-2 text-secondary"></i>
                            No sales orders recorded. Click "New Sales Order / POS" to issue an invoice.
                        </td>
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
