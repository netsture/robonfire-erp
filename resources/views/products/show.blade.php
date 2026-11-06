@extends('layouts.app')

@section('title', 'Product Overview')
@section('page_title', $product->name)
@section('page_subtitle', 'Category: ' . $product->category->name)

@section('header_actions')
    <a href="{{ route('reports.product-stock', ['product_id' => $product->id]) }}" class="btn btn-outline-info rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-clock-history me-1"></i> Stock Ledger Report
    </a>
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('products.edit', $product) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-pencil me-1"></i> Edit Product
        </a>
    @endif
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Product
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <!-- Key Details -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle dark-symbol-avatar dark-symbol-product" style="width: 54px; height: 54px;">
                    @if(!empty($product->image) && file_exists(public_path('storage/' . $product->image)))
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded-circle w-100 h-100 object-fit-cover">
                    @else
                        <i class="bi bi-box-seam fs-3"></i>
                    @endif
                </div>
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-0">{{ $product->name }}</h5>
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-tag-fill me-1"></i>{{ $product->category->name }}</span>
                    @if($product->brand)
                        <span class="badge text-white rounded-pill px-2.5 py-1 ms-1" style="background-color: #6f42c1;"><i class="bi bi-award-fill me-1"></i>{{ $product->brand->name }}</span>
                    @endif
                </div>
            </div>

            <hr class="my-2 text-muted">

            <div class="d-flex flex-column gap-2 my-2">
                <div class="small"><i class="bi bi-qr-code me-2 text-muted"></i>Product Unique ID: <span class="badge text-dark rounded-pill px-2.5 py-1 font-monospace fw-bold ms-1 border border-warning" style="background-color: #fef08a; color: #713f12 !important;">{{ $product->product_identifier ?? 'N/A' }}</span></div>
                <div class="small"><i class="bi bi-hash me-2 text-muted"></i>HSN / SAC: <span class="badge bg-dark text-white rounded-pill px-2.5 py-1 font-monospace fw-semibold ms-1"><i class="bi bi-hash me-1 text-warning"></i>{{ $product->hsn_code ?? 'N/A' }}</span></div>
                <div class="small"><i class="bi bi-percent me-2 text-muted"></i>Tax Rate: <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 ms-1 fw-bold">{{ number_format($product->tax_percent, 2) }}%</span></div>
                <div class="small"><i class="bi bi-rulers me-2 text-muted"></i>Measurement Type: <span class="badge bg-info text-white rounded-pill px-2.5 py-1 ms-1 fw-semibold"><i class="bi bi-rulers me-1"></i>{{ $product->unit }}</span></div>
                <div class="small"><i class="bi bi-calendar-event me-2 text-muted"></i>Created Date: <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 ms-1 font-monospace fw-semibold">{{ $product->created_at ? $product->created_at->format('d-m-Y') : 'N/A' }}</span></div>
            </div>
        </div>
    </div>

    <!-- Pricing & Stock Metrics -->
    <div class="col-12 col-md-8">
        <div class="row g-3 h-100">
            <div class="col-6 col-sm-4">
                <div class="card card-custom border-0 p-3 text-white text-center h-100 justify-content-center" style="background-color: #e54848;">
                    <span class="text-white small fw-semibold">COST PRICE</span>
                    <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($product->cost_price, 2) }}</h3>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="card card-custom border-0 p-3 bg-success text-white text-center h-100 justify-content-center">
                    <span class="text-white small fw-semibold">SELLING RETAIL PRICE</span>
                    <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($product->selling_price, 2) }}</h3>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="card card-custom border-0 p-3 bg-dark text-white text-center h-100 justify-content-center">
                    <span class="text-white small fw-semibold">CURRENT IN-STOCK</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-0 {{ $product->isLowStock() ? 'text-danger' : 'text-success' }}">
                        {{ $product->stock_quantity }} {{ $product->unit }}
                    </h3>
                    <span class="small text-white mt-1">Alert limit: {{ $product->alert_quantity }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Activity & Edit History -->
<div class="card card-custom border-0 p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold font-outfit text-dark mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Product Activity & Change History</h5>
        <a href="{{ route('reports.product-logs', ['search' => $product->product_identifier ?? $product->name]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" target="_blank">
            <i class="bi bi-journal-text me-1"></i> Full Product Log Report
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Date & Time</th>
                    <th>Action</th>
                    <th>Changed By (User)</th>
                    <th>Change Summary</th>
                </tr>
            </thead>
            <tbody>
                @forelse($product->logs as $idx => $log)
                    <tr>
                        <td class="fw-bold text-muted">{{ $idx + 1 }}</td>
                        <td class="small font-monospace text-dark">{{ $log->created_at ? $log->created_at->format('d-m-Y, h:i A') : 'N/A' }}</td>
                        <td>
                            @if($log->action === 'create')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1"><i class="bi bi-plus-circle me-1"></i>Added</span>
                            @elseif($log->action === 'update')
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1"><i class="bi bi-pencil-square me-1"></i>Updated</span>
                            @elseif($log->action === 'delete')
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1"><i class="bi bi-trash me-1"></i>Deleted</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $log->user->name ?? 'System' }}</span>
                            @if($log->user?->role)
                                <span class="badge bg-light text-muted border ms-1">{{ ucfirst($log->user->role) }}</span>
                            @endif
                        </td>
                        <td class="small text-dark">{{ $log->description }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No activity logs recorded for this product yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
