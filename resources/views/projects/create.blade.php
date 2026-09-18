@extends('layouts.app')

@section('title', 'Add Project Entry')
@section('page_title', 'Create Project Entry')
@section('page_subtitle', 'Register a new project with PO Number, PO Date, and PO Amount')

@section('header_actions')
    <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Projects
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="card card-custom border-0 p-4">
            <form action="{{ route('projects.store') }}" method="POST">
                @csrf
                
                <h5 class="fw-bold font-outfit text-dark mb-4">Project Information</h5>

                <div class="row g-3">
                    @if(auth()->user()->isSuperAdmin())
                        <div class="col-12">
                            <label for="firm_id" class="form-label fw-semibold text-dark">Firm Scope <span class="text-danger">*</span></label>
                            <select class="form-select @error('firm_id') is-invalid @enderror" id="firm_id" name="firm_id" required>
                                <option value="">Select Firm</option>
                                @foreach($firms as $firm)
                                    <option value="{{ $firm->id }}" {{ old('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                                @endforeach
                            </select>
                            @error('firm_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endif

                    <div class="col-12">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name') }}" required placeholder="e.g. Metro Line 3 Fire Safety Installation">
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="po_number" class="form-label fw-semibold text-dark">PO Number <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-hash text-muted"></i></span>
                            <input type="text" class="form-control @error('po_number') is-invalid @enderror" id="po_number" name="po_number" value="{{ old('po_number') }}" required placeholder="e.g. PO-2026-8891">
                        </div>
                        @error('po_number') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="po_date" class="form-label fw-semibold text-dark">PO Date <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-calendar3 text-muted"></i></span>
                            <input type="date" class="form-control datepicker-ddmmyyyy @error('po_date') is-invalid @enderror" id="po_date" name="po_date" value="{{ old('po_date', date('Y-m-d')) }}" required>
                        </div>
                        @error('po_date') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12">
                        <label for="po_amount" class="form-label fw-semibold text-dark">PO Amount (₹) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-currency-rupee text-muted"></i></span>
                            <input type="number" step="any" min="0" class="form-control @error('po_amount') is-invalid @enderror" id="po_amount" name="po_amount" value="{{ old('po_amount') }}" required placeholder="e.g. 150000.00">
                        </div>
                        @error('po_amount') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('projects.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Project Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
