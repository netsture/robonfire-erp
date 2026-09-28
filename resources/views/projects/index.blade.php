@extends('layouts.app')

@section('title', 'Project Entry')
@section('page_title', 'Project Entry Management')
@section('page_subtitle', 'Manage project records, purchase orders, dates, and PO amounts')

@section('header_actions')
    <a href="{{ route('projects.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
    @if(!auth()->user()->isSuperAdmin())
        <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#addProjectModal">
            <i class="bi bi-plus-circle me-1"></i> Add New Project
        </button>
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

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center mb-1">
            <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
            <div class="fw-bold">Please correct the following errors:</div>
        </div>
        <ul class="mb-0 ps-4 text-start">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

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
                                    <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editProjectModal{{ $project->id }}" title="Edit Project">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteProjectModal{{ $project->id }}" title="Delete Project">
                                        <i class="bi bi-trash"></i>
                                    </button>

                                    <!-- Edit Project Modal -->
                                    <div class="modal fade" id="editProjectModal{{ $project->id }}" tabindex="-1" aria-labelledby="editProjectModalLabel{{ $project->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 shadow">
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title fw-bold text-dark" id="editProjectModalLabel{{ $project->id }}">
                                                        <i class="bi bi-pencil-square me-1 text-primary"></i> Edit Project Entry
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('projects.update', $project) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body text-start">
                                                        @if(auth()->user()->isSuperAdmin())
                                                            <div class="mb-3">
                                                                <label for="edit_firm_id_{{ $project->id }}" class="form-label fw-semibold text-dark">Firm Scope <span class="text-danger">*</span></label>
                                                                <select class="form-select" id="edit_firm_id_{{ $project->id }}" name="firm_id" required>
                                                                    <option value="">Select Firm</option>
                                                                    @foreach($firms as $firm)
                                                                        <option value="{{ $firm->id }}" {{ old('firm_id', $project->firm_id) == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        @endif

                                                        <div class="mb-3">
                                                            <label for="edit_project_name_{{ $project->id }}" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" id="edit_project_name_{{ $project->id }}" name="project_name" value="{{ old('project_name', $project->project_name) }}" required placeholder="e.g. Metro Line 3 Fire Safety Installation">
                                                        </div>

                                                        <div class="row g-2 mb-3">
                                                            <div class="col-12 col-md-6">
                                                                <label for="edit_po_number_{{ $project->id }}" class="form-label fw-semibold text-dark">PO Number <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light"><i class="bi bi-hash text-muted"></i></span>
                                                                    <input type="text" class="form-control" id="edit_po_number_{{ $project->id }}" name="po_number" value="{{ old('po_number', $project->po_number) }}" required placeholder="e.g. PO-2026-8891">
                                                                </div>
                                                            </div>
                                                            <div class="col-12 col-md-6">
                                                                <label for="edit_po_date_{{ $project->id }}" class="form-label fw-semibold text-dark">PO Date <span class="text-danger">*</span></label>
                                                                <div class="input-group">
                                                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-muted"></i></span>
                                                                    <input type="date" class="form-control datepicker-ddmmyyyy" id="edit_po_date_{{ $project->id }}" name="po_date" value="{{ old('po_date', $project->po_date ? $project->po_date->format('Y-m-d') : '') }}" required>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label for="edit_po_amount_{{ $project->id }}" class="form-label fw-semibold text-dark">PO Amount (₹) <span class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <span class="input-group-text bg-light"><i class="bi bi-currency-rupee text-muted"></i></span>
                                                                <input type="number" step="any" min="0" class="form-control" id="edit_po_amount_{{ $project->id }}" name="po_amount" value="{{ old('po_amount', $project->po_amount) }}" required placeholder="e.g. 150000.00">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer border-top">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Update Project Entry</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

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

@if(!auth()->user()->isSuperAdmin())
    <!-- Add Project Modal -->
    <div class="modal fade" id="addProjectModal" tabindex="-1" aria-labelledby="addProjectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="addProjectModalLabel">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Add New Project Entry
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        @if(auth()->user()->isSuperAdmin())
                            <div class="mb-3">
                                <label for="firm_id" class="form-label fw-semibold text-dark">Firm Scope <span class="text-danger">*</span></label>
                                <select class="form-select" id="firm_id" name="firm_id" required>
                                    <option value="">Select Firm</option>
                                    @foreach($firms as $firm)
                                        <option value="{{ $firm->id }}" {{ old('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="mb-3">
                            <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="project_name" name="project_name" value="{{ old('project_name') }}" required placeholder="e.g. Metro Line 3 Fire Safety Installation">
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="po_number" class="form-label fw-semibold text-dark">PO Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-hash text-muted"></i></span>
                                    <input type="text" class="form-control" id="po_number" name="po_number" value="{{ old('po_number') }}" required placeholder="e.g. PO-2026-8891">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="po_date" class="form-label fw-semibold text-dark">PO Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-muted"></i></span>
                                    <input type="date" class="form-control datepicker-ddmmyyyy" id="po_date" name="po_date" value="{{ old('po_date', date('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="po_amount" class="form-label fw-semibold text-dark">PO Amount (₹) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-currency-rupee text-muted"></i></span>
                                <input type="number" step="any" min="0" class="form-control" id="po_amount" name="po_amount" value="{{ old('po_amount') }}" required placeholder="e.g. 150000.00">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Project Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

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
