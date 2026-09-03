@extends('layouts.app')

@section('title', 'Sales Orders')
@section('page_title', 'Sales Invoicing')
@section('page_subtitle', 'Customer sales transactions, POS billing, and invoice generation')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('sales.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-bag-plus-fill me-1"></i> New Sales Order
        </a>
    @endif
@endsection

@section('content')

<!-- Search Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('sales.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-6' : 'col-md-9' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search invoice #, project, vehicle #, or customer name..." value="{{ request('search') }}">
            </div>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-12 col-md-3">
            <select name="firm_id" class="form-select bg-light">
                <option value="">All Firms</option>
                @foreach($firms as $firm)
                    <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
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
                    <th class="ps-4">Invoice No #</th>
                    <th>Customer Name</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
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
                        <td class="ps-4">
                            <div class="fw-bold font-monospace text-dark">#{{ $sale->invoice_number }}</div>
                            @if($sale->project_name)
                                <div class="small text-muted"><i class="bi bi-folder2-open me-1"></i>{{ $sale->project_name }}</div>
                            @endif
                            @if($sale->vehicle_number)
                                <div class="small text-muted"><i class="bi bi-truck me-1"></i>{{ $sale->vehicle_number }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $sale->customer->company_name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $sale->customer->phone ?? '' }}</div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $sale->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-muted">{{ $sale->sale_date }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($sale->grand_total, 2) }}</td>
                        <td class="text-muted small">₹{{ number_format($sale->paid_amount, 2) }}</td>
                        <td>
                            @if($sale->payment_status === 'paid')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">PAID</span>
                            @elseif($sale->payment_status === 'unpaid')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">UNPAID</span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1">PENDING</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                @if(!auth()->user()->isSuperAdmin())
                                    <button type="button" class="btn btn-sm btn-light border text-success" title="Set Payment Status" data-bs-toggle="modal" data-bs-target="#updatePaymentModal_{{ $sale->id }}">
                                        <i class="bi bi-wallet2"></i>
                                    </button>
                                @endif
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-light border" target="_blank" title="View Order">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('sales.challan', $sale) }}" class="btn btn-sm btn-light border text-info" target="_blank" title="Delivery Challan View">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <!--<a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Invoice">
                                    <i class="bi bi-printer"></i>
                                </a>-->
                                @if(!auth()->user()->isSuperAdmin())
                                    <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline" onsubmit="return confirm('Cancel sales order and restore stock?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>

                            @if(!auth()->user()->isSuperAdmin())
                            <!-- Update Payment Modal -->
                            <div class="modal fade" id="updatePaymentModal_{{ $sale->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4 border-0 text-start">
                                        <form action="{{ route('sales.update-payment-status', $sale) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold font-outfit">Set Payment Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 p-3 bg-light rounded-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">Invoice #:</span>
                                                        <span class="fw-bold font-monospace">{{ $sale->invoice_number }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">Customer:</span>
                                                        <span class="fw-semibold">{{ $sale->customer->company_name ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted small">Grand Total:</span>
                                                        <span class="fw-bold text-primary">₹{{ number_format($sale->grand_total, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                                    <select name="payment_status" class="form-select" onchange="toggleModalPaidInput(this, {{ $sale->grand_total }}, 'modal_paid_{{ $sale->id }}')">
                                                        <option value="pending" {{ in_array($sale->payment_status, ['pending', 'due']) ? 'selected' : '' }}>Pending</option>
                                                        <option value="paid" {{ $sale->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                        <option value="unpaid" {{ $sale->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Amount Received (₹)</label>
                                                    <input type="number" step="0.01" name="paid_amount" id="modal_paid_{{ $sale->id }}" class="form-control" value="{{ number_format($sale->paid_amount, 2, '.', '') }}" readonly>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif
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

@push('scripts')
<script>
    function toggleModalPaidInput(selectEl, grandTotal, targetInputId) {
        const inputEl = document.getElementById(targetInputId);
        if (!inputEl) return;
        if (selectEl.value === 'paid') {
            inputEl.value = parseFloat(grandTotal).toFixed(2);
        } else {
            inputEl.value = '0.00';
        }
    }
</script>
@endpush

@endsection
