@extends('layouts.app')

@section('title', $details['title'])

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $details['title'] }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">{{ $details['title'] }}</h1>
        </div>
        <a href="{{ route('purchases.create', ['type' => $type]) }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create New Entry
        </a>
    </div>

    <!-- Purchases Card -->
    <div class="card card-premium">
        <div class="card-header d-flex flex-column sm:flex-row align-items-start sm:align-items-center justify-content-between gap-3">
            <span>List of {{ $details['title'] }} Records</span>
            
            <!-- Simple Search Form -->
            <form method="GET" action="{{ route('purchases.index', ['type' => $type]) }}" class="d-flex gap-2 align-items-center">
                <div class="position-relative">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm ps-4" placeholder="Search records..." style="width: 220px; font-size: 12px;">
                    <i class="bi bi-search position-absolute text-muted" style="left: 10px; top: 50%; transform: translateY(-50%); font-size: 11px;"></i>
                </div>
                <button type="submit" class="btn btn-sm btn-primary py-1.5 px-3">Search</button>
                @if(request('search'))
                    <a href="{{ route('purchases.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary py-1.5 px-3">Clear</a>
                @endif
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="purchases-table" style="width:100%">
                    <thead>
                        @php
                            $docField = $details['doc_field'];
                            $sortLink = function($column, $label) {
                                $currentSort = request('sort', 'date');
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
                            <th>{!! $sortLink('date', 'Date') !!}</th>
                            <th>{!! $sortLink('party_name', 'Party Name') !!}</th>
                            <th>{!! $sortLink($docField, $details['doc_label']) !!}</th>
                            @if($type === 'outward')
                                <th>Vehicle</th>
                            @elseif($type === 'returnable_material')
                                <th>Reason</th>
                            @endif
                            <th>Project Name</th>
                            @if($type === 'inward')
                                <th>Total</th>
                                <th>{!! $sortLink('grand_total_with_tax', 'Total With Tax') !!}</th>
                            @endif
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td>{{ $purchase->id }}</td>
                                <td>{{ $purchase->date ? $purchase->date->format('d/m/Y') : '-' }}</td>
                                <td><strong>{{ $purchase->party ? $purchase->party->name : '-' }}</strong></td>
                                <td>{{ $purchase->$docField ?? '-' }}</td>
                                @if($type === 'outward')
                                    <td>{{ $purchase->vehicle ?? '-' }}</td>
                                @elseif($type === 'returnable_material')
                                    <td>{{ $purchase->reason ?? '-' }}</td>
                                @endif
                                <td>{{ $purchase->project_name ?? '-' }}</td>
                                @if($type === 'inward')
                                    <td>₹{{ number_format($purchase->grand_total, 2) }}</td>
                                    <td>₹{{ number_format($purchase->grand_total_with_tax, 2) }}</td>
                                @endif
                                <td class="text-end">
                                    <a href="{{ route('purchases.edit', ['type' => $type, 'id' => $purchase->id]) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $purchase->id }}">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 6 + ($type === 'outward' || $type === 'returnable_material' ? 1 : 0) + ($type === 'inward' ? 2 : 0) }}" class="text-center py-4 text-slate-400">
                                    No {{ $details['title'] }} records found matching the criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination links -->
            <div class="mt-4 d-flex justify-content-end">
                {{ $purchases->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Delete Button Click
    $(document).on('click', '.delete-btn', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This will delete the record and its items permanently!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/purchases/{{ $type }}/" + id,
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
                            text: xhr.responseJSON.message || 'Could not delete record.'
                        });
                    }
                });
            }
        });
    });
});
</script>
@endsection
