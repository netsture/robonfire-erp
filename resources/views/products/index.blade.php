@extends('layouts.app')

@section('title', 'Product Catalog')
@section('page_title', 'Product & Inventory Management')
@section('page_subtitle', 'Master item list, prices, cost margins, and real-time stock levels')

@section('header_actions')
    <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-box-seam-fill me-1"></i> Add Product
    </a>
@endsection

@section('content')

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search product title, SKU, barcode..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3">
            <select name="category_id" class="form-select bg-light">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2">
            <div class="form-check pt-2">
                <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock" {{ request('low_stock') ? 'checked' : '' }}>
                <label class="form-check-label small text-dark fw-medium" for="low_stock">
                    Low Stock Only
                </label>
            </div>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Filter</button>
            <a href="{{ route('products.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Products Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Product Name / SKU</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>In Stock</th>
                    <th>Alert Limit</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-box-seam"></i>
                                </div>
                                <div>
                                    <a href="{{ route('products.show', $product) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                        {{ $product->name }}
                                    </a>
                                    <div class="small text-muted font-monospace"><i class="bi bi-barcode me-1"></i>{{ $product->sku }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $product->category->name }}</span>
                        </td>
                        <td class="text-muted small">${{ number_format($product->cost_price, 2) }}</td>
                        <td class="fw-bold text-dark">${{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            @if($product->isLowStock())
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-exclamation-triangle me-1"></i>{{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold">
                                    {{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            @endif
                        </td>
                        <td class="small text-muted">{{ $product->alert_quantity }} {{ $product->unit }}</td>
                        <td>
                            @if($product->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-light border" title="View Audit Trail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete product from inventory?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                            No products found in catalog. Click "Add Product" to add stock items.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="p-3 border-top">
            {{ $products->links() }}
        </div>
    @endif
</div>

@endsection
