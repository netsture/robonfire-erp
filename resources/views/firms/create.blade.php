@extends('layouts.app')

@section('title', 'Add New Firm')
@section('page_title', 'Register Firm')
@section('page_subtitle', 'Create a new isolated organization on the ERP platform')

@section('header_actions')
    <a href="{{ route('firms.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit">
        <i class="bi bi-arrow-left me-1"></i> Back to Firms
    </a>
@endsection

@section('content')

<div class="card card-custom border-0 max-w-700 mx-auto">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('firms.store') }}" method="POST">
            @csrf

            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label fw-semibold text-dark">Firm Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Robonfire Safety Systems Pvt Ltd">
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="email" class="form-label fw-semibold text-dark">Contact Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="contact@robonfire.com">
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="phone" class="form-label fw-semibold text-dark">Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" required minlength="10" maxlength="10" placeholder="e.g. 9876543210">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold text-dark">Office / Headquarter Address</label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" placeholder="Robonfire HQ, Plot 10 Fire Safety Zone, City, Pin 400001">{{ old('address') }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="status" class="form-label fw-semibold text-dark">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Firm Admin Account Details -->
            <div class="border-top pt-4 mt-4">
                <h5 class="fw-bold font-outfit text-dark mb-1"><i class="bi bi-person-badge me-2 text-primary"></i>Firm Admin Account</h5>
                <p class="text-muted small mb-3">Set up the initial Administrator user for this firm firm.</p>

                <div class="row g-3">
                    <div class="col-12">
                        <label for="admin_name" class="form-label fw-semibold text-dark">Admin Full Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('admin_name') is-invalid @enderror" id="admin_name" name="admin_name" value="{{ old('admin_name') }}" required placeholder="e.g. Chief Fire Officer John Doe">
                        @error('admin_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="admin_email" class="form-label fw-semibold text-dark">Username / Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('admin_email') is-invalid @enderror" id="admin_email" name="admin_email" value="{{ old('admin_email') }}" required placeholder="fireadmin@robonfire.com">
                        @error('admin_email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="admin_password" class="form-label fw-semibold text-dark">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('admin_password') is-invalid @enderror" id="admin_password" name="admin_password" required placeholder="Minimum 6 characters">
                        @error('admin_password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>

            <hr class="my-4 text-muted">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('firms.index') }}" class="btn btn-light border rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Create Firm</button>
            </div>
        </form>
    </div>
</div>

@endsection
