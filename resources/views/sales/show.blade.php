@extends('layouts.app')

@section('title', 'Sales Order Details')
@section('page_title', 'Sales Order: #' . $sale->invoice_number)
@section('page_subtitle', 'Customer: ' . $sale->customer->company_name . ' | Date: ' . $sale->sale_date)

@section('header_actions')
    @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-pencil me-1"></i> Edit Sale
        </a>
    @endif
    <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-outline-primary rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-printer me-1"></i> Print Invoice
    </a>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Sales
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card card-custom border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">Delivery Invoice</h4>
                    <span class="text-muted small font-monospace">Challan No : {{ $sale->invoice_number }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($sale->payment_status === 'paid')
                        <span class="badge bg-success text-white rounded-pill px-4 py-2 fs-6">PAID</span>
                    @elseif($sale->payment_status === 'unpaid')
                        <span class="badge bg-danger text-white rounded-pill px-4 py-2 fs-6">UNPAID</span>
                    @elseif($sale->payment_status === 'partial')
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
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">CUSTOMER DETAILS</span>
                    <div class="d-flex align-items-center gap-3 mt-2 mb-2">
                        <div class="rounded-circle dark-symbol-avatar dark-symbol-customer" style="width: 42px; height: 42px;">
                            <i class="bi bi-person-circle fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5 mb-0">{{ $sale->customer->company_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    @if($sale->customer->address)
                        <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1 text-secondary"></i><strong>Address:</strong> {{ $sale->customer->address }}</div>
                    @endif
                    @if($sale->customer->gst_number)
                        <div class="small text-muted mb-1"><i class="bi bi-card-heading me-1 text-secondary"></i><strong>Gst No:</strong> {{ $sale->customer->gst_number }}</div>
                    @endif
                    @if($sale->customer->phone)
                        <div class="small text-muted mb-1"><i class="bi bi-telephone me-1 text-secondary"></i><strong>Contact No:</strong> {{ $sale->customer->phone }}</div>
                    @endif
                    @if($sale->customer->email)
                        <div class="small text-muted mb-1"><i class="bi bi-envelope me-1 text-secondary"></i><strong>Email:</strong> {{ $sale->customer->email }}</div>
                    @endif
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">DELIVERY DETAILS</span>
                    <div class="small text-dark mt-2 mb-1">Challan Date: <strong>{{ $sale->sale_date }}</strong></div>
                    <div class="small text-dark mb-1">Challan No: <strong>#{{ $sale->invoice_number }}</strong></div>
                    @if($sale->project_name)
                        <div class="small text-dark mb-1">Project Name: <strong>{{ $sale->project_name }}</strong></div>
                    @endif
                    @if($sale->vehicle_number)
                        <div class="small text-dark mb-1">Vehicle No: <strong>{{ $sale->vehicle_number }}</strong></div>
                    @endif
                    <div class="small text-dark mb-1">Recorded By: <strong>{{ $sale->user->name ?? 'Admin' }}</strong></div>
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
                            <th class="text-end">Selling Price (₹)</th>
                            <th class="text-end">Total (₹)</th>
                            <th class="text-center">Tax (%)</th>
                            <th class="text-end">Tax (₹)</th>
                            <th class="text-end">Total w/ Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
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
                                <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                                <td class="text-end font-monospace">₹{{ number_format($item->subtotal, 2) }}</td>
                                <td class="text-center">{{ number_format($taxPct, 2) }}%</td>
                                <td class="text-end font-monospace text-dark">₹{{ number_format($lineTax, 2) }}</td>
                                <td class="text-end fw-bold text-dark font-monospace">₹{{ number_format($totalWithTax, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    @if($sale->notes)
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small fw-semibold d-block mb-1">SALESORDER NOTES</span>
                            <div class="small text-dark">{{ $sale->notes }}</div>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold text-dark">₹{{ number_format($sale->subtotal, 2) }}</span>
                        </div>                        
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax Amount:</span>
                            <span class="text-dark">+₹{{ number_format($sale->tax_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount Amount:</span>
                            <span class="text-dark">-₹{{ number_format($sale->discount_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping Cost:</span>
                            <span class="text-dark">+₹{{ number_format($sale->shipping_cost, 2) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-dark">Grand Total:</span>
                            <span class="fw-bold text-primary fs-5">₹{{ number_format($sale->grand_total, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Amount Received:</span>
                            <span class="fw-bold text-success">₹{{ number_format($sale->paid_amount, 2) }}</span>
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
            <form action="{{ route('sales.update-payment-status', $sale) }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold font-outfit">Set Payment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 p-3 bg-light rounded-3">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted small">Challan #:</span>
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
                        <select name="payment_status" class="form-select" onchange="toggleModalPaidInput(this, {{ $sale->grand_total }}, 'modal_paid_show')">
                            <option value="pending" {{ in_array($sale->payment_status, ['pending', 'due']) ? 'selected' : '' }}>Pending</option>
                            <option value="paid" {{ $sale->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            <option value="unpaid" {{ $sale->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount Received (₹)</label>
                        <input type="number" name="paid_amount" id="modal_paid_show" class="form-control" value="{{ (float)$sale->paid_amount == 0 ? 0 : (float)$sale->paid_amount }}" readonly>
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

@push('scripts')
<script>
    function toggleModalPaidInput(selectEl, grandTotal, targetInputId) {
        const inputEl = document.getElementById(targetInputId);
        if (!inputEl) return;
        if (selectEl.value === 'paid') {
            inputEl.value = parseFloat(grandTotal).toFixed(2);
        } else {
            inputEl.value = '0';
        }
    }
</script>
@endpush
@endif
@endsection
