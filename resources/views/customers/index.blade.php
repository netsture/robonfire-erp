@extends('layouts.app')

@section('title', 'Customers Directory')
@section('page_title', 'Customer Management')
@section('page_subtitle', 'Maintain client directory, credit terms, and financial ledgers')

@section('header_actions')
    <a href="{{ route('customers.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-person-plus-fill me-1"></i> Add Customer
    </a>
@endsection

@section('content')

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('customers.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-9">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search by customer name, company, email, phone..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Search</button>
            <a href="{{ route('customers.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Customers Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Customer / Company</th>
                    <th>Contact Details</th>
                    <th>Location</th>
                    <th>Credit Limit</th>
                    <th>Current Balance</th>
                    <th>Status</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <a href="{{ route('customers.show', $customer) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                        {{ $customer->name }}
                                    </a>
                                    @if($customer->company_name)
                                        <div class="text-muted small"><i class="bi bi-briefcase me-1"></i>{{ $customer->company_name }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small"><i class="bi bi-telephone text-muted me-1"></i>{{ $customer->phone }}</div>
                            @if($customer->email)
                                <div class="small text-muted"><i class="bi bi-envelope me-1"></i>{{ $customer->email }}</div>
                            @endif
                        </td>
                        <td class="small text-muted">
                            {{ $customer->city ?? 'N/A' }}
                        </td>
                        <td class="fw-semibold text-dark">
                            ${{ number_format($customer->credit_limit, 2) }}
                        </td>
                        <td>
                            @if($customer->current_balance > 0)
                                <span class="fw-bold text-danger">${{ number_format($customer->current_balance, 2) }} Due</span>
                            @else
                                <span class="fw-bold text-success">${{ number_format(abs($customer->current_balance), 2) }} Clear</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->status === 'active')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active</span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-light border" title="View Ledger">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete customer record?')">
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
                            <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                            No customer records found. Click "Add Customer" to create one.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($customers->hasPages())
        <div class="p-3 border-top">
            {{ $customers->links() }}
        </div>
    @endif
</div>

@endsection
