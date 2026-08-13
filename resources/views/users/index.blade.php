@extends('layouts.app')

@section('title', 'User Accounts')
@section('page_title', 'User Management')
@section('page_subtitle', 'Manage system users, assign roles and configure access status')

@section('header_actions')
    <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-person-plus-fill me-1"></i> Add New User
    </a>
@endsection

@section('content')

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-5">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search by name, email, phone..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-4">
            <select name="role" class="form-select bg-light">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ $role->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Filter</button>
            <a href="{{ route('users.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">User</th>
                    <th>Contact Info</th>
                    <th>Assigned Role</th>
                    <th>Status</th>
                    <th>Joined Date</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                    <div class="text-muted small">ID: #USR-{{ str_pad($user->id, 4, '0', STR_PAD_LEFT) }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $user->email }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $user->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            <span class="badge bg-indigo-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1">
                                <i class="bi bi-shield-check me-1"></i>{{ $user->role ?? 'User' }}
                            </span>
                        </td>
                        <td>
                            <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm border-0 p-0" title="Click to toggle status">
                                    @if($user->status === 'active')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">Inactive</span>
                                    @endif
                                </button>
                            </form>
                        </td>
                        <td class="small text-muted">
                            {{ $user->created_at->format('M d, Y') }}
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-light border" title="Edit User">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete User">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No users found. Try searching with different keywords.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-3 border-top">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
