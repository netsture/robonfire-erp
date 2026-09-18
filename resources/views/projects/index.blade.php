@extends('layouts.app')

@section('title', 'Project Entry')
@section('page_title', 'Project Entry Management')
@section('page_subtitle', 'Manage project records, purchase orders, dates, and PO amounts')

@section('header_actions')
    @if(!auth()->user()->isSuperAdmin())
        <a href="{{ route('projects.create') }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium">
            <i class="bi bi-plus-circle me-1"></i> Add New Project
        </a>
    @endif
@endsection

@section('content')

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-check-circle-fill me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold font-outfit">Total Projects</span>
                    <h3 class="fw-bold font-outfit text-dark mb-0 mt-1">{{ number_format($totalProjects) }}</h3>
                </div>
                <div class="rounded-circle p-3 text-primary bg-primary bg-opacity-10">
                    <i class="bi bi-briefcase-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold font-outfit">Total PO Amount</span>
                    <h3 class="fw-bold font-outfit text-success mb-0 mt-1">₹{{ number_format($totalPoAmount, 2) }}</h3>
                </div>
                <div class="rounded-circle p-3 text-success bg-success bg-opacity-10">
                    <i class="bi bi-currency-rupee fs-4"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold font-outfit">Total Final Amount</span>
                    <h3 class="fw-bold font-outfit {{ $totalFinalAmount >= 0 ? 'text-primary' : 'text-danger' }} mb-0 mt-1">₹{{ number_format($totalFinalAmount, 2) }}</h3>
                </div>
                <div class="rounded-circle p-3 {{ $totalFinalAmount >= 0 ? 'text-primary bg-primary bg-opacity-10' : 'text-danger bg-danger bg-opacity-10' }}">
                    <i class="bi bi-calculator-fill fs-4"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
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
                    <th class="ps-4">Project Name</th>
                    <th>PO Number</th>
                    <th>PO Date</th>
                    <th class="text-end">PO Amount</th>
                    <th class="text-end">Total Expenses</th>
                    <th class="text-end">Final Amount</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th class="text-end pe-4">Actions</th>
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
                                ₹{{ number_format($project->po_amount, 2) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-danger">
                                ₹{{ number_format($project->total_expenses, 2) }}
                            </span>
                        </td>
                        <td class="text-end">
                            @php $finalAmount = $project->po_amount - $project->total_expenses; @endphp
                            <span class="fw-bold {{ $finalAmount >= 0 ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($finalAmount, 2) }}
                            </span>
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $project->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="text-end pe-4">
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
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 8 : 7 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                            No project entries found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($projects->hasPages())
        <div class="p-3 border-top d-flex justify-content-end">
            {{ $projects->links() }}
        </div>
    @endif
</div>

@endsection
