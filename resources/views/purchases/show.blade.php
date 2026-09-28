@extends('layouts.app')

@section('title', 'Purchase Order Details')
@section('page_title', 'Purchase Order: ' . $purchase->project_name)
@section('page_subtitle', 'Supplier: ' . ($purchase->supplier->company_name ?? 'N/A') . ' | Date: ' . ($purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') : 'N/A'))

@section('header_actions')
    @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
        <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-pencil me-1"></i> Edit Purchase
        </a>
    @endif
    <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-outline-primary rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-printer me-1"></i> Print Invoice
    </a>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
@endsection

@section('content')
@if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
@endif

@if(session('success'))
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    </div>
@endif

<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card card-custom border-0 p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">Purchase Invoice</h4>
                    <span class="text-muted small">Project: {{ $purchase->project_name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($purchase->payment_status === 'paid')
                        <span class="badge bg-success text-white rounded-pill px-4 py-2 fs-6">PAID</span>
                    @elseif($purchase->payment_status === 'unpaid')
                        <span class="badge bg-danger text-white rounded-pill px-4 py-2 fs-6">UNPAID</span>
                    @elseif($purchase->payment_status === 'partial')
                        <span class="badge bg-primary text-white rounded-pill px-4 py-2 fs-6">PARTIAL</span>
                    @else
                        <span class="badge bg-warning text-dark rounded-pill px-4 py-2 fs-6">PENDING</span>
                    @endif

                    @if(!auth()->user()->isSuperAdmin())
                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 font-outfit" data-bs-toggle="modal" data-bs-target="#updatePaymentModal">
                            <i class="bi bi-wallet2 me-1"></i> Update Payment
                        </button>
                    @endif
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">SUPPLIER DETAILS</span>
                    <div class="d-flex align-items-center gap-3 mt-2 mb-2">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-supplier" style="width: 42px; height: 42px;">
                            <i class="bi bi-truck fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5 mb-0">{{ $purchase->supplier->company_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    @if($purchase->supplier->address)
                        <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1 text-secondary"></i><strong>Address:</strong> {{ $purchase->supplier->address }}</div>
                    @endif
                    @if($purchase->supplier->gst_number)
                        <div class="small text-muted mb-1"><i class="bi bi-card-heading me-1 text-secondary"></i><strong>Gst No:</strong> {{ $purchase->supplier->gst_number }}</div>
                    @endif
                    @if($purchase->supplier->phone)
                        <div class="small text-muted mb-1"><i class="bi bi-telephone me-1 text-secondary"></i><strong>Contact No:</strong> {{ $purchase->supplier->phone }}</div>
                    @endif
                    @if($purchase->supplier->email)
                        <div class="small text-muted mb-1"><i class="bi bi-envelope me-1 text-secondary"></i><strong>Email:</strong> {{ $purchase->supplier->email }}</div>
                    @endif
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">PURCHASE ORDER DETAILS</span>
                    <div class="small text-dark mt-2 mb-1">Purchase Date: <strong>{{ $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y') : 'N/A' }}</strong></div>
                    <div class="small text-dark mb-1">Invoice No: <strong>#{{ $purchase->invoice_number }}</strong></div>
                    @if($purchase->project_name)
                        <div class="small text-dark mb-1">Project Name: <strong>{{ $purchase->project_name }}</strong></div>
                    @endif
                    <div class="small text-dark mb-1">Recorded By: <strong>{{ $purchase->user->name ?? 'Admin' }}</strong></div>
                </div>
            </div>

            <h6 class="fw-bold font-outfit text-dark mb-2">Order Line Items</h6>
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 55px;">Symbol</th>
                            <th>Category</th>
                            <th>Product Item (Brand)</th>
                            <th>HSN Code</th>
                            <th>Type</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Cost (₹)</th>
                            <th class="text-end">Total (₹)</th>
                            <th class="text-center">Tax (%)</th>
                            <th class="text-end">Tax (₹)</th>
                            <th class="text-end">Total w/ Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                            @php
                                $prod = $item->product;
                                $taxPct = $prod->tax_percent ?? 0;
                                $lineTax = ($item->subtotal * $taxPct) / 100;
                                $totalWithTax = $item->subtotal + $lineTax;
                            @endphp
                            <tr>
                                <td class="text-center">
                                    <div class="rounded-circle dark-symbol-avatar dark-symbol-product mx-auto" style="width: 38px; height: 38px;">
                                        @if(!empty($prod->image) && file_exists(public_path('storage/' . $prod->image)))
                                            <img src="{{ asset('storage/' . $prod->image) }}" alt="{{ $prod->name }}" class="rounded-circle w-100 h-100 object-fit-cover">
                                        @else
                                            <i class="bi bi-box-seam fs-6"></i>
                                        @endif
                                    </div>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $prod->category->name ?? 'N/A' }}</span></td>
                                <td class="fw-semibold text-dark">{{ $prod->name ?? 'N/A' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                                <td class="small font-monospace text-dark">{{ $prod->hsn_code ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary border px-2">{{ $prod->unit ?? 'Pcs' }}</span></td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                <td class="text-end">₹{{ number_format($item->unit_cost, 2) }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($item->subtotal, 2) }}</td>
                                <td class="text-center">{{ number_format($taxPct, 2) }}%</td>
                                <td class="text-end font-monospace">₹{{ number_format($lineTax, 2) }}</td>
                                <td class="text-end fw-bold text-dark font-monospace">₹{{ number_format($totalWithTax, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end">
                <div class="col-12 col-md-5">
                    <div class="bg-light rounded-3 p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold text-dark">₹{{ number_format($purchase->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax Amount:</span>
                            <span class="text-dark">+₹{{ number_format($purchase->tax_amount ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount Amount:</span>
                            <span class="text-dark">-₹{{ number_format($purchase->discount_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping Cost:</span>
                            <span class="text-dark">+₹{{ number_format($purchase->shipping_cost, 2) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Grand Total:</span>
                            <span class="fw-bold text-primary fs-5">₹{{ number_format($purchase->grand_total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Amount Paid:</span>
                            <span class="fw-bold text-success">₹{{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Remaining Balance:</span>
                            <span class="fw-bold text-danger">₹{{ number_format(max(0, $purchase->grand_total - $purchase->paid_amount), 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Partial Payment Details & History Section -->
        @php
            $gTotal = (float)$purchase->grand_total;
            $pPaid = (float)$purchase->paid_amount;
            $pDue = max(0, $gTotal - $pPaid);
        @endphp
        <div class="card card-custom border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-0">
                        <i class="bi bi-receipt me-2 text-primary"></i>Payment Details & History
                    </h5>
                    <span class="text-muted small">Comprehensive partial payment tracking for this purchase order</span>
                </div>
                @if(!auth()->user()->isSuperAdmin())
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 font-outfit" data-bs-toggle="modal" data-bs-target="#updatePaymentModal">
                        <i class="bi bi-plus-circle me-1"></i> Add Partial Payment
                    </button>
                @endif
            </div>

            <!-- Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-4">
                    <div class="p-3 bg-light rounded-3 border text-center">
                        <span class="text-muted small d-block fw-semibold text-uppercase">Grand Total</span>
                        <span class="fw-bold fs-5 text-dark font-monospace">₹{{ number_format($gTotal, 2) }}</span>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 bg-light rounded-3 border text-center">
                        <span class="text-muted small d-block fw-semibold text-uppercase">Total Paid</span>
                        <span class="fw-bold fs-5 text-success font-monospace">₹{{ number_format($pPaid, 2) }}</span>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="p-3 bg-light rounded-3 border text-center">
                        <span class="text-muted small d-block fw-semibold text-uppercase">Remaining Due</span>
                        <span class="fw-bold fs-5 text-danger font-monospace">₹{{ number_format($pDue, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($purchase->payments->count() > 0)
                <div class="table-responsive border rounded-3">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small">
                            <tr>
                                <th class="ps-3">#</th>
                                <th>Payment Date</th>
                                <th>Amount Paid (₹)</th>
                                <th>Cumulative Paid</th>
                                <th>Remaining Due</th>
                                <th>Notes / Method</th>
                                @if(!auth()->user()->isSuperAdmin())
                                    <th class="text-end pe-3">Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody class="small">
                            @php $runningPaid = 0; @endphp
                            @foreach($purchase->payments as $index => $pmt)
                                @php
                                    $runningPaid += (float)$pmt->amount;
                                    $runningDue = max(0, $gTotal - $runningPaid);
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted">{{ $index + 1 }}</td>
                                    <td class="font-monospace text-dark">{{ \Carbon\Carbon::parse($pmt->payment_date)->format('d-m-Y') }}</td>
                                    <td class="fw-bold text-success font-monospace">₹{{ number_format($pmt->amount, 2) }}</td>
                                    <td class="font-monospace text-primary">₹{{ number_format($runningPaid, 2) }}</td>
                                    <td class="font-monospace text-danger">₹{{ number_format($runningDue, 2) }}</td>
                                    <td class="text-muted">{{ $pmt->notes ?? '-' }}</td>
                                    @if(!auth()->user()->isSuperAdmin())
                                        <td class="text-end pe-3">
                                            <form action="{{ route('purchases.payments.destroy', [$purchase, $pmt]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this partial payment entry?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0 border-0" title="Delete Payment">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-light border text-center py-4 text-muted mb-0">
                    <i class="bi bi-info-circle fs-4 d-block mb-1 text-secondary"></i>
                    No partial payment history entries recorded for this invoice yet.
                </div>
            @endif
        </div>
    </div>
</div>

@if(!auth()->user()->isSuperAdmin())
<!-- Update Payment Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0">
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
                                <select name="payment_status" class="form-select form-select-sm" onchange="toggleModalPartialInputShow(this, {{ $pDue }}, 'modal_payment_amount_show')">
                                    <option value="partial" {{ $purchase->payment_status === 'partial' || $pDue > 0 ? 'selected' : '' }}>Partial</option>
                                    <option value="paid" {{ $purchase->payment_status === 'paid' ? 'selected' : '' }}>Paid (Full)</option>
                                    <option value="unpaid" {{ $purchase->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid (Reset)</option>
                                    <option value="pending" {{ $purchase->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-6 mb-2">
                                <label class="form-label fw-semibold small">Payment Amount (₹)</label>
                                <input type="number" step="any" min="0.01" max="{{ $pDue }}" name="payment_amount" id="modal_payment_amount_show" class="form-control form-control-sm font-monospace" placeholder="Enter amount (Max: ₹{{ number_format($pDue, 2) }})" {{ $pDue <= 0 ? 'readonly' : '' }}>
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

<script>
    function toggleModalPartialInputShow(selectEl, dueAmount, inputId) {
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
@endif

@endsection
