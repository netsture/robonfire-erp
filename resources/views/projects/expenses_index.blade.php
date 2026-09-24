@extends('layouts.app')

@section('title', 'Project Expense Management')
@section('page_title', 'Project Expense Management')
@section('page_subtitle', 'Track, record, and filter project expenses entry wise')

@section('header_actions')
    <a href="{{ route('projects.expenses.print', request()->query()) }}" target="_blank" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-2 no-print">
        <i class="bi bi-printer me-1"></i> Print Report
    </a>
    @if(!auth()->user()->isSuperAdmin())
        <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#addGeneralExpenseModal">
            <i class="bi bi-plus-circle me-1"></i> Add Project Expense
        </button>
    @endif
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Project Expense Management Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- Print Summary Single Line -->
<div class="d-none d-print-block mb-4 p-3 border rounded text-center">
    <div class="row text-center fw-bold font-outfit fs-6">
        <div class="col-4">
            Total Expenses Logged: {{ number_format($totalExpensesCount) }}
        </div>
        <div class="col-4 border-start border-end">
            Total Expense Amount: {{ number_format($totalExpensesAmount, 2) }}
        </div>
        <div class="col-4">
            Projects Tracked: {{ number_format($projects->count()) }}
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
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Expenses Logged</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">{{ number_format($totalExpensesCount) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-receipt-cutoff"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #e11d48 0%, #9f1239 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Total Expense Amount</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">₹{{ number_format($totalExpensesAmount, 2) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-currency-rupee"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100 text-white" style="background: linear-gradient(135deg, #10b981 0%, #047857 100%);">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-uppercase" style="color: #ffffff !important;">Projects Tracked</span>
                    <h3 class="fw-bold font-outfit text-white mb-0 mt-1" style="color: #ffffff !important;">{{ number_format($projects->count()) }}</h3>
                </div>
                <div class="rounded-circle dark-symbol-avatar shadow-sm" style="width: 48px; height: 48px; font-size: 1.25rem;">
                    <i class="bi bi-briefcase-fill"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('projects.expenses.index') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                <input type="text" name="search" class="form-control border-start-0 bg-light" placeholder="Search Item, Description, Project, Amount..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-12 col-md-3">
            <select name="project_id" class="form-select bg-light">
                <option value="">All Project Entries</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                        {{ $proj->project_name }} {{ $proj->po_number ? '('.$proj->po_number.')' : '' }}
                    </option>
                @endforeach
            </select>
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
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-dark w-100 rounded-3">Search</button>
            <a href="{{ route('projects.expenses.index') }}" class="btn btn-light border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Expenses Table -->
