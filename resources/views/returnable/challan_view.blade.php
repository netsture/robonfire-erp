@extends('layouts.app')

@section('title', 'Return Challan')
@section('page_title', 'Return Challan: #' . $returnableMaterial->return_number)
@section('page_subtitle', 'Customer: ' . ($returnableMaterial->customer->company_name ?? 'N/A') . ' | Date: ' . $returnableMaterial->return_date)

@section('header_actions')
    <a href="{{ route('returnable.print', $returnableMaterial) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" target="_blank">
        <i class="bi bi-printer me-1"></i> Print Challan
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
                    <h4 class="fw-bold font-outfit text-dark mb-0">Return Challan</h4>
                    <span class="text-muted small font-monospace">Challan #: {{ $returnableMaterial->return_number }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-4 py-2 fs-6">RETURNED</span>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6">
                    <span class="text-muted small fw-semibold text-uppercase tracking-wider">CUSTOMER</span>
                    <div class="fw-bold text-dark mt-2 mb-1 fs-5">{{ $returnableMaterial->customer->company_name ?? 'N/A' }}</div>
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
                    <div class="small text-dark mb-1">Challan No: <strong>#{{ $returnableMaterial->return_number }}</strong></div>
                    @if($returnableMaterial->project_name)
                        <div class="small text-dark mb-1">Project Name: <strong>{{ $returnableMaterial->project_name }}</strong></div>
                    @endif
                    @if($returnableMaterial->return_reason)
                        <div class="small text-dark mb-1">Reason for Return: <strong>{{ $returnableMaterial->return_reason }}</strong></div>
                    @endif
                    <div class="small text-dark mb-1">Recorded By: <strong>{{ $returnableMaterial->user->name ?? 'Admin' }}</strong></div>
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnableMaterial->items as $item)
                            @php
                                $prod = $item->product;
                            @endphp
                            <tr>
                                <td><span class="badge bg-light text-dark border">{{ $prod->category->name ?? 'N/A' }}</span></td>
                                <td class="fw-semibold text-dark">{{ $prod->name ?? 'N/A' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                                <td class="small font-monospace text-muted">{{ $prod->hsn_code ?? 'N/A' }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary border px-2">{{ $prod->unit ?? 'Pcs' }}</span></td>
                                <td class="text-center fw-bold">{{ $item->quantity }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($returnableMaterial->notes)
                <div class="p-3 bg-light rounded-3 border">
                    <span class="text-muted small fw-semibold d-block mb-1">RETURN NOTES</span>
                    <div class="small text-dark">{{ $returnableMaterial->notes }}</div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
