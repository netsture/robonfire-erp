@extends('layouts.app')

@section('title', 'Product Categories')
@section('page_title', 'Category Management')
@section('page_subtitle', 'Organize product catalog into hierarchical categories')

@section('header_actions')
    <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
        <i class="bi bi-folder-plus me-1"></i> Add Category
    </button>
@endsection

@section('content')

<div class="row g-3">
    @forelse($categories as $category)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-custom border-0 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-tags fs-4"></i>
                    </div>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                        <i class="bi bi-box-seam me-1"></i>{{ $category->products_count }} Products
                    </span>
                </div>

                <h4 class="fw-bold font-outfit text-dark mb-1">{{ $category->name }}</h4>
                <p class="text-muted small mb-3">{{ $category->description ?? 'No description.' }}</p>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small font-monospace">slug: {{ $category->slug }}</span>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#editCategoryModal_{{ $category->id }}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete category?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-light border text-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Category Modal -->
        <div class="modal fade" id="editCategoryModal_{{ $category->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content rounded-4 border-0">
                    <form action="{{ route('categories.update', $category) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-header border-bottom">
                            <h5 class="modal-title fw-bold font-outfit">Edit Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $category->name }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ $category->description }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center py-5 text-muted">
            <i class="bi bi-tags fs-1 d-block mb-2 text-secondary"></i>
            No product categories created yet. Click "Add Category" to get started.
        </div>
    @endforelse
</div>

<!-- Create Category Modal -->
<div class="modal fade" id="createCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold font-outfit">Add New Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Industrial Electronics" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="Brief category description"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
