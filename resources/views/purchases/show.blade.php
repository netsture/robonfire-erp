@extends('layouts.app')

@section('title', 'Purchase Order Details')
@section('page_title', 'Purchase Order: ' . $purchase->project_name)
@section('page_subtitle', 'Supplier: ' . $purchase->supplier->name . ' | Date: ' . $purchase->purchase_date)

@section('header_actions')
    <a href="{{ route('purchases.print', $purchase) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-printer me-1"></i> Print Receipt
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
                    <h4 class="fw-bold font-outfit text-dark mb-0">ERP Procurement Order</h4>
                    <span class="text-muted small">Project: {{ $purchase->project_name }}</span>
                </div>
                <div>
                    @if($purchase->payment_status === 'paid')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-4 py-2 fs-6">PAID</span>
                    @elseif($purchase->payment_status === 'partial')
                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-4 py-2 fs-6">PARTIAL</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-4 py-2 fs-6">DUE</span>
                    @endif
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <span class="text-muted small fw-semibold">VENDOR / SUPPLIER</span>
                    <div class="fw-bold text-dark mt-1">{{ $purchase->supplier->name }}</div>
                    <div class="small text-muted">{{ $purchase->supplier->company_name }}</div>
                    <div class="small text-muted">{{ $purchase->supplier->phone }}</div>
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small fw-semibold">ORDER METADATA</span>
                    <div class="small text-dark mt-1">Date: <strong>{{ $purchase->purchase_date }}</strong></div>
                    @if($purchase->project_name)
                        <div class="small text-dark">Project: <strong>{{ $purchase->project_name }}</strong></div>
                    @endif
                    <div class="small text-dark">Recorded By: <strong>{{ $purchase->user->name ?? 'Admin' }}</strong></div>
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
                            <th>Unit</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Cost (₹)</th>
                            <th class="text-center">Tax (%)</th>
                            <th class="text-end">Total (₹)</th>
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
                                <td class="text-center">{{ number_format($taxPct, 2) }}%</td>
                                <td class="text-end font-monospace">₹{{ number_format($item->subtotal, 2) }}</td>
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
@endsection
