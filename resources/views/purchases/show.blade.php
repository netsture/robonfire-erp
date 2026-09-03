@extends('layouts.app')

@section('title', 'Purchase Order Details')
@section('page_title', 'Purchase Order: ' . $purchase->project_name)
@section('page_subtitle', 'Supplier: ' . $purchase->supplier->company_name . ' | Date: ' . $purchase->purchase_date)

@section('header_actions')
    <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-printer me-1"></i> Print Invoice
    </a>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card card-custom border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">Purchase Invoice</h4>
                    <span class="text-muted small">Project: {{ $purchase->project_name }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($purchase->payment_status === 'paid')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-4 py-2 fs-6">PAID</span>
                    @elseif($purchase->payment_status === 'unpaid')
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-4 py-2 fs-6">UNPAID</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-4 py-2 fs-6">PENDING</span>
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
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">VENDOR / SUPPLIER</span>
                    <div class="fw-bold text-dark mt-2 mb-1 fs-5">{{ $purchase->supplier->company_name ?? 'N/A' }}</div>
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
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">ORDER METADATA</span>
                    <div class="small text-dark mt-2 mb-1">Purchase Date: <strong>{{ $purchase->purchase_date }}</strong></div>
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
                                <td><span class="badge bg-light text-dark border">{{ $prod->category->name ?? 'N/A' }}</span></td>
                                <td class="fw-semibold text-dark">{{ $prod->name ?? 'N/A' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                                <td class="small font-monospace text-muted">{{ $prod->hsn_code ?? 'N/A' }}</td>
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
                            <span class="text-muted">Discount:</span>
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
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Amount Paid:</span>
                            <span class="fw-bold text-success">₹{{ number_format($purchase->paid_amount, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!auth()->user()->isSuperAdmin())
<!-- Update Payment Modal -->
<div class="modal fade" id="updatePaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('purchases.update-payment-status', $purchase) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold font-outfit">Set Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Invoice #:</span>
                            <span class="fw-bold font-monospace">{{ $purchase->invoice_number }}</span>
                        </div>
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
                        <select name="payment_status" class="form-select" onchange="toggleModalPaidInput(this, {{ $purchase->grand_total }}, 'modal_paid_show')">
                            <option value="pending" {{ in_array($purchase->payment_status, ['pending', 'due']) ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $purchase->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ $purchase->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Paid Amount (₹)</label>
                        <input type="number" step="0.01" name="paid_amount" id="modal_paid_show" class="form-control" value="{{ number_format($purchase->paid_amount, 2, '.', '') }}" {{ $purchase->payment_status !== 'partial' ? 'readonly' : '' }}>
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

<script>
    function toggleModalPaidInput(selectEl, grandTotal, inputId) {
        var val = selectEl.value;
        var input = document.getElementById(inputId);
        if (!input) return;
        if (val === 'paid') {
            input.value = grandTotal.toFixed(2);
            input.readOnly = true;
        } else if (val === 'unpaid') {
            input.value = '0.00';
            input.readOnly = true;
        } else {
            input.readOnly = false;
        }
    }
</script>
@endif

@endsection
