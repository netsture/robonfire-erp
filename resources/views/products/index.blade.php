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
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-3' : 'col-md-3' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Product Name, HSN Code..." value="{{ request('search') }}">
            </div>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-12 col-md-2">
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
            <select name="brand_id" class="form-select bg-light">
                <option value="">All Brands</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand_id') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-1' : 'col-md-1' }}">
            <div class="form-check pt-1">
                <input class="form-check-input" type="checkbox" name="low_stock" value="1" id="low_stock" {{ request('low_stock') ? 'checked' : '' }}>
                <label class="form-check-label small text-dark fw-medium" for="low_stock">
                    Low Stock
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
                    <th>Category</th>
                    <th>Brand</th>
                    <th>HSN Code</th>
                    <th>Type</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Tax (%)</th>
                    <th>In Stock</th>
                    <th>Low Stock</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-product" style="width: 40px; height: 40px;">
                                    @if(!empty($product->image) && file_exists(public_path('storage/' . $product->image)))
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="rounded-circle w-100 h-100 object-fit-cover">
                                    @else
                                        <i class="bi bi-box-seam fs-5"></i>
                                    @endif
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
                            <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-tag-fill me-1"></i>{{ $product->category->name }}</span>
                        </td>
                        <td>
                            @if($product->brand)
                                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #6f42c1;"><i class="bi bi-award-fill me-1"></i>{{ $product->brand->name }}</span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td class="small font-monospace">
                            @if($product->hsn_code)
                                <span class="badge bg-dark text-white rounded-pill px-2.5 py-1 font-monospace fw-semibold"><i class="bi bi-hash me-1 text-warning"></i>{{ $product->hsn_code }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-rulers me-1"></i>{{ $product->unit }}</span>
                        </td>
                        <td class="text-dark small">₹{{ number_format($product->cost_price, 2) }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($product->selling_price, 2) }}</td>
                        <td>
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold">
                                {{ number_format($product->tax_percent, 2) }}%
                            </span>
                        </td>
                        <td>
                            @if($product->isLowStock())
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            @else
                                <span class="badge bg-success text-white rounded-pill px-3 py-1 fw-bold">
                                    <i class="bi bi-check-circle-fill me-1"></i>{{ $product->stock_quantity }} {{ $product->unit }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-1 fw-bold">
                                <i class="bi bi-bell-fill me-1"></i>{{ $product->alert_quantity }} {{ $product->unit }}
                            </span>
                        </td>
                        <td>
                            @if($product->status === 'active')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1">Inactive</span>
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
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete product from catalog?')">
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
                        <td colspan="13" class="text-center py-5 text-muted">
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
