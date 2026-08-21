@extends('layouts.app')

@section('title', 'Create Role')
@section('page_title', 'Create New Role')
@section('page_subtitle', 'Define a new role title and configure module-level permission checkboxes')

@section('header_actions')
    <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Roles
    </a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-lg-10">
        <div class="card card-custom border-0 p-4">
            <form action="{{ route('roles.store') }}" method="POST">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="name" class="form-label fw-semibold text-dark">Role Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required placeholder="e.g. Chief Fire Marshal">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="description" class="form-label fw-semibold text-dark">Description</label>
                        <input type="text" class="form-control @error('description') is-invalid @enderror" id="description" name="description" value="{{ old('description') }}" placeholder="e.g. Fire safety compliance, equipment audit & emergency oversight">
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="border-top pt-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold font-outfit text-dark mb-0">Module Permissions Matrix</h5>
                            <span class="text-muted small">Select the actions users with this role are allowed to perform</span>
                        </div>
                        <button type="button" id="selectAllBtn" class="btn btn-sm btn-outline-primary rounded-pill px-3">Select All</button>
                    </div>

                    <div class="row g-3">
                        @foreach($permissionsByModule as $module => $permissions)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border rounded-3 p-3 h-100 bg-light-subtle">
                                    <div class="fw-bold text-dark mb-2 pb-2 border-bottom d-flex align-items-center justify-content-between">
                                        <span><i class="bi bi-folder2-open me-2 text-primary"></i>{{ $module }}</span>
                                        <span class="badge bg-light text-muted border">{{ count($permissions) }}</span>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        @foreach($permissions as $perm)
                                            <div class="form-check">
                                                <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $perm->id }}">
                                                <label class="form-check-label small text-dark fw-medium" for="perm_{{ $perm->id }}">
                                                    {{ $perm->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                    <a href="{{ route('roles.index') }}" class="btn btn-light border px-4">Cancel</a>
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-shield-check me-1"></i> Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAllBtn')?.addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.perm-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        this.textContent = allChecked ? 'Select All' : 'Deselect All';
    });
</script>
@endpush
