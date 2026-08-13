@extends('layouts.app')

@section('title', 'Product Overview')
@section('page_title', $product->name)
@section('page_subtitle', 'SKU: ' . $product->sku . ' | Category: ' . $product->category->name)

@section('header_actions')
    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-pencil me-1"></i> Edit Product
    </a>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Catalog
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <!-- Key Details -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-box-seam fs-3"></i>
                </div>
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-0">{{ $product->name }}</h5>
                    <span class="badge bg-light text-dark border">{{ $product->category->name }}</span>
                </div>
            </div>

            <hr class="my-2 text-muted">

            <div class="d-flex flex-column gap-2 my-2">
                <div class="small"><i class="bi bi-barcode me-2 text-muted"></i>SKU: <span class="font-monospace fw-bold">{{ $product->sku }}</span></div>
                <div class="small"><i class="bi bi-qr-code me-2 text-muted"></i>Barcode: <span class="font-monospace">{{ $product->barcode ?? 'N/A' }}</span></div>
                <div class="small"><i class="bi bi-tag me-2 text-muted"></i>Tax Rate: {{ $product->tax_percent }}%</div>
                <div class="small"><i class="bi bi-rulers me-2 text-muted"></i>Unit: {{ $product->unit }}</div>
            </div>
        </div>
    </div>

    <!-- Pricing & Stock Metrics -->
    <div class="col-12 col-md-8">
        <div class="row g-3 h-100">
            <div class="col-6 col-sm-4">
                <div class="card card-custom border-0 p-3 bg-light text-center h-100 justify-content-center">
                    <span class="text-muted small fw-semibold">COST PRICE</span>
                    <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">${{ number_format($product->cost_price, 2) }}</h3>
                </div>
            </div>
            <div class="col-6 col-sm-4">
                <div class="card card-custom border-0 p-3 bg-primary text-white text-center h-100 justify-content-center">
                    <span class="text-white-50 small fw-semibold">SELLING RETAIL PRICE</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-0">${{ number_format($product->selling_price, 2) }}</h3>
                </div>
            </div>
            <div class="col-12 col-sm-4">
                <div class="card card-custom border-0 p-3 bg-dark text-white text-center h-100 justify-content-center">
                    <span class="text-white-50 small fw-semibold">CURRENT IN-STOCK</span>
                    <h3 class="fw-bold font-outfit mt-2 mb-0 {{ $product->isLowStock() ? 'text-danger' : 'text-success' }}">
                        {{ $product->stock_quantity }} {{ $product->unit }}
                    </h3>
                    <span class="small text-white-50 mt-1">Alert limit: {{ $product->alert_quantity }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Stock Adjustments Audit Trail -->
<div class="card card-custom border-0 p-4">
    <h5 class="fw-bold font-outfit mb-3 text-dark">Stock Adjustment Audit Log</h5>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Date & Time</th>
                    <th>Adjusted By</th>
                    <th>Type</th>
                    <th>Quantity</th>
                    <th>Audit Reason</th>
                </tr>
            </thead>
            <tbody>
                @forelse($product->stockAdjustments as $adj)
                    <tr>
                        <td class="small text-muted">{{ $adj->created_at->format('M d, Y H:i') }}</td>
                        <td class="fw-semibold text-dark">{{ $adj->user->name ?? 'System' }}</td>
                        <td>
                            @if($adj->type === 'add')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3">+ STOCK ADD</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3">- STOCK SUBTRACT</span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">{{ $adj->quantity }} {{ $product->unit }}</td>
                        <td class="small text-muted">{{ $adj->reason }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No manual stock adjustments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
