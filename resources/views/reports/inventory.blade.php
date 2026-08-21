@extends('layouts.app')

@section('title', 'Stock Valuation')
@section('page_title', 'Inventory Valuation & Asset Report')
@section('page_subtitle', 'Comprehensive breakdown of warehouse stock asset value and profit margins')

@section('header_actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6">
        <div class="card card-custom border-0 p-3 bg-dark text-white">
            <span class="text-white-50 small fw-semibold">TOTAL STOCK COST ASSET VALUE</span>
            <h2 class="fw-bold font-outfit mt-1 mb-0">₹{{ number_format($totalCostValue, 2) }}</h2>
        </div>
    </div>
    <div class="col-6">
        <div class="card card-custom border-0 p-3 bg-primary text-white">
            <span class="text-white-50 small fw-semibold">RETAIL MARGIN TURNOVER</span>
            <h2 class="fw-bold font-outfit mt-1 mb-0">₹{{ number_format($totalRetailValue, 2) }}</h2>
        </div>
    </div>
</div>

<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Product Name</th>
                    <th>Category</th>
                    <th>Qty in Stock</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Total Cost Value</th>
                    <th class="text-end pe-4">Total Retail Value</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    @php
                        $itemCostVal = $product->stock_quantity * $product->cost_price;
                        $itemRetailVal = $product->stock_quantity * $product->selling_price;
                    @endphp
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold text-dark">{{ $product->name }}</div>
                            <div class="small text-muted font-monospace">{{ $product->sku }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $product->category->name }}</span></td>
                        <td class="fw-bold">{{ $product->stock_quantity }} {{ $product->unit }}</td>
                        <td>₹{{ number_format($product->cost_price, 2) }}</td>
                        <td>₹{{ number_format($product->selling_price, 2) }}</td>
                        <td class="fw-semibold text-dark">₹{{ number_format($itemCostVal, 2) }}</td>
                        <td class="text-end pe-4 fw-bold text-primary">₹{{ number_format($itemRetailVal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No products available in stock.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
