@extends('layouts.guest')

@section('title', 'Sign In')

@section('content')
<form action="{{ route('login.submit') }}" method="POST">
    @csrf
    <div class="mb-3">
        <label for="email" class="form-label text-light small fw-medium">Email Address</label>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
            <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'admin@erp.com') }}" required autofocus placeholder="name@company.com">
        </div>
        @error('email')
            <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label text-light small fw-medium mb-0">Password</label>
        </div>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-lock"></i></span>
            <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required placeholder="••••••••">
        </div>
        @error('password')
            <div class="text-danger small mt-1"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
        @enderror
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember">
        <label class="form-check-label text-muted small" for="remember">
            Keep me signed in
        </label>
    </div>

    <button type="submit" class="btn btn-gradient w-100 mb-3">
        <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Workspace
    </button>
</form>

<div class="demo-box mb-3 text-center text-light">
    <div class="fw-semibold mb-1"><i class="bi bi-key-fill text-warning me-1"></i> Quick Seeder Credentials</div>
    <div class="small text-muted">Email: <span class="text-info font-monospace">admin@erp.com</span></div>
    <div class="small text-muted">Password: <span class="text-info font-monospace">password</span></div>
</div>

<div class="text-center">
    <span class="text-muted small">Don't have an account?</span>
    <a href="{{ route('register') }}" class="text-indigo text-decoration-none fw-semibold small ms-1 text-primary">Create Account</a>
</div>
@endsection
