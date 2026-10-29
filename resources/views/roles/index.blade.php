@extends('layouts.app')

@section('title', 'Roles & Permissions')
@section('page_title', 'Roles & Access Control')
@section('page_subtitle', 'Configure system roles and fine-grained module permissions')

@section('header_actions')
    @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
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
                    <div class="rounded-circle dark-symbol-avatar dark-symbol-role" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-1">
                        <i class="bi bi-person me-1"></i>{{ $role->users_count }} Assigned Users
                    </span>
                </div>

                <h4 class="fw-bold font-outfit text-dark mb-1">{{ ucfirst($role->name) }}</h4>
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
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-light border" data-bs-toggle="modal" data-bs-target="#viewRoleModal_{{ $role->id }}" title="View Permissions">
                            <i class="bi bi-eye"></i>
                        </button>
                        @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                            <a href="{{ route('roles.edit', $role) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 ms-1">
                                <i class="bi bi-pencil me-1"></i> Edit Permissions
                            </a>
                            @if(!in_array($role->slug, ['admin', 'superadmin']))
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline ms-1" onsubmit="return confirm('Are you sure you want to delete this role?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2" title="Delete Role">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Modals for View Role Permissions --}}
@foreach($roles as $role)
    <!-- View Role Permissions Modal -->
    <div class="modal fade" id="viewRoleModal_{{ $role->id }}" tabindex="-1" aria-labelledby="viewRoleModalLabel_{{ $role->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header border-bottom bg-light px-4 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-primary fs-5"></i>
                        <h5 class="modal-title fw-bold font-outfit text-dark mb-0" id="viewRoleModalLabel_{{ $role->id }}">
                            Role Details & Permissions: {{ ucfirst($role->name) }}
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-1 font-outfit">{{ ucfirst($role->name) }}</h5>
                            <p class="text-muted small mb-0">{{ $role->description ?? 'No description provided.' }}</p>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1 mb-1 font-monospace d-block">
                                slug: {{ $role->slug }}
                            </span>
                            <span class="badge bg-primary text-white rounded-pill px-3 py-1">
                                {{ $role->permissions->count() }} Permissions Granted
                            </span>
                        </div>
                    </div>

                    @if($role->permissions->count() > 0)
                        <h6 class="fw-bold font-outfit text-dark mb-3">Module Permissions</h6>
                        <div class="row g-3">
                            @foreach($role->permissions->groupBy('module') as $module => $modulePermissions)
                                <div class="col-12 col-md-6">
                                    <div class="card border rounded-3 p-3 h-100 bg-white">
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                            <span class="fw-bold text-capitalize text-dark font-outfit small">
                                                <i class="bi bi-folder2-open text-primary me-1"></i>{{ str_replace('_', ' ', $module) }}
                                            </span>
                                            <span class="badge bg-light text-dark border rounded-pill">{{ $modulePermissions->count() }}</span>
                                        </div>
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach($modulePermissions as $perm)
                                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small" title="{{ $perm->description ?? $perm->slug }}">
                                                    <i class="bi bi-check2 text-success me-1"></i>{{ $perm->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-shield-x fs-1 d-block mb-2 text-secondary"></i>
                            No specific permissions assigned to this role.
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-top bg-light px-4 py-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
