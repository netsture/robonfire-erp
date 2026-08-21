@extends('layouts.app')

@section('title', 'Stock Adjustments')
@section('page_title', 'Stock Adjustment Audit')
@section('page_subtitle', 'Manually adjust stock quantities for damaged goods, returns, or stocktaking reconciliations')

@section('header_actions')
    <button type="button" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium" data-bs-toggle="modal" data-bs-target="#adjustStockModal">
        <i class="bi bi-arrow-repeat me-1"></i> New Adjustment
    </button>
@endsection

@section('content')

<div class="card card-custom border-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Date & Time</th>
                    <th>Product</th>
                    <th>Adjusted By</th>
                    <th>Adjustment Type</th>
                    <th>Qty</th>
                    <th>Reason / Notes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adj)
                    <tr>
                        <td class="ps-4 small text-muted">{{ $adj->created_at->format('M d, Y H:i A') }}</td>
                        <td>
                            <div class="fw-semibold text-dark">{{ $adj->product->name ?? 'Deleted Product' }}</div>
                            <div class="small text-muted font-monospace">{{ $adj->product->sku ?? '' }}</div>
                        </td>
                        <td class="small text-dark fw-medium">{{ $adj->user->name ?? 'System' }}</td>
                        <td>
                            @if($adj->type === 'add')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-plus-circle me-1"></i>ADD STOCK
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-1">
                                    <i class="bi bi-dash-circle me-1"></i>SUBTRACT STOCK
                                </span>
                            @endif
                        </td>
                        <td class="fw-bold text-dark">{{ $adj->quantity }}</td>
                        <td class="small text-muted">{{ $adj->reason }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-arrow-repeat fs-1 d-block mb-2 text-secondary"></i>
                            No stock adjustments recorded. Click "New Adjustment" to reconcile inventory.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($adjustments->hasPages())
        <div class="p-3 border-top">
            {{ $adjustments->links() }}
        </div>
    @endif
</div>

<!-- Modal for New Adjustment -->
<div class="modal fade" id="adjustStockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0">
            <form action="{{ route('inventory.adjust') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold font-outfit">Adjust Inventory Stock</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Product <span class="text-danger">*</span></label>
                        <select name="product_id" class="form-select" required>
                            <option value="">Choose item...</option>
                            @foreach($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->name }} (Current: {{ $prod->stock_quantity }} {{ $prod->unit }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Adjustment Action <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="add">Add Stock (+ Quantity)</option>
                            <option value="subtract">Subtract Stock (- Quantity)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" class="form-control" min="1" required placeholder="e.g. 10">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Reason for Adjustment <span class="text-danger">*</span></label>
                        <input type="text" name="reason" class="form-control" placeholder="e.g. Annual fire safety audit / Refill expired cylinder / Hose pressure replacement" required>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Process Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
