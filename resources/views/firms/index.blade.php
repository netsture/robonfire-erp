@extends('layouts.app')

@section('title', 'Firms Management')
@section('page_title', 'Firms Directory')
@section('page_subtitle', 'Manage registered firms, organizations, and platform subscriptions')

@section('header_actions')
    <a href="{{ route('firms.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
    <a href="{{ route('firms.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-building-add me-1"></i> Add New Firm
    </a>
@endsection

@section('content')

<!-- Search Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('firms.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Firm Name, Email, Phone, Address..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Search</button>
            <a href="{{ route('firms.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Firms Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'name', 'sort_order' => request('sort_by') === 'name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Firm Name
                            @if(request('sort_by') === 'name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'email', 'sort_order' => request('sort_by') === 'email' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Contact Info
                            @if(request('sort_by') === 'email')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'address', 'sort_order' => request('sort_by') === 'address' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Address
                            @if(request('sort_by') === 'address')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'users_count', 'sort_order' => request('sort_by') === 'users_count' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Users
                            @if(request('sort_by') === 'users_count')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'products_count', 'sort_order' => request('sort_by') === 'products_count' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Products
                            @if(request('sort_by') === 'products_count')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Status
                            @if(request('sort_by') === 'status')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('firms.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Created Date
                            @if(request('sort_by') === 'created_at')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($firms as $firm)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-firm" style="width: 42px; height: 42px;">
                                    <i class="bi bi-building fs-5"></i>
                                </div>
                                <div>
                                    <a href="{{ route('firms.show', $firm) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                        {{ $firm->name }}
                                    </a>
                                    <div class="text-muted small font-monospace">ID: #FRM-{{ $firm->id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-envelope text-muted me-1"></i>{{ $firm->email ?? 'N/A' }}</div>
                            <div class="small text-muted"><i class="bi bi-telephone me-1"></i>{{ $firm->phone ?? 'N/A' }}</div>
                        </td>
                        <td class="small text-muted">
                            {{ $firm->address ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $firm->users_count }} Users</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $firm->products_count }} Items</span>
                        </td>
                        <td>
                            @if($firm->status === 'active')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="small text-muted font-monospace">{{ $firm->created_at ? $firm->created_at->format('d-m-Y') : 'N/A' }}</td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('firms.show', $firm) }}" class="btn btn-sm btn-light border" title="View Profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('firms.edit', $firm) }}" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('firms.destroy', $firm) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete firm and all associated records?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-building fs-1 d-block mb-2 text-secondary"></i>
                            No firms found matching your search.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($firms->hasPages())
        <div class="p-3 border-top">
            {{ $firms->withQueryString()->links() }}
        </div>
    @endif
</div>

@endsection
