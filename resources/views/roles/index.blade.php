@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page_title', 'Roles & Access Control')
@section('page_subtitle', 'Configure system roles and fine-grained module permissions')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('roles.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-shield-plus me-1"></i> Create New Role
        </a>
    @endif
@endsection

@section('content')

<div class="row g-3">
    @foreach($roles as $role)
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card card-custom border-0 h-100 p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="rounded-circle bg-indigo-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; background: #e0e7ff;">
                        <i class="bi bi-shield-lock fs-4 text-primary"></i>
                    </div>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                        <i class="bi bi-person me-1"></i>{{ $role->users_count }} Assigned Users
                    </span>
                </div>

                <h4 class="fw-bold font-outfit text-dark mb-1">{{ $role->name }}</h4>
                <p class="text-muted small mb-3">{{ $role->description ?? 'No description provided.' }}</p>

                <div class="bg-light rounded-3 p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-dark">Permissions Granted</span>
                        <span class="badge bg-primary rounded-pill">{{ $role->permissions_count }}</span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, ($role->permissions_count / 20) * 100) }}%"></div>
                    </div>
                </div>

                <div class="mt-auto d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-muted small font-monospace">slug: {{ $role->slug }}</span>
                    @if(!auth()->user()->isSuperAdmin())
                        <div class="btn-group">
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                <i class="bi bi-pencil me-1"></i> Edit Permissions
                            </a>
                            @if(!in_array($role->slug, ['admin', 'superadmin']))
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
</div>

@endsection
