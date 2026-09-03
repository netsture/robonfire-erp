@extends('layouts.app')

@section('title', 'Product Categories')
@section('page_title', 'Category Management')
@section('page_subtitle', 'Organize product catalog into hierarchical categories')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
            <i class="bi bi-folder-plus me-1"></i> Add Category
        </button>
    @endif
@endsection

@section('content')

@if(auth()->user()->isSuperAdmin())
<!-- Firm Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('categories.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-9">
            <select name="firm_id" class="form-select bg-light">
                <option value="">All Firms</option>
                @foreach($firms as $firm)
                    <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Filter</button>
            <a href="{{ route('categories.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>
@endif

<div class="row g-3">
    @forelse($categories as $category)
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card card-custom border-0 p-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-tags fs-4"></i>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                            <i class="bi bi-box-seam me-1"></i>{{ $category->products_count }} Products
                        </span>
                        @if(auth()->user()->isSuperAdmin())
                            <div class="mt-1"><span class="badge bg-light text-dark border me-1">{{ $category->firm->name ?? 'N/A' }}</span></div>
                        @endif
                    </div>
                </div>

                <h4 class="fw-bold font-outfit text-dark mb-1">{{ $category->name }}</h4>
                <p class="text-muted small mb-3">{{ $category->description ?? 'No description.' }}</p>

                <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small font-monospace">slug: {{ $category->slug }}</span>
                    @if(!auth()->user()->isSuperAdmin())
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
                    @endif
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
                            @if($errors->any() && session('open_modal') === 'editCategoryModal_' . $category->id)
                                <div class="alert alert-danger alert-dismissible fade show mb-3 py-2 px-3 small">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    @foreach($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                    <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @if($errors->has('name') && session('open_modal') === 'editCategoryModal_' . $category->id) is-invalid @endif" value="{{ old('name', $category->name) }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea name="description" class="form-control" rows="2">{{ old('description', $category->description) }}</textarea>
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
                    @if($errors->any() && session('open_modal') === 'createCategoryModal')
                        <div class="alert alert-danger alert-dismissible fade show mb-3 py-2 px-3 small">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                            <button type="button" class="btn-close py-2" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @if($errors->has('name') && session('open_modal') === 'createCategoryModal') is-invalid @endif" placeholder="e.g. Fire Extinguishers & Suppression" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Description</label>
                        <textarea name="description" class="form-control" rows="2" placeholder="e.g. Fire extinguishers, hydrants, alarm panels & safety gear">{{ old('description') }}</textarea>
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

@if(session('open_modal'))
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var modalEl = document.getElementById('{{ session('open_modal') }}');
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    </script>
@endif

@endsection