<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">#</th>
                    <th>
                        <a href="{{ route('projects.expenses.index', array_merge(request()->query(), ['sort_by' => 'project_name', 'sort_order' => request('sort_by') === 'project_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Project Entry
                            @if(request('sort_by') === 'project_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('projects.expenses.index', array_merge(request()->query(), ['sort_by' => 'expense_date', 'sort_order' => request('sort_by') === 'expense_date' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Date
                            @if(request('sort_by') === 'expense_date')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('projects.expenses.index', array_merge(request()->query(), ['sort_by' => 'item_name', 'sort_order' => request('sort_by') === 'item_name' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Item Name
                            @if(request('sort_by') === 'item_name')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th>
                        <a href="{{ route('projects.expenses.index', array_merge(request()->query(), ['sort_by' => 'description', 'sort_order' => request('sort_by') === 'description' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
                            Description
                            @if(request('sort_by') === 'description')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-end">
                        <a href="{{ route('projects.expenses.index', array_merge(request()->query(), ['sort_by' => 'amount', 'sort_order' => request('sort_by') === 'amount' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center ms-auto">
                            Amount (₹)
                            @if(request('sort_by') === 'amount')
                                <i class="bi bi-arrow-{{ request('sort_order', 'asc') === 'asc' ? 'up' : 'down' }} text-primary ms-1"></i>
                            @else
                                <i class="bi bi-arrow-down-up text-muted opacity-50 ms-1 small"></i>
                            @endif
                        </a>
                    </th>
                    <th class="text-center">Uploaded Document</th>
                    @if(auth()->user()->isSuperAdmin())
                        <th>Firm</th>
                    @endif
                    <th>
                        <a href="{{ route('projects.expenses.index', array_merge(request()->query(), ['sort_by' => 'created_at', 'sort_order' => request('sort_by') === 'created_at' && request('sort_order', 'asc') === 'asc' ? 'desc' : 'asc'])) }}" class="text-dark text-decoration-none d-inline-flex align-items-center">
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
                @forelse($expenses as $index => $expense)
                    <tr>
                        <td class="ps-4 fw-bold text-muted">{{ $expenses->firstItem() + $index }}</td>
                        <td>
                            @if($expense->project)
                                <div>
                                    <a href="{{ route('projects.show', ['project' => $expense->project, 'tab' => 'expenses']) }}" class="fw-semibold text-dark text-decoration-none hover-primary">
                                        {{ $expense->project->project_name }}
                                    </a>
                                    @if($expense->project->po_number)
                                        <div class="small text-muted font-monospace"><i class="bi bi-hash text-muted me-1"></i>{{ $expense->project->po_number }}</div>
                                    @endif
                                </div>
                            @else
                                <span class="text-muted small">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="small fw-medium text-dark">
                                <i class="bi bi-calendar3 text-muted me-1"></i>{{ $expense->expense_date ? $expense->expense_date->format('d-m-Y') : 'N/A' }}
                            </div>
                        </td>
                        <td>
                            <span class="fw-semibold text-dark">{{ $expense->item_name }}</span>
                        </td>
                        <td>
                            @if($expense->description)
                                <span class="text-muted small">{{ $expense->description }}</span>
                            @else
                                <span class="text-muted small italic">-</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-danger"><span class="currency-symbol">₹</span>{{ number_format($expense->amount, 2) }}</span>
                        </td>
                        <td class="text-center">
                            @if($expense->document_path)
                                <a href="{{ route('projects.expenses.download', [$expense->project, $expense]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 no-print" title="Download Uploaded Document">
                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Download
                                </a>
                                <span class="badge bg-light text-primary border d-none d-print-inline font-outfit fw-normal">Attached</span>
                            @else
                                <span class="badge bg-light text-muted border font-outfit fw-normal">No File</span>
                            @endif
                        </td>
                        @if(auth()->user()->isSuperAdmin())
                            <td>
                                <span class="badge bg-light text-dark border">{{ $expense->project->firm->name ?? 'N/A' }}</span>
                            </td>
                        @endif
                        <td class="small text-muted font-monospace">{{ $expense->created_at ? $expense->created_at->format('d-m-Y') : 'N/A' }}</td>
                        <td class="text-end pe-4 no-print">
                            <div class="d-flex align-items-center justify-content-end gap-1">
                                <a href="{{ route('projects.show', ['project' => $expense->project, 'tab' => 'expenses']) }}" class="btn btn-sm btn-light border text-dark" title="View Project Details">
                                    <i class="bi bi-eye"></i>
                                </a>
                                @if(!auth()->user()->isSuperAdmin())
                                    <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editIndexExpenseModal{{ $expense->id }}" title="Edit Expense">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteIndexExpenseModal{{ $expense->id }}" title="Delete Expense">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isSuperAdmin() ? 10 : 9 }}" class="text-center py-5 text-muted">
                            <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                            No project expense records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
        <div class="p-3 border-top d-flex justify-content-end no-print">
            {{ $expenses->links() }}
        </div>
    @endif
</div>

@if(!auth()->user()->isSuperAdmin())
    <!-- Add General Expense Modal -->
    <div class="modal fade" id="addGeneralExpenseModal" tabindex="-1" aria-labelledby="addGeneralExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="addGeneralExpenseModalLabel">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Add Project Expense
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('projects.expenses.store-general') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="project_id" class="form-label fw-semibold text-dark">Select Project Entry <span class="text-danger">*</span></label>
                            <select class="form-select" id="project_id" name="project_id" required>
                                <option value="">Choose Project...</option>
                                @foreach($projects as $proj)
                                    <option value="{{ $proj->id }}">{{ $proj->project_name }} {{ $proj->po_number ? '('.$proj->po_number.')' : '' }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="item_name" class="form-label fw-semibold text-dark">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="item_name" name="item_name" required placeholder="e.g. Electrical Cables & Fittings">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold text-dark">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2" placeholder="Enter details or notes regarding this expense..."></textarea>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="amount" class="form-label fw-semibold text-dark">Amount (₹) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-currency-rupee text-muted"></i></span>
                                    <input type="number" step="any" min="0" class="form-control" id="amount" name="amount" required placeholder="e.g. 15000.00">
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="expense_date" class="form-label fw-semibold text-dark">Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-muted"></i></span>
                                    <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label for="document" class="form-label fw-semibold text-dark">Upload Document</label>
                            <input type="file" class="form-control" id="document" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                            <div class="form-text small text-muted">Supported formats: PDF, JPG, PNG, DOC, DOCX (Max 10MB)</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit & Delete Modals -->
    @foreach($expenses as $expense)
        <!-- Edit Modal -->
        <div class="modal fade" id="editIndexExpenseModal{{ $expense->id }}" tabindex="-1" aria-labelledby="editIndexExpenseModalLabel{{ $expense->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="editIndexExpenseModalLabel{{ $expense->id }}">
                            <i class="bi bi-pencil-square me-1 text-primary"></i> Edit Project Expense
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('projects.expenses.update', [$expense->project, $expense]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label fw-semibold text-dark">Project Entry</label>
                                <input type="text" class="form-control bg-light" value="{{ $expense->project->project_name ?? 'N/A' }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label for="edit_idx_item_name_{{ $expense->id }}" class="form-label fw-semibold text-dark">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_idx_item_name_{{ $expense->id }}" name="item_name" value="{{ $expense->item_name }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit_idx_description_{{ $expense->id }}" class="form-label fw-semibold text-dark">Description</label>
                                <textarea class="form-control" id="edit_idx_description_{{ $expense->id }}" name="description" rows="2">{{ $expense->description }}</textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="edit_idx_amount_{{ $expense->id }}" class="form-label fw-semibold text-dark">Amount (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-currency-rupee text-muted"></i></span>
                                        <input type="number" step="any" min="0" class="form-control" id="edit_idx_amount_{{ $expense->id }}" name="amount" value="{{ $expense->amount }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="edit_idx_expense_date_{{ $expense->id }}" class="form-label fw-semibold text-dark">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-muted"></i></span>
                                        <input type="date" class="form-control" id="edit_idx_expense_date_{{ $expense->id }}" name="expense_date" value="{{ $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '' }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="edit_idx_document_{{ $expense->id }}" class="form-label fw-semibold text-dark">Upload Document</label>
                                @if($expense->document_path)
                                    <div class="mb-2 small text-muted">
                                        <i class="bi bi-paperclip me-1"></i> Current file attached. Upload a new file only to replace it.
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="edit_idx_document_{{ $expense->id }}" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
                                <div class="form-text small text-muted">Supported formats: PDF, JPG, PNG, DOC, DOCX (Max 10MB)</div>
                            </div>
                        </div>
                        <div class="modal-footer border-top">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Update Expense</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div class="modal fade" id="deleteIndexExpenseModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold text-danger">Confirm Delete</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-start py-3">
                        Are you sure you want to delete expense record <strong class="text-dark">{{ $expense->item_name }}</strong> (₹{{ number_format($expense->amount, 2) }})?
                        <br><small class="text-muted">This action cannot be undone.</small>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <form action="{{ route('projects.expenses.destroy', [$expense->project, $expense]) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger px-3">Delete Expense</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
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
