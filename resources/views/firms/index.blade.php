@extends('layouts.app')

@section('title', 'Firms Management')
@section('page_title', 'Firms Directory')
@section('page_subtitle', 'Manage registered firms, organizations, and platform subscriptions')

@section('header_actions')
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
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search by firm name, email, phone..." value="{{ request('search') }}">
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
                    <th class="ps-4">Firm Name</th>
                    <th>Contact Info</th>
                    <th>Address</th>
                    <th>Users</th>
                    <th>Products</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($firms as $firm)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
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
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Inactive</span>
                            @endif
                        </td>
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-building fs-1 d-block mb-2 text-secondary"></i>
                            No firms found. Click "Add New Firm" to register a firm.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($firms->hasPages())
        <div class="p-3 border-top">
            {{ $firms->links() }}
        </div>
    @endif
</div>

@endsection
