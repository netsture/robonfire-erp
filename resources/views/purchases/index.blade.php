@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('page_title', 'Purchases Invoicing')
@section('page_subtitle', 'Record inventory restocks from suppliers and manage procurement costs')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('purchases.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-cart-plus-fill me-1"></i> New Purchase Order
        </a>
    @endif
@endsection

@section('content')

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


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
                    <th class="ps-4">
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'invoice_number', 'sort_order' => request('sort_by') === 'invoice_number' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Invoice No / Project Name
                            @if(request('sort_by') === 'invoice_number')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'supplier_name', 'sort_order' => request('sort_by') === 'supplier_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Supplier Name / Number
                            @if(request('sort_by') === 'supplier_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>
                            <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'firm_name', 'sort_order' => request('sort_by') === 'firm_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Firm
                                @if(request('sort_by') === 'firm_name')
                                    <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                                @endif
                            </a>
                        </th>
                    @endif
                    <th>
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'purchase_date', 'sort_order' => request('sort_by') === 'purchase_date' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Purchase Date
                            @if(request('sort_by') === 'purchase_date')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'grand_total', 'sort_order' => request('sort_by') === 'grand_total' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Grand Total
                            @if(request('sort_by') === 'grand_total')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'paid_amount', 'sort_order' => request('sort_by') === 'paid_amount' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Paid Amount
                            @if(request('sort_by') === 'paid_amount')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'payment_status', 'sort_order' => request('sort_by') === 'payment_status' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Payment Status
                            @if(request('sort_by') === 'payment_status')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('purchases.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Created Date
                            @if(request('sort_by') === 'created_at')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
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
                        <td class="small font-monospace">
                            {{ $purchase->created_at ? $purchase->created_at->format('d-m-Y') : 'N/A' }}
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
                            @php
                                $gTotal = (float)$purchase->grand_total;
                                $pPaid = (float)$purchase->paid_amount;
                                $pDue = max(0, $gTotal - $pPaid);
                            @endphp
                            <!-- Update Payment Modal -->
                            <div class="modal fade" id="updatePaymentModal_{{ $purchase->id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content rounded-4 border-0 text-start">
                                        <div class="modal-header border-bottom">
                                            <div>
                                                <h5 class="modal-title fw-bold font-outfit">Set Payment Status & Record Payments</h5>
                                                <div class="small text-muted font-monospace">Invoice #{{ $purchase->invoice_number }} | {{ $purchase->supplier->company_name ?? 'N/A' }}</div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Summary Metrics Cards -->
                                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                                <div class="row text-center g-2">
                                                    <div class="col-4">
                                                        <span class="text-muted small d-block fw-semibold text-uppercase">Grand Total</span>
                                                        <span class="fw-bold fs-6 text-dark font-monospace">₹{{ number_format($gTotal, 2) }}</span>
                                                    </div>
                                                    <div class="col-4 border-start">
                                                        <span class="text-muted small d-block fw-semibold text-uppercase">Paid Amount</span>
                                                        <span class="fw-bold fs-6 text-success font-monospace">₹{{ number_format($pPaid, 2) }}</span>
                                                    </div>
                                                    <div class="col-4 border-start">
                                                        <span class="text-muted small d-block fw-semibold text-uppercase">Remaining Due</span>
                                                        <span class="fw-bold fs-6 text-danger font-monospace">₹{{ number_format($pDue, 2) }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Partial Payment History Logs -->
                                            @if($purchase->payments->count() > 0)
                                                <div class="mb-3">
                                                    <h6 class="fw-bold font-outfit text-dark small text-uppercase tracking-wider mb-2">
                                                        <i class="bi bi-clock-history me-1"></i> Partial Payment History Logs ({{ $purchase->payments->count() }})
                                                    </h6>
                                                    <div class="table-responsive border rounded-3">
                                                        <table class="table table-sm table-hover align-middle mb-0">
                                                            <thead class="table-light small">
                                                                <tr>
                                                                    <th class="ps-3">#</th>
                                                                    <th>Payment Date</th>
                                                                    <th>Amount Paid</th>
                                                                    <th>Notes</th>
                                                                    <th class="text-end pe-3">Action</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="small">
                                                                @foreach($purchase->payments as $index => $pmt)
                                                                    <tr>
                                                                        <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                                                        <td class="font-monospace text-dark">{{ \Carbon\Carbon::parse($pmt->payment_date)->format('d-m-Y') }}</td>
                                                                        <td class="fw-bold text-success font-monospace">₹{{ number_format($pmt->amount, 2) }}</td>
                                                                        <td class="text-muted">{{ $pmt->notes ?? '-' }}</td>
                                                                        <td class="text-end pe-3">
                                                                            <form action="{{ route('purchases.payments.destroy', [$purchase, $pmt]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this partial payment entry?')">
                                                                                @csrf
                                                                                @method('DELETE')
                                                                                <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Delete Payment">
                                                                                    <i class="bi bi-trash"></i>
                                                                                </button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="alert alert-light border small text-muted mb-3 py-2 text-center">
                                                    <i class="bi bi-info-circle me-1"></i> No partial payment entries logged yet.
                                                </div>
                                            @endif

                                            <!-- Add New Payment / Update Status Form -->
                                            <form action="{{ route('purchases.update-payment-status', $purchase) }}" method="POST">
                                                @csrf
                                                <div class="card card-body bg-light border-0 rounded-3 p-3">
                                                    <h6 class="fw-bold font-outfit text-dark small text-uppercase tracking-wider mb-2">
                                                        <i class="bi bi-plus-circle me-1"></i> Enter Partial Payment / Update Status
                                                    </h6>
                                                    <div class="row g-2">
                                                        <div class="col-12 col-md-6 mb-2">
                                                            <label class="form-label fw-semibold small">Payment Status <span class="text-danger">*</span></label>
                                                            <select name="payment_status" class="form-select form-select-sm" onchange="toggleModalPartialInput(this, {{ $pDue }}, 'modal_payment_amount_{{ $purchase->id }}')">
                                                                <option value="partial" {{ $purchase->payment_status === 'partial' || $pDue > 0 ? 'selected' : '' }}>Partial</option>
                                                                <option value="paid" {{ $purchase->payment_status === 'paid' ? 'selected' : '' }}>Paid (Full)</option>
                                                                <option value="unpaid" {{ $purchase->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid (Reset)</option>
                                                                <option value="pending" {{ $purchase->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-12 col-md-6 mb-2">
                                                            <label class="form-label fw-semibold small">Payment Amount (₹)</label>
                                                            <input type="number" step="any" min="0.01" max="{{ $pDue }}" name="payment_amount" id="modal_payment_amount_{{ $purchase->id }}" class="form-control form-control-sm font-monospace" placeholder="Enter amount (Max: ₹{{ number_format($pDue, 2) }})" {{ $pDue <= 0 ? 'readonly' : '' }}>
                                                            <div class="form-text small text-muted">Max payable: ₹{{ number_format($pDue, 2) }} (Cannot exceed Grand Total)</div>
                                                        </div>
                                                        <div class="col-12 col-md-6 mb-2">
                                                            <label class="form-label fw-semibold small">Payment Date</label>
                                                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}">
                                                        </div>
                                                        <div class="col-12 col-md-6 mb-2">
                                                            <label class="form-label fw-semibold small">Notes / Payment Method</label>
                                                            <input type="text" name="notes" class="form-control form-control-sm" placeholder="e.g. Cash, GPay, Bank Transfer...">
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 text-end">
                                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-medium">Save Payment</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-light btn-sm border rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 9 : 8 }}" class="text-center py-5 text-muted">
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
    function toggleModalPartialInput(selectEl, dueAmount, inputId) {
        var val = selectEl.value;
        var input = document.getElementById(inputId);
        if (!input) return;
        if (val === 'paid') {
            input.value = dueAmount.toFixed(2);
            input.readOnly = true;
        } else if (val === 'unpaid') {
            input.value = '0';
            input.readOnly = true;
        } else {
            input.value = '';
            input.readOnly = false;
        }
    }
</script>

@endsection
