@extends('layouts.app')

@section('title', 'Edit Firm')
@section('page_title', 'Edit Firm Profile')
@section('page_subtitle', 'Update firm details and subscription status')

@section('header_actions')
    <a href="{{ route('firms.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit">
        <i class="bi bi-arrow-left me-1"></i> Back to Firms
    </a>
@endsection

@section('content')

<div class="card card-custom border-0 max-w-700 mx-auto">
    <div class="card-body p-4 p-md-5">
        <form action="{{ route('firms.update', $firm) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-12">
                    <label for="name" class="form-label fw-semibold text-dark">Firm Name <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $firm->name) }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="address" class="form-label fw-semibold text-dark">Office / Headquarter Address <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('address') is-invalid @enderror" id="address" name="address" rows="3" required>{{ old('address', $firm->address) }}</textarea>
                    @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="email" class="form-label fw-semibold text-dark">Firm Email/Login Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $firm->email) }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="phone" class="form-label fw-semibold text-dark">Firm Phone Number <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $firm->phone) }}" required maxlength="10" minlength="10" pattern="[0-9]{10}" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="e.g. 9876543210">
                    @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="password" class="form-label fw-semibold text-dark">Password</label>
                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Leave blank to keep current">
                    <div class="form-text small text-muted">Leave blank to keep current password</div>
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="status" class="form-label fw-semibold text-dark">Status <span class="text-danger">*</span></label>
                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                        <option value="active" {{ old('status', $firm->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $firm->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <hr class="my-4 text-muted">

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('firms.index') }}" class="btn btn-light border rounded-pill px-4">Cancel</a>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save Changes</button>
            </div>
        </form>
    </div>
</div>

@endsection
