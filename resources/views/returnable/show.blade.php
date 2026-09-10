@extends('layouts.app')

@section('title', 'Return Entry: #' . $returnableMaterial->return_number)
@section('page_title', 'Returnable Entry: #' . $returnableMaterial->return_number)
@section('page_subtitle', 'Customer: ' . ($returnableMaterial->customer->company_name ?? 'N/A') . ' | Date: ' . $returnableMaterial->return_date)

@section('header_actions')
    @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
        <a href="{{ route('returnable.edit', $returnableMaterial) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-pencil me-1"></i> Edit Return
        </a>
    @endif
    <!--<a href="{{ route('returnable.challan', $returnableMaterial) }}" class="btn btn-info text-white rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-truck me-1"></i> Return Challan View
    </a>-->
    <a href="{{ route('returnable.invoice', $returnableMaterial) }}" class="btn btn-outline-primary rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-printer me-1"></i> Print Invoice
    </a>
    <a href="{{ route('returnable.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Returnable Entry
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card card-custom border-0 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold font-outfit text-dark mb-0">Return Invoice</h4>
                    <span class="text-muted small font-monospace">Return No: {{ $returnableMaterial->return_number }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white rounded-pill px-4 py-2 fs-6">RETURNED</span>
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
                            <div class="fw-bold text-dark fs-5 mb-0">{{ $returnableMaterial->customer->company_name ?? 'N/A' }}</div>
                        </div>
                    </div>
                    @if($returnableMaterial->customer && $returnableMaterial->customer->address)
                        <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1 text-secondary"></i><strong>Address:</strong> {{ $returnableMaterial->customer->address }}</div>
                    @endif
                    @if($returnableMaterial->customer && ($returnableMaterial->customer->gst_number ?? $returnableMaterial->customer->gstin))
                        <div class="small text-muted mb-1"><i class="bi bi-card-heading me-1 text-secondary"></i><strong>Gst No:</strong> {{ $returnableMaterial->customer->gst_number ?? $returnableMaterial->customer->gstin }}</div>
                    @endif
                    @if($returnableMaterial->customer && $returnableMaterial->customer->phone)
                        <div class="small text-muted mb-1"><i class="bi bi-telephone me-1 text-secondary"></i><strong>Contact No:</strong> {{ $returnableMaterial->customer->phone }}</div>
                    @endif
                    @if($returnableMaterial->customer && $returnableMaterial->customer->email)
                        <div class="small text-muted mb-1"><i class="bi bi-envelope me-1 text-secondary"></i><strong>Email:</strong> {{ $returnableMaterial->customer->email }}</div>
                    @endif
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">RETURN DETAILS</span>
                    <div class="small text-dark mt-2 mb-1">Return Date: <strong>{{ $returnableMaterial->return_date }}</strong></div>
                    <div class="small text-dark mb-1">Return No: <strong>#{{ $returnableMaterial->return_number }}</strong></div>
                    @if($returnableMaterial->project_name)
                        <div class="small text-dark mb-1">Project Name: <strong>{{ $returnableMaterial->project_name }}</strong></div>
                    @endif
                    @if($returnableMaterial->return_reason)
                        <div class="small text-dark mb-1">Reason for Return: <strong>{{ $returnableMaterial->return_reason }}</strong></div>
                    @endif
                    <div class="small text-dark mb-1">Recorded By: <strong>{{ $returnableMaterial->user->name ?? 'Admin' }}</strong></div>
                </div>
            </div>

            <h6 class="fw-bold font-outfit text-dark mb-2">Returned Line Items</h6>
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
                            <th class="text-end">Unit Price (₹)</th>
                            <th class="text-end">Total (₹)</th>
                            <th class="text-center">Tax (%)</th>
                            <th class="text-end">Tax (₹)</th>
                            <th class="text-end">Total w/ Tax</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnableMaterial->items as $item)
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
                    @if($returnableMaterial->notes)
                        <div class="p-3 bg-light rounded-3 border">
                            <span class="text-muted small fw-semibold d-block mb-1">RETURN REMARKS / NOTES</span>
                            <div class="small text-dark">{{ $returnableMaterial->notes }}</div>
                        </div>
                    @endif
                </div>
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal:</span>
                            <span class="fw-bold text-dark">₹{{ number_format($returnableMaterial->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tax Amount:</span>
                            <span class="text-dark">+₹{{ number_format($returnableMaterial->tax_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Discount Amount:</span>
                            <span class="text-dark">-₹{{ number_format($returnableMaterial->discount_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Shipping Cost:</span>
                            <span class="text-dark">+₹{{ number_format($returnableMaterial->shipping_cost, 2) }}</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold text-dark">Grand Total:</span>
                            <span class="fw-bold text-primary fs-5">₹{{ number_format($returnableMaterial->grand_total, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
