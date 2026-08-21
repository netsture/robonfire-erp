@extends('layouts.app')

@section('title', 'Product Brands')
@section('page_title', 'Brand Master Management')
@section('page_subtitle', 'Manage manufacturers, product brands, and catalog lines')

@section('header_actions')
    <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#createBrandModal">
        <i class="bi bi-patch-check me-1"></i> Add New Brand
    </button>
@endsection

@section('content')

<div class="row g-3">
    @forelse($brands as $brand)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-custom border-0 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-award-fill fs-4"></i>
                    </div>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                        <i class="bi bi-box-seam me-1"></i>{{ $brand->products_count }} Products
                    </span>
                </div>

                <h4 class="fw-bold font-outfit text-dark mb-1">{{ $brand->name }}</h4>
                <p class="text-muted small mb-3">{{ $brand->description ?? 'No brand description specified.' }}</p>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small font-monospace">slug: {{ $brand->slug }}</span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editBrandModal_{{ $brand->id }}" title="Edit Brand">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('brands.destroy', $brand) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete brand master?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete Brand">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Brand Modal -->
        <div class="modal fade" id="editBrandModal_{{ $brand->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 border-0">
                    <form action="{{ route('brands.update', $brand) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold font-outfit">Edit Brand Master</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $brand->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $brand->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Brand</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-award fs-1 d-block mb-2 text-secondary"></i>
            No brand records created yet. Click "Add New Brand" to get started.
        </div>
    @endforelse
</div>

<!-- Create Brand Modal -->
<div class="modal fade" id="createBrandModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('brands.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold font-outfit">Add New Brand Master</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Minimax, Ceasefire, Kidde, Kanex" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="e.g. Certified fire safety extinguishers, hydrants & alarms"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Brand</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
