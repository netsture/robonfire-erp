@extends('layouts.app')

@section('title', 'Purchase, Sale & Return Audit Log Report')
@section('page_title', 'Transaction Audit Log Report')
@section('page_subtitle', 'Comprehensive audit trail of purchases, sales, and returnable entries (created, edited, and deleted) with user attribution')

@section('header_actions')
    <a href="{{ route('reports.transaction-logs.print', request()->query()) }}" target="_blank" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Purchases, Sales & Returns Activity Audit Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl">
        <div class="card card-custom border-0 p-3 text-white h-100" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Audit Records</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalLogs) }}</h3>
            <span class="small mt-1 opacity-75">Valuation: ₹{{ number_format($totalAmount, 2) }}</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card card-custom border-0 p-3 text-white h-100" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Purchases</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalPurchases) }} Orders</h3>
            <span class="small mt-1 opacity-75">Procurement logs</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card card-custom border-0 p-3 text-white h-100" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Sales Orders</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalSales) }} Challans</h3>
            <span class="small mt-1 opacity-75">Revenue transactions</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card card-custom border-0 p-3 text-white h-100" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Returns</span>
            <h3 class="fw-bold font-outfit mt-2 mb-0" style="color: #ffffff !important;">{{ number_format($totalReturns) }} Entries</h3>
            <span class="small mt-1 opacity-75">Returnable items</span>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl">
        <div class="card card-custom border-0 p-3 text-white h-100" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);">
            <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Actions Activity</span>
            <div class="mt-2 d-flex flex-wrap gap-1 small">
                <span class="badge bg-success text-white px-2 py-1">+{{ $totalCreated }} Added</span>
                <span class="badge bg-info text-white px-2 py-1">{{ $totalUpdated }} Edits</span>
                <span class="badge bg-danger text-white px-2 py-1">-{{ $totalDeleted }} Del</span>
            </div>
            <span class="small mt-1 opacity-75">Breakdown by action</span>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.transaction-logs') }}" class="row g-2 align-items-end">
        @if(auth()->user()->isSuperAdmin() && count($firms) > 0)
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Firm</label>
                <select name="firm_id" class="form-select form-select-sm">
                    <option value="">All Firms</option>
                    @foreach($firms as $firm)
                        <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Transaction Module</label>
            <select name="module" class="form-select form-select-sm">
                <option value="">All Modules</option>
                <option value="purchase" {{ request('module') === 'purchase' ? 'selected' : '' }}>Purchases</option>
                <option value="sale" {{ request('module') === 'sale' ? 'selected' : '' }}>Sales Orders</option>
                <option value="returnable" {{ request('module') === 'returnable' ? 'selected' : '' }}>Returnable Materials</option>
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Action Type</label>
            <select name="action" class="form-select form-select-sm">
                <option value="">All Actions</option>
                <option value="create" {{ request('action') === 'create' ? 'selected' : '' }}>Entry (Created)</option>
                <option value="update" {{ request('action') === 'update' ? 'selected' : '' }}>Updated (Edited)</option>
                <option value="delete" {{ request('action') === 'delete' ? 'selected' : '' }}>Deleted (Cancelled)</option>
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Staff / User</label>
            <select name="user_id" class="form-select form-select-sm">
                <option value="">All Users</option>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ ucfirst($u->role ?? 'User') }})</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-4">
            <label class="form-label small fw-semibold text-muted mb-1">Search Ref # / Project / Party / Product</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Start Date (dd-mm-yyyy)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                <input type="text" name="start_date" class="form-control form-control-sm datepicker-ddmmyyyy" placeholder="dd-mm-yyyy" value="{{ request('start_date') ? (\Carbon\Carbon::canBeCreatedFromFormat(request('start_date'), 'd-m-Y') ? request('start_date') : \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y')) : '' }}" autocomplete="off">
            </div>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">End Date (dd-mm-yyyy)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                <input type="text" name="end_date" class="form-control form-control-sm datepicker-ddmmyyyy" placeholder="dd-mm-yyyy" value="{{ request('end_date') ? (\Carbon\Carbon::canBeCreatedFromFormat(request('end_date'), 'd-m-Y') ? request('end_date') : \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y')) : '' }}" autocomplete="off">
            </div>
        </div>

        <div class="col-12 d-flex justify-content-end gap-2 mt-3">
            <button type="submit" class="btn btn-dark btn-sm px-4 rounded-3"><i class="bi bi-filter me-1"></i> Apply Filters</button>
            <a href="{{ route('reports.transaction-logs') }}" class="btn btn-light btn-sm border px-3 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Transaction Audit Logs Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4" style="width: 50px;">#</th>
                    <th>Date & Time</th>
                    <th>Module</th>
                    <th>Action</th>
                    <th>Invoice / Slip / Project</th>
                    <th>Party & Total Amount</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>Performed By (User)</th>
                    <th>Audit Summary</th>
                    <th class="text-end pe-4 no-print">Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $index => $log)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $logs->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-semibold text-dark font-monospace small">
                                {{ $log->created_at ? $log->created_at->format('d-m-Y, h:i A') : 'N/A' }}
                            </div>
                            <div class="text-muted small">
                                {{ $log->created_at ? $log->created_at->diffForHumans() : '' }}
                            </div>
                        </td>
                        <td>
                            @if($log->module === 'purchase')
                                <span class="badge bg-primary text-white rounded-pill px-3 py-1">
                                    <i class="bi bi-cart-plus-fill me-1"></i>Purchase
                                </span>
                            @elseif($log->module === 'sale')
                                <span class="badge bg-success text-white rounded-pill px-3 py-1">
                                    <i class="bi bi-bag-check-fill me-1"></i>Sales Order
                                </span>
                            @elseif($log->module === 'returnable')
                                <span class="badge bg-warning text-dark rounded-pill px-3 py-1">
                                    <i class="bi bi-box-arrow-in-left me-1"></i>Returnable
                                </span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-3 py-1">
                                    {{ ucfirst($log->module) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($log->action === 'create')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    <i class="bi bi-plus-circle-fill me-1"></i>Entry
                                </span>
                            @elseif($log->action === 'update')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    <i class="bi bi-pencil-square me-1"></i>Updated
                                </span>
                            @elseif($log->action === 'delete')
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                    <i class="bi bi-trash-fill me-1"></i>Deleted
                                </span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1">
                                    {{ ucfirst($log->action) }}
                                </span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-bold font-monospace text-dark">
                                #{{ $log->reference_no }}
                            </div>
                            @if($log->project_name)
                                <div class="mt-0.5">
                                    <span class="badge text-white rounded-pill px-2 py-0.5" style="background-color: #4f46e5; font-size: 0.72rem;">
                                        <i class="bi bi-briefcase me-1"></i>{{ $log->project_name }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark small">{{ $log->party_name }}</div>
                            <div class="font-monospace fw-bold text-primary small">
                                ₹{{ number_format($log->grand_total, 2) }}
                            </div>
                            <div class="text-muted small" style="font-size: 0.75rem;">
                                {{ $log->items_count }} items
                            </div>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $log->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td>
                            @if($log->user)
                                <div class="d-flex align-items-center gap-2">
                                    <div class="rounded-circle dark-symbol-avatar dark-symbol-user" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                        {{ strtoupper(substr($log->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-semibold text-dark small">{{ $log->user->name }}</div>
                                        <div class="badge bg-light text-muted border px-2 py-0.5" style="font-size: 0.7rem;">
                                            {{ ucfirst($log->user->role ?? 'User') }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">System / Anonymous</span>
                            @endif
                        </td>
                        <td>
                            <div class="small text-dark" style="max-width: 280px;">
                                {{ $log->description ?? 'No extra summary' }}
                            </div>
                        </td>
                        <td class="text-end pe-4 no-print">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#viewTxLogModal_{{ $log->id }}">
                                <i class="bi bi-eye me-1"></i> Details
                            </button>

                            <!-- Modal for Transaction Log Detail -->
                            <div class="modal fade" id="viewTxLogModal_{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow-lg text-start">
                                        <div class="modal-header border-bottom bg-light px-4 py-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <i class="bi bi-receipt text-primary fs-5"></i>
                                                <div>
                                                    <h5 class="modal-title fw-bold font-outfit text-dark mb-0">Transaction Audit Detail #{{ $log->id }}</h5>
                                                    <div class="small text-muted font-monospace">{{ $log->created_at ? $log->created_at->format('d-m-Y, h:i A') : '' }}</div>
                                                </div>
                                            </div>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <!-- Summary Metric Banner -->
                                            <div class="p-3 bg-light rounded-3 border mb-3">
                                                <div class="row g-2">
                                                    <div class="col-md-3">
                                                        <span class="text-muted small text-uppercase fw-semibold d-block">Module</span>
                                                        <span class="fw-bold text-dark text-capitalize">{{ $log->module }}</span>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="text-muted small text-uppercase fw-semibold d-block">Action</span>
                                                        @if($log->action === 'create')
                                                            <span class="badge bg-success text-white">Entry (Created)</span>
                                                        @elseif($log->action === 'update')
                                                            <span class="badge bg-primary text-white">Updated</span>
                                                        @elseif($log->action === 'delete')
                                                            <span class="badge bg-danger text-white">Deleted</span>
                                                        @else
                                                            <span class="badge bg-secondary text-white">{{ ucfirst($log->action) }}</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="text-muted small text-uppercase fw-semibold d-block">Reference No</span>
                                                        <span class="fw-bold font-monospace text-dark">#{{ $log->reference_no }}</span>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <span class="text-muted small text-uppercase fw-semibold d-block">Performed By</span>
                                                        <span class="fw-semibold text-dark">{{ $log->user->name ?? 'System' }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            @if($log->action === 'update' && !empty($log->changes['before']))
                                                <h6 class="fw-bold font-outfit text-dark mb-2"><i class="bi bi-clock-history me-1 text-primary"></i> Modifications (Before vs After)</h6>
                                                <div class="table-responsive border rounded-3 mb-3">
                                                    <table class="table table-sm table-striped align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th>Field</th>
                                                                <th>Previous Value (Before)</th>
                                                                <th>New Value (After)</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($log->changes['before'] as $k => $item)
                                                                @php
                                                                    $afterItem = $log->changes['after'][$k] ?? null;
                                                                @endphp
                                                                <tr>
                                                                    <td class="fw-semibold text-dark">{{ $item['label'] ?? ucfirst($k) }}</td>
                                                                    <td class="text-danger font-monospace">
                                                                        <del>{{ is_array($item['value'] ?? null) ? json_encode($item['value']) : ($item['value'] ?? 'N/A') }}</del>
                                                                    </td>
                                                                    <td class="text-success font-monospace fw-bold">
                                                                        {{ is_array($afterItem['value'] ?? null) ? json_encode($afterItem['value']) : ($afterItem['value'] ?? 'N/A') }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @elseif(!empty($log->changes['attributes']))
                                                <h6 class="fw-bold font-outfit text-dark mb-2"><i class="bi bi-card-checklist me-1 text-success"></i> Transaction Snapshot Attributes</h6>
                                                <div class="table-responsive border rounded-3 mb-3">
                                                    <table class="table table-sm table-hover align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th style="width: 40%;">Property</th>
                                                                <th>Recorded Value</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($log->changes['attributes'] as $k => $val)
                                                                <tr>
                                                                    <td class="text-muted fw-semibold">{{ ucfirst(str_replace('_', ' ', $k)) }}</td>
                                                                    <td class="font-monospace fw-medium text-dark">
                                                                        @if(in_array($k, ['grand_total', 'paid_amount']))
                                                                            ₹{{ number_format((float)$val, 2) }}
                                                                        @else
                                                                            {{ is_array($val) ? json_encode($val) : ($val ?? 'N/A') }}
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif

                                            <div class="p-3 bg-light rounded-3 border">
                                                <span class="text-muted small fw-semibold d-block mb-1">Audit Description & Notes</span>
                                                <p class="mb-0 text-dark small">{{ $log->description ?? 'No detailed description.' }}</p>
                                                @if($log->products_summary)
                                                    <div class="mt-2 small text-muted"><strong>Products:</strong> {{ $log->products_summary }}</div>
                                                @endif
                                                @if($log->ip_address)
                                                    <div class="mt-1 text-muted font-monospace" style="font-size: 0.75rem;">IP: {{ $log->ip_address }}</div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-light btn-sm border rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 10 : 9 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt-cutoff fs-1 d-block mb-2 text-secondary"></i>
                            No transaction audit logs found matching the selected filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($logs->hasPages())
        <div class="p-3 border-top no-print">
            {{ $logs->links() }}
        </div>
    @endif
</div>

@endsection

