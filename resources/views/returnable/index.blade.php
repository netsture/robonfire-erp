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
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search return #, project, reason for return, or customer name..." value="{{ request('search') }}">
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

<!-- Returnable Materials Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Challan No #</th>
                    <th>Customer Name</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Return Date</th>
                    <th>Grand Total</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $ret)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold font-monospace text-dark">#{{ $ret->return_number }}</div>
                            @if($ret->project_name)
                                <div class="small text-muted"><i class="bi bi-folder2-open me-1"></i>{{ $ret->project_name }}</div>
                            @endif
                            @if($ret->return_reason)
                                <div class="small text-muted"><i class="bi bi-info-circle me-1"></i>{{ $ret->return_reason }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $ret->customer->company_name ?? 'N/A' }}</div>
                            <div class="small text-muted">{{ $ret->customer->phone ?? '' }}</div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $ret->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-muted">{{ $ret->return_date }}</td>
                        <td class="fw-bold text-dark">₹{{ number_format($ret->grand_total, 2) }}</td>
                        <td>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 text-uppercase">RETURNED</span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
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
