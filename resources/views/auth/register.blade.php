@extends('layouts.guest')

@section('title', 'Create Account')

@section('content')
<form action="{{ route('register.submit') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="name" class="form-label text-light small fw-medium">Full Name</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person"></i></span>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="John Doe">
        </div>
        @error('name')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label text-light small fw-medium">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required placeholder="name@company.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label text-light small fw-medium">Phone Number (Optional)</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
            <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+1 234 567 890">
        </div>
        @error('phone')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label text-light small fw-medium">Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="Minimum 8 characters">
        </div>
        @error('password')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label text-light small fw-medium">Confirm Password</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Repeat password">
        </div>
    </div>

    <button type="submit" class="btn btn-gradient w-100 mb-3">
        <i class="bi bi-person-plus-fill me-2"></i>Create New Account
    </button>
</form>

<div class="text-center">
    <span class="text-muted small">Already registered?</span>
    <a href="{{ route('login') }}" class="text-indigo text-decoration-none fw-semibold small ms-1 text-primary">Sign In</a>
</div>
@endsection
