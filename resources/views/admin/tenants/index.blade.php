@extends('layouts.app')

@section('title', 'Tenant Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Tenants</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Tenant Management</h1>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createTenantModal">
            <i class="bi bi-plus-circle me-1"></i> Create Tenant
        </button>
    </div>

    <!-- Tenants Card -->
    <div class="card card-premium">
        <div class="card-header d-flex flex-column sm:flex-row align-items-start sm:align-items-center justify-content-between gap-3">
            <span>List of System Tenants</span>
            
            <!-- Simple Search Form -->
            <form method="GET" action="{{ route('admin.tenants.index') }}" class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4" placeholder="Search tenants..." style="width: 220px; font-size: 12px;">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 11px;"></i>
                </div>
                <button type="submit" class="btn btn-sm btn-primary py-1.5 px-3">Search</button>
                @if(request('search'))
                    <a href="{{ route('admin.tenants.index') }}" class="btn btn-sm btn-outline-secondary py-1.5 px-3">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="tenants-table" style="width:100%">
                    <thead>
                        @php
                            $sortLink = function($column, $label) {
                                $currentSort = request('sort', 'id');
                                $currentDir = request('direction', 'desc');
                                $nextDir = ($currentSort === $column && $currentDir === 'asc') ? 'desc' : 'asc';
                                $icon = '';
                                if ($currentSort === $column) {
                                    $icon = $currentDir === 'asc' ? ' <i class="bi bi-arrow-up"></i>' : ' <i class="bi bi-arrow-down"></i>';
                                }
                                $url = request()->fullUrlWithQuery(['sort' => $column, 'direction' => $nextDir]);
                                return '<a href="' . $url . '" class="text-decoration-none text-slate-700 font-bold">' . $label . $icon . '</a>';
                            };
                        @endphp
                        <tr>
                            <th>{!! $sortLink('id', 'ID') !!}</th>
                            <th>{!! $sortLink('name', 'Company Name') !!}</th>
                            <th>{!! $sortLink('email', 'Email') !!}</th>
                            <th>{!! $sortLink('mobile', 'Mobile') !!}</th>
                            <th>{!! $sortLink('status', 'Status') !!}</th>
                            <th>Created At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tenants as $tenant)
                            <tr>
                                <td>{{ $tenant->id }}</td>
                                <td><strong>{{ $tenant->name }}</strong></td>
                                <td>{{ $tenant->email ?? '-' }}</td>
                                <td>{{ $tenant->mobile ?? '-' }}</td>
                                <td>
                                    <span class="badge {{ $tenant->status === 'Active' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $tenant->status }}
                                    </span>
                                </td>
                                <td>{{ $tenant->created_at ? $tenant->created_at->format('d/m/Y') : '-' }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="{{ $tenant->id }}">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-slate-400">No tenants found matching the criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination links -->
            <div class="mt-4 d-flex justify-content-end">
                {{ $tenants->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Create Tenant Modal -->
<div class="modal fade" id="createTenantModal" tabindex="-1" aria-labelledby="createTenantModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="create-tenant-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="createTenantModalLabel">Create New Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Error Container -->
                    <div class="alert alert-danger d-none" id="create-errors"></div>

                    <div class="mb-3">
                        <label for="create_name" class="form-label">Company/Tenant Name</label>
                        <input type="text" class="form-control" id="create_name" name="name" required placeholder="e.g. Kanex Fire Safety Pvt Ltd">
                    </div>
                    <div class="mb-3">
                        <label for="create_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="create_email" name="email" required placeholder="admin@company.com">
                    </div>
                    <div class="mb-3">
                        <label for="create_mobile" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="create_mobile" name="mobile" required placeholder="9876543210">
                    </div>

                    <h6 class="border-bottom pb-2 mt-4 mb-3 text-secondary">Administrator Login Credentials</h6>
                    
                    <div class="mb-3">
                        <label for="create_username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="create_username" name="username" required placeholder="kanexadmin">
                    </div>
                    <div class="mb-3">
                        <label for="create_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="create_password" name="password" required placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label for="create_status" class="form-label">Status</label>
                        <select class="form-select" id="create_status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Tenant</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Tenant Modal -->
<div class="modal fade" id="editTenantModal" tabindex="-1" aria-labelledby="editTenantModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit-tenant-form">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTenantModalLabel">Edit Tenant</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Error Container -->
                    <div class="alert alert-danger d-none" id="edit-errors"></div>

                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Company/Tenant Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_mobile" class="form-label">Mobile</label>
                        <input type="text" class="form-control" id="edit_mobile" name="mobile" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select class="form-select" id="edit_status" name="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Create Form Submission
    $('#create-tenant-form').submit(function(e) {
        e.preventDefault();
        $('#create-errors').addClass('d-none').html('');
        
        $.ajax({
            url: "{{ route('admin.tenants.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createTenantModal').modal('hide');
                    $('#create-tenant-form')[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#create-errors').removeClass('d-none').html(errorHtml);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON.message || 'Something went wrong.'
                    });
                }
            }
        });
    });

    // Edit Button Click
    $(document).on('click', '.edit-btn', function() {
        let id = $(this).data('id');
        $('#edit-errors').addClass('d-none').html('');
        
        $.ajax({
            url: "/admin/tenants/" + id + "/edit",
            type: "GET",
            success: function(data) {
                $('#edit_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_email').val(data.email);
                $('#edit_mobile').val(data.mobile);
                $('#edit_status').val(data.status);
                $('#editTenantModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch tenant data.'
                });
            }
        });
    });

    // Edit Form Submission
    $('#edit-tenant-form').submit(function(e) {
        e.preventDefault();
        let id = $('#edit_id').val();
        $('#edit-errors').addClass('d-none').html('');
        
        $.ajax({
            url: "/admin/tenants/" + id + "/update",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editTenantModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.message
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#edit-errors').removeClass('d-none').html(errorHtml);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON.message || 'Something went wrong.'
                    });
                }
            }
        });
    });
});
</script>
@endsection
