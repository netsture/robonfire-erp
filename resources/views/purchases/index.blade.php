@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page_title', 'Procurement & Purchases')
@section('page_subtitle', 'Record inventory restocks from suppliers and manage procurement costs')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('purchases.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-cart-plus-fill me-1"></i> New Purchase Order
        </a>
    @endif
@endsection

@section('content')

<!-- Search Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('purchases.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-6' : 'col-md-9' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Invoice No, Project Name, Supplier Name, Phone Number, Purchase Date..." value="{{ request('search') }}">
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
                    <th class="ps-4">Invoice No / Project Name</th>
                    <th>Supplier Name / Number</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Purchase Date</th>
                    <th>Grand Total</th>
                    <th>Paid Amount</th>
                    <th>Payment Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-supplier" style="width: 40px; height: 40px;">
                                    <i class="bi bi-cart-plus-fill fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold font-monospace text-dark">#{{ $purchase->invoice_number ?? 'N/A' }}</div>
                                    @if($purchase->project_name)
                                        <div class="small text-muted"><i class="bi bi-folder2-open me-1"></i>{{ $purchase->project_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-supplier" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $purchase->supplier->company_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $purchase->supplier->phone ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $purchase->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-dark font-monospace">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($purchase->grand_total, 2) }}</td>
                        <td class="text-muted small">₹{{ number_format($purchase->paid_amount, 2) }}</td>
                        <td>
                            @if($purchase->payment_status === 'paid')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">PAID</span>
                            @elseif($purchase->payment_status === 'unpaid')
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1">UNPAID</span>
                            @elseif($purchase->payment_status === 'partial')
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1">PARTIAL</span>
                            @else
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">PENDING</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-light border" title="Edit Purchase">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light border text-success" title="Set Payment Status" data-bs-toggle="modal" data-bs-target="#updatePaymentModal_{{ $purchase->id }}">
                                        <i class="bi bi-wallet2"></i>
                                    </button>
                                @endif
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-light border" target="_blank" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <!--<a href="{{ route('purchases.print', $purchase) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Receipt">
                                    <i class="bi bi-printer"></i>
                                </a>-->
                                @if(!auth()->user()->isSuperAdmin())
                                    <form action="{{ route('purchases.destroy', $purchase) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete purchase record and restore stock?')">
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
                            <div class="modal fade" id="updatePaymentModal_{{ $purchase->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content rounded-4 border-0 text-start">
                                        <form action="{{ route('purchases.update-payment-status', $purchase) }}" method="POST">
                                            @csrf
                                            <div class="modal-header border-bottom">
                                                <h5 class="modal-title fw-bold font-outfit">Set Payment Status</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3 p-3 bg-light rounded-3">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">Invoice No:</span>
                                                        <span class="fw-bold font-monospace">{{ $purchase->invoice_number }}</span>
                                                    </div>
                                                    @if($purchase->project_name)
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">Project Name:</span>
                                                        <span class="fw-semibold text-dark">{{ $purchase->project_name }}</span>
                                                    </div>
                                                    @endif
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span class="text-muted small">Supplier:</span>
                                                        <span class="fw-semibold">{{ $purchase->supplier->company_name ?? 'N/A' }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span class="text-muted small">Grand Total:</span>
                                                        <span class="fw-bold text-primary">₹{{ number_format($purchase->grand_total, 2) }}</span>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Payment Status <span class="text-danger">*</span></label>
                                                    <select name="payment_status" class="form-select" onchange="toggleModalPaidInput(this, {{ $purchase->grand_total }}, 'modal_paid_{{ $purchase->id }}')">
                                                        <option value="pending" {{ in_array($purchase->payment_status, ['pending', 'due']) ? 'selected' : '' }}>Pending</option>
                                                        <option value="paid" {{ $purchase->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                        <option value="unpaid" {{ $purchase->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Paid Amount (₹)</label>
                                                    <input type="number" name="paid_amount" id="modal_paid_{{ $purchase->id }}" class="form-control" value="{{ (float)$purchase->paid_amount == 0 ? 0 : (float)$purchase->paid_amount }}" {{ $purchase->payment_status !== 'partial' ? 'readonly' : '' }}>
                                                </div>
                                            </div>
                                            <div class="modal-footer border-top">
                                                <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Payment Status</button>
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

<script>
    function toggleModalPaidInput(selectEl, grandTotal, inputId) {
        var val = selectEl.value;
        var input = document.getElementById(inputId);
        if (!input) return;
        if (val === 'paid') {
            input.value = grandTotal.toFixed(2);
            input.readOnly = true;
        } else if (val === 'unpaid') {
            input.value = '0';
            input.readOnly = true;
        } else {
            input.readOnly = false;
        }
    }
</script>

@endsection
