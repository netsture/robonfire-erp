@extends('layouts.app')

@section('title', 'Add Supplier')
@section('page_title', 'Create Supplier Account')
@section('page_subtitle', 'Register a new vendor or distributor profile')

@section('header_actions')
    <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Suppliers
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card card-custom border-0 p-4">
            <form action="{{ route('suppliers.store') }}" method="POST">
                @csrf
                
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <label for="company_name" class="form-label fw-semibold text-dark">Company Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" id="company_name" name="company_name" value="{{ old('company_name') }}" required placeholder="e.g. PyroShield Fire Equipment Ltd">
                        @error('company_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="gst_number" class="form-label fw-semibold text-dark">GST Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('gst_number') is-invalid @enderror" id="gst_number" name="gst_number" value="{{ old('gst_number') }}" required placeholder="e.g. 27AAAAA0000A1Z5">
                        @error('gst_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="address" class="form-label fw-semibold text-dark">Warehouse / Office Address <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="2" required placeholder="Fire Safety Supply Depot, Sector 4, Industrial Zone">{{ old('address') }}</textarea>
                        @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label fw-semibold text-dark">Phone Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required maxlength="10" minlength="10" pattern="[0-9]{10}" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="e.g. 9876543210">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label fw-semibold text-dark">Email Address</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="orders@pyroshieldfire.com">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>                    

                    <div class="col-12">
                        <label for="status" class="form-label fw-semibold text-dark">Status <span class="text-danger">*</span></label>
                        <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                            <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('suppliers.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Supplier</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
