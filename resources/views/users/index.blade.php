@extends('layouts.app')

@section('title', 'User Accounts')
@section('page_title', 'User Management')
@section('page_subtitle', 'Manage system users, assign roles and configure access status')

@section('header_actions')
    <a href="{{ route('users.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
    @if(auth()->user()->isAdmin())
        <a href="{{ route('users.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-person-plus-fill me-1"></i> Add New User
        </a>
    @endif
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">User Accounts & Access Management Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- Print Summary Single Line -->
<div class="d-none d-print-block mb-4 p-3 border rounded text-center">
    <div class="row text-center fw-bold font-outfit fs-6">
        <div class="col-4">
            Total User: {{ number_format($totalUsers) }}
        </div>
        <div class="col-4 border-start border-end">
            Active User: {{ number_format($activeUsers) }}
        </div>
        <div class="col-4">
            Inactive User: {{ number_format($inactiveUsers) }}
        </div>
    </div>
</div>

<!-- Summary Statistics Cards -->
<div class="row g-3 mb-4 no-print">
    <!-- Total User Card -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total User</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">{{ number_format($totalUsers) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Active User Card -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Active User</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">{{ number_format($activeUsers) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Inactive User Card -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Inactive User</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">{{ number_format($inactiveUsers) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-person-x-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('users.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-4' : 'col-md-5' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search User, Firm, Email, Phone Number..." value="{{ request('search') }}">
            </div>
        </div>
        @if(auth()->user()->isSuperAdmin())
        <div class="col-12 col-md-3">
            <select name="firm_id" class="form-select bg-light">
                <option value="">All Firms</option>
                @foreach($firms as $firm)
                    <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-3' : 'col-md-4' }}">
            <select name="role" class="form-select bg-light">
                <option value="">All Roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-2 d-flex gap-2">
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
                    <th class="ps-4">
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            User
                            @if(request('sort_by') === 'name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'firm_name', 'sort_order' => request('sort_by') === 'firm_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Firm / Organization
                            @if(request('sort_by') === 'firm_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'email', 'sort_order' => request('sort_by') === 'email' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Contact Info
                            @if(request('sort_by') === 'email')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'role', 'sort_order' => request('sort_by') === 'role' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Assigned Role
                            @if(request('sort_by') === 'role')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Status
                            @if(request('sort_by') === 'status')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('users.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Joined Date
                            @if(request('sort_by') === 'created_at')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end pe-4 no-print">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            @php
                                $rStr = strtolower(trim($user->role ?? ($user->roles->first()->name ?? 'User')));
                                $avatarStyle = 'background-color: rgba(187, 59, 159, 0.15); color: #bb3b9f;';
                                if (in_array($rStr, ['superadmin', 'super admin', 'super-admin'])) {
                                    $avatarStyle = 'background-color: rgba(220, 53, 69, 0.15); color: #dc3545;';
                                } elseif (in_array($rStr, ['admin', 'administrator'])) {
                                    $avatarStyle = 'background-color: rgba(79, 70, 229, 0.15); color: #4f46e5;';
                                } elseif (in_array($rStr, ['manager', 'supervisor'])) {
                                    $avatarStyle = 'background-color: rgba(255, 193, 7, 0.2); color: #856404;';
                                } elseif (in_array($rStr, ['sales', 'staff', 'biller'])) {
                                    $avatarStyle = 'background-color: rgba(13, 202, 240, 0.15); color: #0dcaf0;';
                                }
                            @endphp
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-user" style="width: 40px; height: 40px; font-size: 1rem;">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $user->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($user->firm)
                                <div class="fw-semibold text-dark"><i class="bi bi-building text-primary me-1"></i>{{ $user->firm->name }}</div>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">
                                    Global / Superadmin
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $user->email }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $user->phone ?? 'N/A' }}</div>
                        </td>
                        <td>
                            @if(in_array($rStr, ['superadmin', 'super admin', 'super-admin']))
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-semibold">
                                    <i class="bi bi-shield-lock-fill me-1"></i>{{ ucfirst($user->role ?? 'Superadmin') }}
                                </span>
                            @elseif(in_array($rStr, ['admin', 'administrator']))
                                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #4f46e5;">
                                    <i class="bi bi-shield-check me-1"></i>{{ ucfirst($user->role ?? 'Admin') }}
                                </span>
                            @elseif(in_array($rStr, ['manager', 'supervisor']))
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-semibold">
                                    <i class="bi bi-person-badge-fill me-1"></i>{{ ucfirst($user->role ?? 'Manager') }}
                                </span>
                            @elseif(in_array($rStr, ['sales', 'staff', 'biller']))
                                <span class="badge bg-info text-white rounded-pill px-3 py-1 fw-semibold">
                                    <i class="bi bi-person-workspace me-1"></i>{{ ucfirst($user->role ?? 'Sales') }}
                                </span>
                            @elseif(in_array($rStr, ['user', 'standard user', 'customer']))
                                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #bb3b9f;">
                                    <i class="bi bi-person-check-fill me-1"></i>{{ ucfirst($user->role ?? 'User') }}
                                </span>
                            @else
                                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #bb3b9f;">
                                    <i class="bi bi-person-fill me-1"></i>{{ ucfirst($user->role ?? 'User') }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if(auth()->user()->isAdmin())
                                <form action="{{ route('users.toggle-status', $user) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm border-0 p-0" title="Click to toggle status">
                                        @if($user->status === 'active')
                                            <span class="badge bg-success text-white rounded-pill px-3 py-1">Active</span>
                                        @else
                                            <span class="badge bg-danger text-white rounded-pill px-3 py-1">Inactive</span>
                                        @endif
                                    </button>
                                </form>
                            @else
                                @if($user->status === 'active')
                                    <span class="badge bg-success text-white rounded-pill px-3 py-1">Active</span>
                                @else
                                    <span class="badge bg-danger text-white rounded-pill px-3 py-1">Inactive</span>
                                @endif
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $user->created_at->format('d-m-Y') }}
                        </td>
                        <td class="text-end pe-4 no-print">
                            @if(auth()->user()->isAdmin())
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
                            @else
                                <span class="text-muted small">View Only</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No users found. Try searching with different keywords.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($users->hasPages())
        <div class="p-3 border-top no-print">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
