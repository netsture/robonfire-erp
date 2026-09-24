@extends('layouts.app')

@section('title', 'Project Details - ' . $project->project_name)
@section('page_title', 'Project Details')
@section('page_subtitle', 'Comprehensive view of project entry, purchase orders, and project expenses')

@section('header_actions')
    <div class="d-flex align-items-center gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium me-1 no-print">
            <i class="bi bi-printer me-1"></i> Print
        </button>
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium no-print">
            <i class="bi bi-arrow-left me-1"></i> Back to Projects
        </a>
        @if(!auth()->user()->isSuperAdmin())
            <a href="{{ route('projects.edit', $project) }}" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium no-print">
                <i class="bi bi-pencil-square me-1"></i> Edit Project
            </a>
        @endif
    </div>
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Project Details Report - {{ $project->project_name }}</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
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

@php
    $activeTab = request('tab', 'info');
    $totalExpenses = $project->expenses->sum('amount');
    $remainingBalance = $project->po_amount - $totalExpenses;
@endphp

<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <!-- Project Header Card -->
        <div class="card card-custom border-0 p-4 mb-4">
            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom flex-wrap gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle p-3 bg-primary bg-opacity-10 text-primary fw-bold" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-briefcase-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold font-outfit text-dark mb-1">{{ $project->project_name }}</h4>
                        <span class="text-muted small"><i class="bi bi-building me-1"></i>Firm: {{ $project->firm->name ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill font-outfit fs-6">
                        <i class="bi bi-check-circle-fill me-1"></i>Active Project
                    </span>
                </div>
            </div>

            <!-- Nav Tabs -->
            <ul class="nav nav-tabs nav-tabs-custom mt-4 mb-3 border-bottom" id="projectDetailsTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'info' ? 'active' : '' }} font-outfit fw-semibold px-4 py-2" id="info-tab" data-bs-toggle="tab" data-bs-target="#info-tab-pane" type="button" role="tab" aria-controls="info-tab-pane" aria-selected="{{ $activeTab === 'info' ? 'true' : 'false' }}">
                        <i class="bi bi-info-circle me-2"></i>Project Information
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link {{ $activeTab === 'expenses' ? 'active' : '' }} font-outfit fw-semibold px-4 py-2" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses-tab-pane" type="button" role="tab" aria-controls="expenses-tab-pane" aria-selected="{{ $activeTab === 'expenses' ? 'true' : 'false' }}">
                        <i class="bi bi-receipt-cutoff me-2"></i>Project Expense
                        @if($project->expenses->count() > 0)
                            <span class="badge bg-primary rounded-pill ms-2">{{ $project->expenses->count() }}</span>
                        @endif
                    </button>
                </li>
            </ul>

            <!-- Tab Content -->
            <div class="tab-content pt-2" id="projectDetailsTabContent">
                
                <!-- TAB 1: Project Information -->
                <div class="tab-pane fade {{ $activeTab === 'info' ? 'show active' : '' }}" id="info-tab-pane" role="tabpanel" aria-labelledby="info-tab" tabindex="0">
                    <div class="row g-4 mt-1">
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">PO Number</span>
                                <div class="fs-5 fw-bold font-monospace text-dark">
                                    @if($project->po_number)
                                        <i class="bi bi-hash text-muted me-1"></i>{{ $project->po_number }}
                                    @else
                                        <span class="text-muted fs-6 fw-normal">Not Specified</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 h-100">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">PO Date</span>
                                <div class="fs-5 fw-bold font-outfit text-dark">
                                    @if($project->po_date)
                                        <i class="bi bi-calendar3 text-muted me-1"></i>{{ $project->po_date->format('d-m-Y') }}
                                    @else
                                        <span class="text-muted fs-6 fw-normal">Not Specified</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-success bg-opacity-10 rounded-3 h-100 border border-success border-opacity-20">
                                <span class="text-success small text-uppercase fw-bold d-block mb-1">PO Amount</span>
                                <div class="fs-4 fw-bold font-outfit text-success">
                                    ₹{{ number_format($project->po_amount, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-danger bg-opacity-10 rounded-3 h-100 border border-danger border-opacity-20">
                                <span class="text-danger small text-uppercase fw-bold d-block mb-1">Total Expenses</span>
                                <div class="fs-4 fw-bold font-outfit text-danger">
                                    ₹{{ number_format($totalExpenses, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="p-3 {{ $remainingBalance >= 0 ? 'bg-primary bg-opacity-10 border-primary' : 'bg-warning bg-opacity-10 border-warning' }} rounded-3 h-100 border border-opacity-20">
                                <span class="{{ $remainingBalance >= 0 ? 'text-primary' : 'text-warning' }} small text-uppercase fw-bold d-block mb-1">Remaining Balance</span>
                                <div class="fs-4 fw-bold font-outfit {{ $remainingBalance >= 0 ? 'text-primary' : 'text-warning' }}">
                                    ₹{{ number_format($remainingBalance, 2) }}
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small text-uppercase fw-semibold d-block mb-1">Record Created At</span>
                                <div class="small fw-medium text-dark">
                                    <i class="bi bi-clock me-1 text-muted"></i>{{ $project->created_at ? $project->created_at->format('d-m-Y h:i A') : 'N/A' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: Project Expense -->
                <div class="tab-pane fade {{ $activeTab === 'expenses' ? 'show active' : '' }}" id="expenses-tab-pane" role="tabpanel" aria-labelledby="expenses-tab" tabindex="0">
                    
                    <!-- Expense Summary Cards -->
                    <div class="row g-3 my-2">
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-light rounded-3 border">
                                <span class="text-muted small text-uppercase fw-semibold font-outfit">Project PO Amount</span>
                                <h4 class="fw-bold font-outfit text-dark mb-0 mt-1">₹{{ number_format($project->po_amount, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-20">
                                <span class="text-danger small text-uppercase fw-semibold font-outfit">Total Expenses Logged</span>
                                <h4 class="fw-bold font-outfit text-danger mb-0 mt-1">₹{{ number_format($totalExpenses, 2) }}</h4>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="p-3 {{ $remainingBalance >= 0 ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-10 border-warning' }} rounded-3 border border-opacity-20">
                                <span class="{{ $remainingBalance >= 0 ? 'text-success' : 'text-warning' }} small text-uppercase fw-semibold font-outfit">Available Balance</span>
                                <h4 class="fw-bold font-outfit {{ $remainingBalance >= 0 ? 'text-success' : 'text-warning' }} mb-0 mt-1">₹{{ number_format($remainingBalance, 2) }}</h4>
                            </div>
                        </div>
                    </div>

                    <!-- Expenses Table Header & Add Button -->
                    <div class="d-flex align-items-center justify-content-between my-3 flex-wrap gap-2">
                        <div>
                            <h5 class="fw-bold font-outfit text-dark mb-0">Project Expense Records</h5>
                            <span class="text-muted small">Manage itemized expenses and documents for project: {{ $project->project_name }}</span>
                        </div>
                        @if(!auth()->user()->isSuperAdmin())
                            <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                                <i class="bi bi-plus-circle me-1"></i> Add Project Expense
                            </button>
                        @endif
                    </div>

                    <!-- Expenses Table -->
                    <div class="card border border-light-subtle rounded-3 overflow-hidden shadow-sm">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 50px;">#</th>
                                        <th>Date</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th class="text-end">Amount (₹)</th>
                                        <th class="text-center">Uploaded Document</th>
                                        @if(!auth()->user()->isSuperAdmin())
                                            <th class="text-end pe-3">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($project->expenses as $index => $expense)
                                        <tr>
                                            <td class="ps-3 fw-bold text-muted">{{ $index + 1 }}</td>
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
                                                <span class="fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if($expense->document_path)
                                                    <a href="{{ route('projects.expenses.download', [$project, $expense]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Download Uploaded Document">
                                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Download
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-muted border font-outfit fw-normal">No File</span>
                                                @endif
                                            </td>
                                            @if(!auth()->user()->isSuperAdmin())
                                                <td class="text-end pe-3">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <button type="button" class="btn btn-sm btn-light border text-primary" data-bs-toggle="modal" data-bs-target="#editExpenseModal{{ $expense->id }}" title="Edit Expense">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light border text-danger" data-bs-toggle="modal" data-bs-target="#deleteExpenseModal{{ $expense->id }}" title="Delete Expense">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            @endif
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ auth()->user()->isSuperAdmin() ? 6 : 7 }}" class="text-center py-5 text-muted">
                                                <i class="bi bi-receipt fs-1 d-block mb-2 text-secondary"></i>
                                                No expense records added yet for this project entry.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

            <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                <span class="text-muted small">Last updated: {{ $project->updated_at ? $project->updated_at->diffForHumans() : 'N/A' }}</span>
                <a href="{{ route('projects.index') }}" class="btn btn-light border px-4">Back to Projects</a>
            </div>
        </div>
    </div>
</div>

@if(!auth()->user()->isSuperAdmin())
    <!-- Add Expense Modal -->
    <div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="addExpenseModalLabel">
                        <i class="bi bi-plus-circle me-1 text-primary"></i> Add Project Expense
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('projects.expenses.store', $project) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
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

    <!-- Edit Expense & Delete Modals -->
    @foreach($project->expenses as $expense)
        <!-- Edit Modal -->
        <div class="modal fade" id="editExpenseModal{{ $expense->id }}" tabindex="-1" aria-labelledby="editExpenseModalLabel{{ $expense->id }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h5 class="modal-title fw-bold text-dark" id="editExpenseModalLabel{{ $expense->id }}">
                            <i class="bi bi-pencil-square me-1 text-primary"></i> Edit Project Expense
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('projects.expenses.update', [$project, $expense]) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_item_name_{{ $expense->id }}" class="form-label fw-semibold text-dark">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_item_name_{{ $expense->id }}" name="item_name" value="{{ $expense->item_name }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit_description_{{ $expense->id }}" class="form-label fw-semibold text-dark">Description</label>
                                <textarea class="form-control" id="edit_description_{{ $expense->id }}" name="description" rows="2">{{ $expense->description }}</textarea>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-12 col-md-6">
                                    <label for="edit_amount_{{ $expense->id }}" class="form-label fw-semibold text-dark">Amount (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-currency-rupee text-muted"></i></span>
                                        <input type="number" step="any" min="0" class="form-control" id="edit_amount_{{ $expense->id }}" name="amount" value="{{ $expense->amount }}" required>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <label for="edit_expense_date_{{ $expense->id }}" class="form-label fw-semibold text-dark">Date <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-muted"></i></span>
                                        <input type="date" class="form-control" id="edit_expense_date_{{ $expense->id }}" name="expense_date" value="{{ $expense->expense_date ? $expense->expense_date->format('Y-m-d') : '' }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="edit_document_{{ $expense->id }}" class="form-label fw-semibold text-dark">Upload Document</label>
                                @if($expense->document_path)
                                    <div class="mb-2 small text-muted">
                                        <i class="bi bi-paperclip me-1"></i> Current file attached. Upload a new file only to replace it.
                                    </div>
                                @endif
                                <input type="file" class="form-control" id="edit_document_{{ $expense->id }}" name="document" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
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
        <div class="modal fade" id="deleteExpenseModal{{ $expense->id }}" tabindex="-1" aria-hidden="true">
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
                        <form action="{{ route('projects.expenses.destroy', [$project, $expense]) }}" method="POST" class="d-inline">
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
    .no-print, nav, sidebar, #sidebar, header, #topbar, .btn, .header_actions, .modal, .nav-tabs { display: none !important; }
    i, i.bi, .rounded-circle, .currency-symbol { display: none !important; }
    body { background: #fff !important; padding: 0 !important; color: #000 !important; }
    #main-content { margin-left: 0 !important; padding: 0 !important; }
    .card { border: none !important; box-shadow: none !important; background: transparent !important; }
    .table { width: 100% !important; border: 1px solid #dee2e6 !important; }
    .table-responsive { overflow: visible !important; }
    .tab-pane { display: block !important; opacity: 1 !important; }
}
</style>

@endsection
