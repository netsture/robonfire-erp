@extends('layouts.app')

@section('title', 'Product Catalog')
@section('page_title', 'Product & Inventory Management')
@section('page_subtitle', 'Master item list, prices, cost margins, and real-time stock levels')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('products.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-box-seam-fill me-1"></i> Add Product
        </a>
    @endif
@endsection

@section('content')

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('products.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-3' : 'col-md-5' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search product title, HSN code..." value="{{ request('search') }}">
            </div>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-12 col-md-3">
            <select name="firm_id" class="form-select bg-light">
                <option value="">All Firms</option>
                @foreach($firms as $firm)
                    <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-12 col-md-2">
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
                    <th class="ps-4">Product Name</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Category / Brand</th>
                    <th>HSN Code</th>
                    <th>Measurement Type</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Tax (%)</th>
                    <th>In Stock</th>
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
                                </div>
                            </div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $product->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $product->category->name }}</span>
                            @if($product->brand)
                                <div class="mt-1"><span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-1 small"><i class="bi bi-award-fill me-1"></i>{{ $product->brand->name }}</span></div>
                            @endif
                        </td>
                        <td class="small font-monospace">
                            @if($product->hsn_code)
                                <span class="badge bg-light text-dark border font-monospace">{{ $product->hsn_code }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">{{ $product->unit }}</span>
                        </td>
                        <td class="text-muted small">₹{{ number_format($product->cost_price, 2) }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-dark border border-secondary-subtle rounded-pill px-3 py-1 fw-semibold">
                                {{ number_format($product->tax_percent, 2) }}%
                            </span>
                        </td>
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
                                @if(!auth()->user()->isSuperAdmin())
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
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
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
