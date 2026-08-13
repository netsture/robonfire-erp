@extends('layouts.app')

@section('title', 'New Purchase Order')
@section('page_title', 'Create Purchase Order')
@section('page_subtitle', 'Restock products from vendors with automatic inventory increment')

@section('header_actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
@endsection

@section('content')
<form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
    @csrf
    
    <div class="row g-3">
        <!-- Main Form Column -->
        <div class="col-12 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Purchase Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="supplier_id" class="form-label fw-semibold text-dark">Supplier / Vendor <span class="text-danger">*</span></label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }} ({{ $supplier->company_name ?? 'Individual' }})</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="reference_no" class="form-label fw-semibold text-dark">Reference # <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="reference_no" name="reference_no" value="{{ old('reference_no', $autoRef) }}" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="purchase_date" class="form-label fw-semibold text-dark">Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <!-- Products Table Line Items -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold font-outfit text-dark mb-0">Order Line Items</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="addRowBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Product Line
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 45%;">Product Item</th>
                                <th style="width: 20%;">Qty</th>
                                <th style="width: 25%;">Unit Cost ($)</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            <tr class="item-row">
                                <td>
                                    <select name="products[0][id]" class="form-select product-select" required>
                                        <option value="">Select Product...</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}">{{ $prod->name }} (SKU: {{ $prod->sku }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="products[0][qty]" class="form-control qty-input" min="1" value="1" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="products[0][cost]" class="form-control cost-input" value="0.00" required>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-light border text-danger remove-row-btn"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary & Totals Column -->
        <div class="col-12 col-lg-4">
            <div class="card card-custom border-0 p-4">
                <h5 class="fw-bold font-outfit text-dark mb-3">Order Summary</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold font-outfit text-dark fs-5" id="subtotalDisplay">$0.00</span>
                </div>

                <div class="mb-3">
                    <label for="discount_amount" class="form-label small fw-semibold">Discount ($)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="0.00">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping / Freight Cost ($)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="0.00">
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark fs-5">Grand Total:</span>
                    <span class="fw-bold font-outfit text-primary fs-3" id="grandTotalDisplay">$0.00</span>
                </div>

                <div class="mb-3">
                    <label for="paid_amount" class="form-label small fw-semibold text-dark">Amount Paid ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="0.00" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label small fw-semibold">Notes / Vendor References</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="e.g. Received via DHL Express"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 font-outfit fw-semibold mt-2">
                    <i class="bi bi-check-circle me-1"></i> Process Purchase Order
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let rowCount = 1;

        const productsOptions = `@foreach($products as $prod)<option value="{{ $prod->id }}" data-cost="{{ $prod->cost_price }}">{{ $prod->name }} (SKU: {{ $prod->sku }})</option>@endforeach`;

        document.getElementById('addRowBtn').addEventListener('click', function () {
            const container = document.getElementById('itemsContainer');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td>
                    <select name="products[${rowCount}][id]" class="form-select product-select" required>
                        <option value="">Select Product...</option>
                        ${productsOptions}
                    </select>
                </td>
                <td>
                    <input type="number" name="products[${rowCount}][qty]" class="form-control qty-input" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="products[${rowCount}][cost]" class="form-control cost-input" value="0.00" required>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light border text-danger remove-row-btn"><i class="bi bi-trash"></i></button>
                </td>
            `;
            container.appendChild(newRow);
            rowCount++;
            calculateTotals();
        });

        document.getElementById('itemsContainer').addEventListener('click', function(e) {
            if (e.target.closest('.remove-row-btn')) {
                const rows = document.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    e.target.closest('.item-row').remove();
                    calculateTotals();
                }
            }
        });

        document.getElementById('itemsContainer').addEventListener('change', function(e) {
            if (e.target.classList.contains('product-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const cost = selectedOption.getAttribute('data-cost') || 0;
                const row = e.target.closest('.item-row');
                row.querySelector('.cost-input').value = parseFloat(cost).toFixed(2);
                calculateTotals();
            }
        });

        document.getElementById('itemsContainer').addEventListener('input', calculateTotals);
        document.getElementById('discount_amount').addEventListener('input', calculateTotals);
        document.getElementById('shipping_cost').addEventListener('input', calculateTotals);

        function calculateTotals() {
            let subtotal = 0;
            const rows = document.querySelectorAll('.item-row');
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                subtotal += (qty * cost);
            });

            const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
            const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
            const grandTotal = Math.max(0, subtotal - discount + shipping);

            document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('grandTotalDisplay').textContent = '$' + grandTotal.toFixed(2);
            document.getElementById('paid_amount').value = grandTotal.toFixed(2);
        }
    });
</script>
@endpush
