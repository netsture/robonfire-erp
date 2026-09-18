@extends('layouts.app')

@section('title', 'User-Wise Activity & Stock Entry Report')
@section('page_title', 'User-Wise Stock & Entry Details Report')
@section('page_subtitle', 'Audit trail of purchases, sales, returns, and inventory adjustments entered by each user')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        border-radius: 0.375rem !important;
        padding: 0.45rem 0.75rem !important;
        border-color: #dee2e6 !important;
        font-size: 0.9rem;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        border: 1px solid #e2e8f0 !important;
        z-index: 1055 !important;
    }
</style>
@endpush

@section('header_actions')
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium me-2">
        <i class="bi bi-printer me-1"></i> Print Report
    </button>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">User-Wise Stock & Entry Details Audit Report</h5>
    @if($selectedUser)
        <h6 class="fw-bold text-dark mt-2 mb-0">User: {{ $selectedUser->name }} (Role: {{ $selectedUser->role }})</h6>
    @else
        <h6 class="fw-bold text-dark mt-2 mb-0">All System Users</h6>
    @endif
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d-m-Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- User Selection & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.user-activity') }}" class="row g-2 align-items-end" id="userReportForm">
        @if(auth()->user()->isSuperAdmin() && count($firms) > 0)
            <div class="col-12 col-md-2">
                <label class="form-label small fw-semibold text-muted mb-1">Firm</label>
                <select name="firm_id" class="form-select form-select-sm" onchange="document.getElementById('userReportForm').submit()">
                    <option value="">All Firms</option>
                    @foreach($firms as $firm)
                        <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-4' : 'col-md-5' }}">
            <label class="form-label small fw-semibold text-dark mb-1">Select User (Staff / Admin)</label>
            <select name="user_id" id="user_id_select" class="form-select form-select-sm">
                <option value="">All Active Users</option>
                @foreach($users as $usr)
                    <option value="{{ $usr->id }}" {{ ($selectedUser && $selectedUser->id == $usr->id) ? 'selected' : '' }}>
                        {{ $usr->name }} ({{ $usr->role }}) {{ $usr->email ? '- '.$usr->email : '' }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">Start Date</label>
            <input type="text" name="start_date" class="form-control form-control-sm datepicker-ddmmyyyy" value="{{ request('start_date') }}" placeholder="DD-MM-YYYY">
        </div>

        <div class="col-12 col-md-2">
            <label class="form-label small fw-semibold text-muted mb-1">End Date</label>
            <input type="text" name="end_date" class="form-control form-control-sm datepicker-ddmmyyyy" value="{{ request('end_date') }}" placeholder="DD-MM-YYYY">
        </div>

        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-dark btn-sm w-100 rounded-3"><i class="bi bi-filter me-1"></i>Filter</button>
            <a href="{{ route('reports.user-activity') }}" class="btn btn-light border btn-sm w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

@if($selectedUser)
    <!-- Selected User Info Banner -->
    <div class="card card-custom border-0 p-4 mb-4">
        <div class="d-flex align-items-center gap-3">
            <div class="rounded-circle dark-symbol-avatar" style="width: 52px; height: 52px; background: linear-gradient(135deg, #0f172a 0%, #3b82f6 100%) !important;">
                <i class="bi bi-person-badge-fill fs-3 text-white"></i>
            </div>
            <div>
                <h4 class="fw-bold font-outfit text-dark mb-1">{{ $selectedUser->name }}</h4>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-shield-check me-1"></i>Role: {{ $selectedUser->role }}</span>
                    @if($selectedUser->email)
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1"><i class="bi bi-envelope me-1 text-muted"></i>{{ $selectedUser->email }}</span>
                    @endif
                    @if($selectedUser->firm)
                        <span class="badge bg-light text-dark border rounded-pill px-3 py-1"><i class="bi bi-building me-1 text-muted"></i>Firm: {{ $selectedUser->firm->name }}</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Summary Statistics Cards -->
<div class="row g-3 mb-4">
    <!-- Purchase Entries Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Purchases Entered</span>
                <i class="bi bi-cart-plus-fill fs-4" style="color: #ffffff !important;"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">{{ number_format($summary['purchase_count']) }} Orders</h3>
            <span class="small mt-1" style="color: #ffffff !important; opacity: 0.9;">Total Value: ₹{{ number_format($summary['purchase_amount'], 2) }}</span>
        </div>
    </div>

    <!-- Sales Entries Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Sales Entered</span>
                <i class="bi bi-bag-check-fill fs-4" style="color: #ffffff !important;"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">{{ number_format($summary['sale_count']) }} Orders</h3>
            <span class="small mt-1" style="color: #ffffff !important; opacity: 0.9;">Total Revenue: ₹{{ number_format($summary['sale_amount'], 2) }}</span>
        </div>
    </div>

    <!-- Returns Entries Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 text-white h-100 justify-content-center" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Returns Entered</span>
                <i class="bi bi-box-arrow-in-left fs-4" style="color: #ffffff !important;"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0" style="color: #ffffff !important;">{{ number_format($summary['return_count']) }} Returns</h3>
            <span class="small mt-1" style="color: #ffffff !important; opacity: 0.9;">Valuation: ₹{{ number_format($summary['return_amount'], 2) }}</span>
        </div>
    </div>

    <!-- Total Activity Entries Card -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3 bg-dark text-white h-100 justify-content-center">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="text-white small fw-semibold text-uppercase">TOTAL USER ENTRIES</span>
                <i class="bi bi-card-checklist fs-4 text-warning"></i>
            </div>
            <h3 class="fw-bold font-outfit mt-1 mb-0 text-white">
                {{ number_format($summary['total_entries']) }} Entries
            </h3>
            <span class="small text-white-50 mt-1">Audit log entries recorded</span>
        </div>
    </div>
</div>

<!-- User Entry Ledger Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="card-header bg-white border-0 py-3 px-4 d-flex align-items-center justify-content-between">
        <div>
            <h5 class="fw-bold font-outfit text-dark mb-0"><i class="bi bi-journal-text me-2 text-primary"></i>User Activity Entry Audit Log</h5>
            <span class="small text-muted">Complete breakdown of all purchase orders, sales orders, returns, and adjustments entered</span>
        </div>
        <span class="badge bg-light text-dark border font-monospace px-3 py-1.5 fw-bold">
            Total Records: {{ number_format(count($entriesLedger)) }}
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Date</th>
                    <th>Entry Type</th>
                    <th>Invoice / Ref No.</th>
                    <th>Party / Customer / Supplier</th>
                    <th>Products & Items</th>
                    <th>Status</th>
                    <th class="text-end">Total Amount (₹)</th>
                    <th class="pe-4">Entered By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entriesLedger as $row)
                    <tr>
                        <td class="ps-4 small font-monospace fw-medium text-dark">
                            {{ \Carbon\Carbon::parse($row['raw_date'])->format('d-m-Y') }}
                        </td>
                        <td>
                            <span class="badge {{ $row['badge_class'] }} rounded-pill px-3 py-1 fw-semibold">
                                {{ $row['entry_type'] }}
                            </span>
                        </td>
                        <td class="font-monospace">
                            @if($row['ref_route'])
                                <a href="{{ $row['ref_route'] }}" class="fw-bold text-decoration-none hover-primary" target="_blank">
                                    {{ $row['ref_no'] }} <i class="bi bi-box-arrow-up-right small ms-1"></i>
                                </a>
                            @else
                                <span class="fw-bold text-dark">{{ $row['ref_no'] }}</span>
                            @endif
                        </td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $row['party'] }}</div>
                            <span class="badge bg-light text-muted border py-0.5 px-2 small">{{ $row['party_type'] }}</span>
                        </td>
                        <td>
                            <div class="small fw-semibold text-dark">{{ $row['products_summary'] }}</div>
                            <span class="badge bg-secondary-subtle text-secondary py-0.5 px-1.5 small">{{ $row['items_count'] }} Item(s)</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border fw-semibold px-2.5 py-1">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td class="text-end fw-bold font-monospace text-dark">
                            ₹{{ number_format($row['amount'], 2) }}
                        </td>
                        <td class="pe-4">
                            <div class="fw-semibold text-dark">{{ $row['entered_by'] }}</div>
                            <span class="badge bg-info-subtle text-info border py-0.5 px-2 small">{{ $row['entered_by_role'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-muted"></i>
                            <h6>No entry records found for the selected criteria.</h6>
                            <p class="small mb-0">Select a user or date range to view entry details.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const userSelect = document.getElementById('user_id_select');
    if (userSelect) {
        new TomSelect('#user_id_select', {
            create: false,
            placeholder: "Search or Select User...",
            plugins: ['dropdown_input'],
            maxOptions: null,
            onChange: function(val) {
                document.getElementById('userReportForm').submit();
            }
        });
    }
});
</script>
@endpush
