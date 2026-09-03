@extends('layouts.app')

@section('title', 'Edit Product')
@section('page_title', 'Edit Product: ' . $product->name)
@section('page_subtitle', 'Update pricing, category, and alert threshold settings')

@section('header_actions')
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Catalog
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-9">
        <div class="card card-custom border-0 p-4">
            <form action="{{ route('products.update', $product) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row g-3">
                    <div class="col-12">
                        <label for="name" class="form-label fw-semibold text-dark">Product Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $product->name) }}" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="category_id" class="form-label fw-semibold text-dark mb-0">Category <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-medium" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="bi bi-plus-circle me-1"></i>Add New Category
                            </button>
                        </div>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="brand_id" class="form-label fw-semibold text-dark mb-0">Brand <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-medium" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                                <i class="bi bi-plus-circle me-1"></i>Add New Brand
                            </button>
                        </div>
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" required>
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="hsn_code" class="form-label fw-semibold text-dark">HSN Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('hsn_code') is-invalid @enderror" id="hsn_code" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}" required placeholder="e.g. 84241000">
                        @error('hsn_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="unit" class="form-label fw-semibold text-dark">Measurement Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('unit') is-invalid @enderror" id="unit" name="unit" required>
                            <option value="Pcs" {{ old('unit', $product->unit) == 'Pcs' ? 'selected' : '' }}>Pcs (Pieces)</option>
                            <option value="Box" {{ old('unit', $product->unit) == 'Box' ? 'selected' : '' }}>Box</option>
                            <option value="Kg" {{ old('unit', $product->unit) == 'Kg' ? 'selected' : '' }}>Kg (Kilograms)</option>
                            <option value="Ltr" {{ old('unit', $product->unit) == 'Ltr' ? 'selected' : '' }}>Ltr (Liters)</option>
                            <option value="Mtr" {{ old('unit', $product->unit) == 'Mtr' ? 'selected' : '' }}>Mtr (Meters)</option>
                            <option value="Set" {{ old('unit', $product->unit) == 'Set' ? 'selected' : '' }}>Set</option>
                        </select>
                        @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="cost_price" class="form-label fw-semibold text-dark">Purchase Cost (₹)</label>
                        <input type="number" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}">
                        @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="selling_price" class="form-label fw-semibold text-dark">Selling Retail Price (₹)</label>
                        <input type="number" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}">
                        @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="tax_percent" class="form-label fw-semibold text-dark">Tax Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('tax_percent') is-invalid @enderror" id="tax_percent" name="tax_percent" value="{{ old('tax_percent', $product->tax_percent) }}" required>
                        @error('tax_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="stock_quantity_display" class="form-label fw-semibold text-dark">Current Stock Level</label>
                        <input type="number" class="form-control bg-light" id="stock_quantity_display" value="{{ old('stock_quantity', $product->stock_quantity) }}" disabled>
                        <input type="hidden" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}">
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="alert_quantity" class="form-label fw-semibold text-dark">Low Stock Warning Limit <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('alert_quantity') is-invalid @enderror" id="alert_quantity" name="alert_quantity" value="{{ old('alert_quantity', $product->alert_quantity) }}" required>
                        @error('alert_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="status" class="form-label fw-semibold text-dark">Item Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label fw-semibold text-dark">Product Description & Specifications</label>
                        <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $product->description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('products.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-outfit fw-bold" id="addCategoryModalLabel">Add New Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ajaxAddCategoryForm">
                @csrf
                <div class="modal-body">
                    <div id="categoryModalAlert" class="alert alert-danger d-none mb-3"></div>
                    <div class="mb-3">
                        <label for="modal_category_name" class="form-label fw-semibold">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_category_name" name="name" required placeholder="e.g. Fire Extinguishers">
                    </div>
                    <div class="mb-3">
                        <label for="modal_category_description" class="form-label fw-semibold">Description (Optional)</label>
                        <textarea class="form-control" id="modal_category_description" name="description" rows="2" placeholder="Category details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveCategoryBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Brand Modal -->
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-outfit fw-bold" id="addBrandModalLabel">Add New Brand</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ajaxAddBrandForm">
                @csrf
                <div class="modal-body">
                    <div id="brandModalAlert" class="alert alert-danger d-none mb-3"></div>
                    <div class="mb-3">
                        <label for="modal_brand_name" class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="modal_brand_name" name="name" required placeholder="e.g. PyroShield">
                    </div>
                    <div class="mb-3">
                        <label for="modal_brand_description" class="form-label fw-semibold">Description (Optional)</label>
                        <textarea class="form-control" id="modal_brand_description" name="description" rows="2" placeholder="Brand details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="saveBrandBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const categoryForm = document.getElementById('ajaxAddCategoryForm');
    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const alertBox = document.getElementById('categoryModalAlert');
            const submitBtn = document.getElementById('saveCategoryBtn');
            alertBox.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

            const formData = new FormData(categoryForm);

            fetch("{{ route('categories.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add Category';

                if (res.status === 200 || res.status === 201) {
                    if (res.body.success) {
                        const select = document.getElementById('category_id');
                        const option = document.createElement('option');
                        option.value = res.body.category.id;
                        option.textContent = res.body.category.name;
                        option.selected = true;
                        select.appendChild(option);

                        categoryForm.reset();
                        const modalEl = document.getElementById('addCategoryModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        if (modal) {
                            modal.hide();
                        }
                    }
                } else {
                    let errMsg = res.body.message || 'Error adding category.';
                    if (res.body.errors && res.body.errors.name) {
                        errMsg = res.body.errors.name.join(' ');
                    }
                    alertBox.textContent = errMsg;
                    alertBox.classList.remove('d-none');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add Category';
                alertBox.textContent = 'Failed to save category. Please try again.';
                alertBox.classList.remove('d-none');
            });
        });
    }

    const brandForm = document.getElementById('ajaxAddBrandForm');
    if (brandForm) {
        brandForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const alertBox = document.getElementById('brandModalAlert');
            const submitBtn = document.getElementById('saveBrandBtn');
            alertBox.classList.add('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

            const formData = new FormData(brandForm);

            fetch("{{ route('brands.store') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(res => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add Brand';

                if (res.status === 200 || res.status === 201) {
                    if (res.body.success) {
                        const select = document.getElementById('brand_id');
                        const option = document.createElement('option');
                        option.value = res.body.brand.id;
                        option.textContent = res.body.brand.name;
                        option.selected = true;
                        select.appendChild(option);

                        brandForm.reset();
                        const modalEl = document.getElementById('addBrandModal');
                        const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        if (modal) {
                            modal.hide();
                        }
                    }
                } else {
                    let errMsg = res.body.message || 'Error adding brand.';
                    if (res.body.errors && res.body.errors.name) {
                        errMsg = res.body.errors.name.join(' ');
                    }
                    alertBox.textContent = errMsg;
                    alertBox.classList.remove('d-none');
                }
            })
            .catch(err => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Add Brand';
                alertBox.textContent = 'Failed to save brand. Please try again.';
                alertBox.classList.remove('d-none');
            });
        });
    }
});
</script>
@endsection
