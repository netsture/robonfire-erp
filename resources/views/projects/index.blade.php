@extends('layouts.app')

@section('title', 'Project Entry')
@section('page_title', 'Project Entry Management')
@section('page_subtitle', 'Manage project records, purchase orders, dates, and PO amounts')

@section('header_actions')
    <a href="{{ route('projects.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('projects.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-plus-circle me-1"></i> Add New Project
        </a>
    @endif
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Project Entry Management Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- Print Summary Single Line -->
<div class="d-none d-print-block mb-4 p-3 border rounded text-center">
    <div class="row text-center fw-bold font-outfit fs-6">
        <div class="col-3">
            Total Projects: {{ number_format($totalProjects) }}
        </div>
        <div class="col-3 border-start border-end">
            Total PO Amount: {{ number_format($totalPoAmount, 2) }}
        </div>
        <div class="col-3 border-end">
            Total Expenses Amount: {{ number_format($totalExpensesAmount, 2) }}
        </div>
        <div class="col-3">
            Total Final Amount: {{ number_format($totalFinalAmount, 2) }}
        </div>
    </div>
</div>

<!-- Summary Cards -->
<div class="row g-3 mb-4 no-print">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Projects</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">{{ number_format($totalProjects) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total PO Amount</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">₹{{ number_format($totalPoAmount, 2) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Expenses Amount</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">₹{{ number_format($totalExpensesAmount, 2) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: {{ $totalFinalAmount >= 0 ? 'linear-gradient(135deg, #6f42c1 0%, #4c1d95 100%)' : 'linear-gradient(135deg, #dc2626 0%, #991b1b 100%)' }};">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Final Amount</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">₹{{ number_format($totalFinalAmount, 2) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-calculator-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('projects.index') }}" class="row g-2 align-items-center">
        <div class="col-12 {{ auth()->user()->isSuperAdmin() ? 'col-md-6' : 'col-md-9' }}">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Project Name, PO Number, Date (dd-mm-yyyy), PO Amount..." value="{{ request('search') }}">
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
            <a href="{{ route('projects.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Projects Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'project_name', 'sort_order' => request('sort_by') === 'project_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Project Name
                            @if(request('sort_by') === 'project_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'po_number', 'sort_order' => request('sort_by') === 'po_number' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            PO Number
                            @if(request('sort_by') === 'po_number')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'po_date', 'sort_order' => request('sort_by') === 'po_date' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            PO Date
                            @if(request('sort_by') === 'po_date')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'po_amount', 'sort_order' => request('sort_by') === 'po_amount' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            PO Amount
                            @if(request('sort_by') === 'po_amount')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'total_expenses', 'sort_order' => request('sort_by') === 'total_expenses' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Expense Amount
                            @if(request('sort_by') === 'total_expenses')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'final_amount', 'sort_order' => request('sort_by') === 'final_amount' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Final Amount
                            @if(request('sort_by') === 'final_amount')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>
                        <a href="{{ route('projects.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Created Date
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
                @forelse($projects as $project)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary fw-bold" style="width: 40px; height: 40px;">
                                    <i class="bi bi-briefcase fs-5"></i>
                                </div>
                                <div>
                                    <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                        {{ $project->project_name }}
                                    </a>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($project->po_number)
                                <span class="badge bg-light text-dark border font-monospace px-2 py-1 fs-6 fw-normal">
                                    <i class="bi bi-hash text-muted me-1"></i>{{ $project->po_number }}
                                </span>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($project->po_date)
                                <div class="small fw-medium text-dark">
                                    <i class="bi bi-calendar3 text-muted me-1"></i>{{ $project->po_date->format('d-m-Y') }}
                                </div>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-dark">
                                <span class="currency-symbol">₹</span>{{ number_format($project->po_amount, 2) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-danger">
                                <span class="currency-symbol">₹</span>{{ number_format($project->total_expenses, 2) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @php $finalAmount = $project->po_amount - $project->total_expenses; @endphp
                            <span class="fw-bold {{ $finalAmount >= 0 ? 'text-success' : 'text-danger' }}">
                                <span class="currency-symbol">₹</span>{{ number_format($finalAmount, 2) }}
                            </span>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $project->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-muted font-monospace">{{ $project->created_at ? $project->created_at->format('d-m-Y') : 'N/A' }}</td>
                        <td class="text-end pe-4 no-print">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-light border text-dark" title="View Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!auth()->user()->isSuperAdmin())
                                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-light border text-primary" title="Edit Project">
                                        <i class="bi bi-pencil-square"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal{{ $project->id }}" title="Delete Project">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    <!-- Delete Confirmation Modal -->
                                    <div class="modal fade" id="deleteProjectModal{{ $project->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-0 pb-0">
                                                    <h5 class="modal-title fw-bold text-danger">Confirm Delete</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body text-start py-3">
                                                    Are you sure you want to delete project <strong class="text-dark">{{ $project->project_name }}</strong>?
                                                    <br><small class="text-muted">This action cannot be undone.</small>
                                                </div>
                                                <div class="modal-footer border-0 pt-0">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger px-3">Delete Project</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 9 : 8 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                            No project entries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($projects->hasPages())
        <div class="p-3 border-top d-flex justify-content-end no-print">
            {{ $projects->links() }}
        </div>
    @endif
</div>

<style>
@media print {
    .no-print, nav, sidebar, #sidebar, header, #topbar, .btn, .header_actions, .modal { display: none !important; }
    i, i.bi, .rounded-circle, .currency-symbol { display: none !important; }
    body { background: #fff !important; padding: 0 !important; color: #000 !important; }
    #main-content { margin-left: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; background: transparent !important; }
    .table { width: 100% !important; border: 1px solid #dee2e6 !important; }
    .table-responsive { overflow: visible !important; }
}
</style>

@endsection
