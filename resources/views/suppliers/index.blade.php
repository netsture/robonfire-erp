@extends('layouts.app')

@section('title', 'Suppliers Directory')
@section('page_title', 'Supplier Management')
@section('page_subtitle', 'Manage vendors, manufacturers, and procurement ledgers')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-truck me-1"></i> Add New Supplier
        </a>
    @endif
@endsection

@section('content')

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('suppliers.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-6' : 'col-md-9' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search company name, GST number, email..." value="{{ request('search') }}">
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
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Search</button>
            <a href="{{ route('suppliers.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Suppliers Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Company Name</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Contact Info</th>
                    <th>Warehouse / Office Address</th>
                    <th>GST Number</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($suppliers as $supplier)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-truck"></i>
                                </div>
                                <div>
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                        {{ $supplier->company_name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $supplier->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td>
                            @if($supplier->phone)
                                <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $supplier->phone }}</div>
                            @endif
                            @if($supplier->email)
                                <div class="small text-muted"><i class="bi bi-envelope me-1"></i>{{ $supplier->email }}</div>
                            @endif
                            @if(!$supplier->phone && !$supplier->email)
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td class="small text-muted">
                            @if($supplier->address)
                                <div class="fw-medium text-dark"><i class="bi bi-geo-alt text-muted me-1"></i>{{ $supplier->address }}</div>
                            @else
                                N/A
                            @endif
                        </td>
                        <td class="small font-monospace">
                            {{ $supplier->gst_number ?? 'N/A' }}
                        </td>
                        <td>
                            @if($supplier->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-sm btn-light border" title="View Profile">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!auth()->user()->isSuperAdmin())
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-light border" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete supplier record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger" title="Delete">
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
                            <i class="bi bi-truck fs-1 d-block mb-2 text-secondary"></i>
                            No supplier records found. Click "Add New Supplier" to add a vendor.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($suppliers->hasPages())
        <div class="p-3 border-top">
            {{ $suppliers->links() }}
        </div>
    @endif
</div>

@endsection
