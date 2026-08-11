@extends('layouts.app')

@section('title', 'Party Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Parties</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">Party Management</h1>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createPartyModal">
            <i class="bi bi-plus-circle me-1"></i> Create Party
        </button>
    </div>

    <!-- Parties Card -->
    <div class="card card-premium">
        <div class="card-header d-flex flex-column sm:flex-row align-items-start sm:align-items-center justify-content-between gap-3">
            <span>List of Associated Parties / Customers</span>
            
            <!-- Simple Search Form -->
            <form method="GET" action="{{ route('parties.index') }}" class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4" placeholder="Search parties..." style="width: 220px; font-size: 12px;">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 11px;"></i>
                </div>
                <button type="submit" class="btn btn-sm btn-primary py-1.5 px-3">Search</button>
                @if(request('search'))
                    <a href="{{ route('parties.index') }}" class="btn btn-sm btn-outline-secondary py-1.5 px-3">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="parties-table" style="width:100%">
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
                            <th>{!! $sortLink('name', 'Party Name') !!}</th>
                            <th>{!! $sortLink('email', 'Email') !!}</th>
                            <th>{!! $sortLink('mobile', 'Mobile') !!}</th>
                            <th>{!! $sortLink('address', 'Address') !!}</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($parties as $party)
                            <tr>
                                <td>{{ $party->id }}</td>
                                <td><strong>{{ $party->name }}</strong></td>
                                <td>{{ $party->email ?? '-' }}</td>
                                <td>{{ $party->mobile ?? '-' }}</td>
                                <td>{{ $party->address }}</td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary edit-btn" data-id="{{ $party->id }}">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $party->id }}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-slate-400">No parties found matching the criteria.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination links -->
            <div class="mt-4 d-flex justify-content-end">
                {{ $parties->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Create Party Modal -->
<div class="modal fade" id="createPartyModal" tabindex="-1" aria-labelledby="createPartyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="create-party-form">
                <div class="modal-header">
                    <h5 class="modal-title" id="createPartyModalLabel">Create New Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Error Container -->
                    <div class="alert alert-danger d-none" id="create-errors"></div>

                    <div class="mb-3">
                        <label for="create_name" class="form-label">Party Name</label>
                        <input type="text" class="form-control" id="create_name" name="name" required placeholder="e.g. Kanex Fire Safety Pvt Ltd">
                    </div>
                    <div class="mb-3">
                        <label for="create_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="create_email" name="email" placeholder="party@company.com">
                    </div>
                    <div class="mb-3">
                        <label for="create_mobile" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="create_mobile" name="mobile" placeholder="9876543210">
                    </div>
                    <div class="mb-3">
                        <label for="create_address" class="form-label">Address</label>
                        <textarea class="form-control" id="create_address" name="address" rows="3" required placeholder="Ahmedabad."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Create Party</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Party Modal -->
<div class="modal fade" id="editPartyModal" tabindex="-1" aria-labelledby="editPartyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="edit-party-form">
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="editPartyModalLabel">Edit Party</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Error Container -->
                    <div class="alert alert-danger d-none" id="edit-errors"></div>

                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Party Name</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="edit_email" name="email">
                    </div>
                    <div class="mb-3">
                        <label for="edit_mobile" class="form-label">Mobile Number</label>
                        <input type="text" class="form-control" id="edit_mobile" name="mobile">
                    </div>
                    <div class="mb-3">
                        <label for="edit_address" class="form-label">Address</label>
                        <textarea class="form-control" id="edit_address" name="address" rows="3" required></textarea>
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
    $('#create-party-form').submit(function(e) {
        e.preventDefault();
        $('#create-errors').addClass('d-none').html('');
        
        $.ajax({
            url: "{{ route('parties.store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#createPartyModal').modal('hide');
                    $('#create-party-form')[0].reset();
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
            url: "/parties/" + id + "/edit",
            type: "GET",
            success: function(data) {
                $('#edit_id').val(data.id);
                $('#edit_name').val(data.name);
                $('#edit_email').val(data.email);
                $('#edit_mobile').val(data.mobile);
                $('#edit_address').val(data.address);
                $('#editPartyModal').modal('show');
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Could not fetch party data.'
                });
            }
        });
    });

    // Edit Form Submission
    $('#edit-party-form').submit(function(e) {
        e.preventDefault();
        let id = $('#edit_id').val();
        $('#edit-errors').addClass('d-none').html('');
        
        $.ajax({
            url: "/parties/" + id + "/update",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    $('#editPartyModal').modal('hide');
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

    // Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the party permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/parties/" + id,
                    type: "DELETE",
                    success: function(response) {
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON.message || 'Could not delete party.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection
