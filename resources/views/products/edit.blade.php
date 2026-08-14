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
                        <label for="category_id" class="form-label fw-semibold text-dark">Category <span class="text-danger">*</span></label>
                        <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="brand_id" class="form-label fw-semibold text-dark">Brand <span class="text-danger">*</span></label>
                        <select class="form-select @error('brand_id') is-invalid @enderror" id="brand_id" name="brand_id" required>
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('brand_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="sku" class="form-label fw-semibold text-dark">SKU Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('sku') is-invalid @enderror" id="sku" name="sku" value="{{ old('sku', $product->sku) }}" required>
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="barcode" class="form-label fw-semibold text-dark">Barcode</label>
                        <input type="text" class="form-control @error('barcode') is-invalid @enderror" id="barcode" name="barcode" value="{{ old('barcode', $product->barcode) }}">
                        @error('barcode') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="hsn_code" class="form-label fw-semibold text-dark">HSN Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('hsn_code') is-invalid @enderror" id="hsn_code" name="hsn_code" value="{{ old('hsn_code', $product->hsn_code) }}" required placeholder="e.g. 84713010">
                        @error('hsn_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="unit" class="form-label fw-semibold text-dark">Measurement Unit <span class="text-danger">*</span></label>
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
                        <label for="cost_price" class="form-label fw-semibold text-dark">Purchase Cost ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" required>
                        @error('cost_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="selling_price" class="form-label fw-semibold text-dark">Selling Retail Price ($) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('selling_price') is-invalid @enderror" id="selling_price" name="selling_price" value="{{ old('selling_price', $product->selling_price) }}" required>
                        @error('selling_price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="tax_percent" class="form-label fw-semibold text-dark">Tax Rate (%) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control @error('tax_percent') is-invalid @enderror" id="tax_percent" name="tax_percent" value="{{ old('tax_percent', $product->tax_percent) }}" required>
                        @error('tax_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="stock_quantity" class="form-label fw-semibold text-dark">Current Stock Level <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('stock_quantity') is-invalid @enderror" id="stock_quantity" name="stock_quantity" value="{{ old('stock_quantity', $product->stock_quantity) }}" required>
                        @error('stock_quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
@endsection
