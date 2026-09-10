@extends('layouts.app')

@section('title', 'Returnable Entry')
@section('page_title', 'Returnable Entry')
@section('page_subtitle', 'Manage material returns, customer return receipts, and stock restock entries')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('returnable.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-box-arrow-in-left me-1"></i> New Returnable Entry
        </a>
    @endif
@endsection

@section('content')

<!-- Search Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('returnable.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-6' : 'col-md-9' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Return No, Project Name, Customer Name, Phone Number, Return Reason, Return Date..." value="{{ request('search') }}">
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
            <a href="{{ route('returnable.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Returnable Entries Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Return No / Project Name</th>
                    <th>Customer Name / Number</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Return Date</th>
                    <th>Return Reason</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-customer" style="width: 40px; height: 40px;">
                                    <i class="bi bi-box-arrow-in-left fs-5"></i>
                                </div>
                                <div>
                                    <div class="fw-bold font-monospace text-dark">#{{ $ret->return_number }}</div>
                                    @if($ret->project_name)
                                        <div class="small text-muted"><i class="bi bi-folder2-open me-1"></i>{{ $ret->project_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle dark-symbol-avatar dark-symbol-customer" style="width: 34px; height: 34px; font-size: 0.85rem;">
                                    <i class="bi bi-person-circle"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">{{ $ret->customer->company_name ?? 'N/A' }}</div>
                                    <div class="small text-muted">{{ $ret->customer->phone ?? '' }}</div>
                                </div>
                            </div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $ret->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-dark font-monospace">{{ \Carbon\Carbon::parse($ret->return_date)->format('d-m-Y') }}</td>
                        <td>{{ $ret->return_reason }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($ret->grand_total, 2) }}</td>
                        <td>
                            <span class="badge bg-success text-white rounded-pill px-3 py-1 text-uppercase">RETURNED</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                @if(auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin())
                                    <a href="{{ route('returnable.edit', $ret) }}" class="btn btn-sm btn-light border" title="Edit Return Entry">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                @endif
                                <a href="{{ route('returnable.show', $ret) }}" class="btn btn-sm btn-light border" target="_blank" title="View Entry">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('returnable.challan', $ret) }}" class="btn btn-sm btn-light border text-info" target="_blank" title="Return Challan View">
                                    <i class="bi bi-truck"></i>
                                </a>
                                <!--<a href="{{ route('returnable.print', $ret) }}" class="btn btn-sm btn-light border text-primary" target="_blank" title="Print Return Receipt / Slip">
                                    <i class="bi bi-printer"></i>
                                </a>-->
                                @if(!auth()->user()->isSuperAdmin())
                                    <form action="{{ route('returnable.destroy', $ret) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete returnable material entry and revert stock?')">
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
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-box-arrow-in-left fs-1 d-block mb-2 text-secondary"></i>
                            No returnable material entries recorded. Click "New Return Material Entry" to add one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($returns->hasPages())
        <div class="p-3 border-top">
            {{ $returns->links() }}
        </div>
    @endif
</div>

@endsection
