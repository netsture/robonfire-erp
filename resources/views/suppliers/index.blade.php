@extends('layouts.app')

@section('title', 'Suppliers Directory')
@section('page_title', 'Supplier Management')
@section('page_subtitle', 'Manage vendors, manufacturers, and procurement ledgers')

@section('header_actions')
    <a href="{{ route('suppliers.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
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
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Company Name, GST Number, Email, Phone Number, Address..." value="{{ request('search') }}">
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
                    <th class="ps-4">
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'company_name', 'sort_order' => request('sort_by') === 'company_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Company Name
                            @if(request('sort_by') === 'company_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>
                            <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'firm_name', 'sort_order' => request('sort_by') === 'firm_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                                Firm
                                @if(request('sort_by') === 'firm_name')
                                    <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                                @endif
                            </a>
                        </th>
                    @endif
                    <th>
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'gst_number', 'sort_order' => request('sort_by') === 'gst_number' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            GST Number
                            @if(request('sort_by') === 'gst_number')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'address', 'sort_order' => request('sort_by') === 'address' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Warehouse / Office Address
                            @if(request('sort_by') === 'address')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'email', 'sort_order' => request('sort_by') === 'email' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Contact Info
                            @if(request('sort_by') === 'email')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'status', 'sort_order' => request('sort_by') === 'status' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Status
                            @if(request('sort_by') === 'status')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('suppliers.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
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
                @forelse($suppliers as $supplier)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-supplier" style="width: 40px; height: 40px;">
                                    <i class="bi bi-truck fs-5"></i>
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
                        <td class="small font-monospace">
                            {{ $supplier->gst_number ?? 'N/A' }}
                        </td>
                        <td class="small text-muted">
                            @if($supplier->address)
                                <div class="fw-medium text-dark"><i class="bi bi-geo-alt text-muted me-1"></i>{{ $supplier->address }}</div>
                            @else
                                N/A
                            @endif
                        </td>
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
                        <td>
                            @if($supplier->status === 'active')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-danger text-white rounded-pill px-3 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="small font-monospace">
                            {{ $supplier->created_at ? $supplier->created_at->format('d-m-Y') : 'N/A' }}
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
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 8 : 7 }}" class="text-center py-5 text-muted">
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
