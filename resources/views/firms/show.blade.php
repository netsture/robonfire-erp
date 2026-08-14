@extends('layouts.app')

@section('title', 'Firm Profile')
@section('page_title', $firm->name)
@section('page_subtitle', 'Firm Profile & Resource Summary')

@section('header_actions')
    <a href="{{ route('firms.edit', $firm) }}" class="btn btn-primary rounded-pill px-3 font-outfit me-2">
        <i class="bi bi-pencil me-1"></i> Edit Firm
    </a>
    <a href="{{ route('firms.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit">
        <i class="bi bi-arrow-left me-1"></i> Back to Directory
    </a>
@endsection

@section('content')

<div class="row g-4">
    <!-- Firm Summary Card -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-4 h-100">
            <div class="d-flex align-items-center gap-3 mb-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 56px; height: 56px;">
                    <i class="bi bi-building fs-2"></i>
                </div>
                <div>
                    <h5 class="fw-bold font-outfit text-dark mb-1">{{ $firm->name }}</h5>
                    @if($firm->status === 'active')
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">Active Firm</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-1">Inactive</span>
                    @endif
                </div>
            </div>

            <hr class="my-3 text-muted">

            <div class="d-flex flex-column gap-2 my-2">
                <div class="small"><i class="bi bi-hash me-2 text-muted"></i>Firm ID: <span class="font-monospace fw-bold">#FRM-{{ $firm->id }}</span></div>
                <div class="small"><i class="bi bi-envelope me-2 text-muted"></i>Email: {{ $firm->email ?? 'N/A' }}</div>
                <div class="small"><i class="bi bi-telephone me-2 text-muted"></i>Phone: {{ $firm->phone ?? 'N/A' }}</div>
                <div class="small"><i class="bi bi-geo-alt me-2 text-muted"></i>Address: {{ $firm->address ?? 'N/A' }}</div>
            </div>
        </div>
    </div>

    <!-- Assigned Users -->
    <div class="col-12 col-md-8">
        <div class="card card-custom border-0 p-4 h-100">
            <h5 class="fw-bold font-outfit text-dark mb-3"><i class="bi bi-people me-2 text-primary"></i>Assigned Firm Users</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($firm->users as $u)
                            <tr>
                                <td class="fw-semibold text-dark">{{ $u->name }}</td>
                                <td class="small text-muted">{{ $u->email }}</td>
                                <td><span class="badge bg-light text-dark border">{{ ucfirst($u->role) }}</span></td>
                                <td>
                                    @if($u->status === 'active')
                                        <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No users assigned to this firm yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
