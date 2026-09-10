@extends('layouts.app')

@section('title', 'Edit Purchase Order')
@section('page_title', 'Edit Purchase Order: ' . $purchase->invoice_number)
@section('page_subtitle', 'Modify purchase order details and line items with automatic inventory recalculation')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-control {
        border-radius: 0.375rem !important;
        padding: 0.45rem 0.75rem !important;
        border-color: #dee2e6 !important;
        font-size: 0.9rem;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        border: 1px solid #e2e8f0 !important;
        z-index: 1055 !important;
    }
</style>
@endpush

@section('header_actions')
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Purchases
    </a>
@endsection

@section('content')
<form action="{{ route('purchases.update', $purchase) }}" method="POST" id="purchaseForm">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Form Column -->
        <div class="col-12 col-xl-9 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Edit Purchase Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="supplier_id" class="form-label fw-semibold text-dark">Select Supplier Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier Name</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $purchase->supplier_id) == $supplier->id ? 'selected' : '' }}>{{ $supplier->company_name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name', $purchase->project_name) }}" required>
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="invoice_number" class="form-label fw-semibold text-dark">Invoice No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $purchase->invoice_number) }}" required>
                        @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="purchase_date" class="form-label fw-semibold text-dark">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', $purchase->purchase_date) }}" required>
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold font-outfit text-dark mb-0">Order Line Items</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="addRowBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Line Item
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 180px;">Category <span class="text-danger">*</span></th>
                                <th style="min-width: 180px;">Product Item <br>(Brand)</th>
                                <th style="min-width: 100px;">HSN Code</th>
                                <th style="min-width: 60px;">Type</th>
                                <th style="min-width: 80px;">Qty</th>
                                <th style="min-width: 100px;">Unit Cost(₹)</th>
                                <th style="min-width: 80px;">Tax (%)</th>
                                <th style="min-width: 110px;">Total (₹)</th>
                                <th style="min-width: 110px;">Total w/ Tax</th>
                                <th style="width: 50px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            @php
                                $itemsToDisplay = old('products') ?? $purchase->items->toArray();
                            @endphp
                            @foreach($itemsToDisplay as $index => $item)
                                @php
                                    $prodId = $item['product_id'] ?? ($item['id'] ?? null);
                                    $selProd = $products->firstWhere('id', $prodId);
                                    $selCatId = $selProd ? $selProd->category_id : null;
                                    $qty = $item['quantity'] ?? ($item['qty'] ?? 1);
                                    $cost = $item['unit_cost'] ?? ($item['cost'] ?? ($selProd ? $selProd->cost_price : 0));
                                    $tax = $item['tax_percent'] ?? ($selProd ? $selProd->tax_percent : 0);
                                @endphp
                                <tr class="item-row">
                                    <td>
                                        <select class="form-select form-select-sm category-select" required>
                                            <option value="">Select Category...</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}" {{ $selCatId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="products[{{ $index }}][id]" class="form-select form-select-sm product-select" required>
                                            <option value="">Select Product...</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}" 
                                                        data-category="{{ $prod->category_id }}" 
                                                        data-hsn="{{ $prod->hsn_code ?? '' }}" 
                                                        data-unit="{{ $prod->unit }}" 
                                                        data-cost="{{ $prod->cost_price }}" 
                                                        data-tax="{{ $prod->tax_percent }}"
                                                        {{ $prodId == $prod->id ? 'selected' : '' }}>
                                                    {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN"></td>
                                    <td><input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type"></td>
                                    <td><input type="number" name="products[{{ $index }}][qty]" class="form-control form-control-sm qty-input" min="1" value="{{ $qty }}" required></td>
                                    <td><input type="number" min="0" name="products[{{ $index }}][cost]" class="form-control form-control-sm cost-input" value="{{ $cost }}" required></td>
                                    <td><input type="number" name="products[{{ $index }}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="{{ $tax }}" min="0"></td>
                                    <td><input type="text" class="form-control form-control-sm row-subtotal-input bg-light" readonly value="₹0"></td>
                                    <td><input type="text" class="form-control form-control-sm row-total-tax-input bg-light fw-bold text-dark" readonly value="₹0"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light border text-danger remove-row-btn"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary Column -->
        <div class="col-12 col-xl-3 col-lg-4">
            <div class="card card-custom border-0 p-4">
                <h5 class="fw-bold font-outfit text-dark mb-3">Order Summary</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Subtotal:</span>
                    <span class="fw-bold text-dark" id="displaySubtotal">₹0.00</span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Tax Total:</span>
                    <span class="fw-semibold text-dark" id="displayTax">₹0.00</span>
                </div>
                
                <div class="mb-2">
                    <label for="discount_amount" class="form-label small fw-semibold">Discount Amount (₹)</label>
                    <input type="number" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="{{ (float)old('discount_amount', $purchase->discount_amount) == 0 ? 0 : old('discount_amount', $purchase->discount_amount) }}">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping Cost (₹)</label>
                    <input type="number" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="{{ (float)old('shipping_cost', $purchase->shipping_cost) == 0 ? 0 : old('shipping_cost', $purchase->shipping_cost) }}">
                </div>

                <hr class="my-3 text-muted">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <span class="fw-bold text-dark">Grand Total:</span>
                    <span class="fs-4 fw-bold text-primary" id="displayGrandTotal">₹0.00</span>
                </div>

                <div class="mb-3">
                    <label for="payment_status" class="form-label fw-semibold text-dark">Payment Status</label>
                    <select class="form-select form-select-sm" id="payment_status" name="payment_status">
                        <option value="pending" {{ old('payment_status', $purchase->payment_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('payment_status', $purchase->payment_status) == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ old('payment_status', $purchase->payment_status) == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="partial" {{ old('payment_status', $purchase->payment_status) == 'partial' ? 'selected' : '' }}>Partial</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="paid_amount" class="form-label fw-semibold text-dark">Paid Amount (₹)</label>
                    <input type="number" class="form-control form-control-sm" id="paid_amount" name="paid_amount" value="{{ (float)old('paid_amount', $purchase->paid_amount) == 0 ? 0 : old('paid_amount', $purchase->paid_amount) }}" required>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold text-dark">Purchase Order Notes / Remark</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="Optional notes...">{{ old('notes', $purchase->notes) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 font-outfit fw-semibold">
                    <i class="bi bi-check-circle me-1"></i> Update Purchase Order
                </button>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = {{ count($itemsToDisplay) }};
    const itemsContainer = document.getElementById('itemsContainer');
    const addRowBtn = document.getElementById('addRowBtn');

    function calculateTotals() {
        let subtotal = 0;
        let totalTax = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
            const taxPct = parseFloat(row.querySelector('.tax-percent-input').value) || 0;

            const rowSubtotal = qty * cost;
            const rowTax = (rowSubtotal * taxPct) / 100;
            const rowTotalTax = rowSubtotal + rowTax;

            subtotal += rowSubtotal;
            totalTax += rowTax;

            row.querySelector('.row-subtotal-input').value = '₹' + rowSubtotal.toFixed(2);
            row.querySelector('.row-total-tax-input').value = '₹' + rowTotalTax.toFixed(2);
        });

        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
        const grandTotal = subtotal + totalTax - discount + shipping;

        document.getElementById('displaySubtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('displayTax').textContent = '₹' + totalTax.toFixed(2);
        document.getElementById('displayGrandTotal').textContent = '₹' + Math.max(0, grandTotal).toFixed(2);
    }

    function initRowEvents(row) {
        const catSelect = row.querySelector('.category-select');
        const prodSelect = row.querySelector('.product-select');

        function filterProducts() {
            const catId = catSelect.value;
            let firstMatched = false;
            Array.from(prodSelect.options).forEach(opt => {
                if (!opt.value) return;
                if (!catId || opt.dataset.category === catId) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) opt.selected = false;
                }
            });
        }

        function updateProductMeta() {
            const selected = prodSelect.options[prodSelect.selectedIndex];
            if (selected && selected.value) {
                row.querySelector('.hsn-input').value = selected.dataset.hsn || 'N/A';
                row.querySelector('.unit-input').value = selected.dataset.unit || 'Unit';
                if (!row.querySelector('.cost-input').value) {
                    row.querySelector('.cost-input').value = selected.dataset.cost || '0.00';
                }
                if (!row.querySelector('.tax-percent-input').value) {
                    row.querySelector('.tax-percent-input').value = selected.dataset.tax || '0.00';
                }
                if (selected.dataset.category && !catSelect.value) {
                    catSelect.value = selected.dataset.category;
                }
            } else {
                row.querySelector('.hsn-input').value = '';
                row.querySelector('.unit-input').value = '';
            }
            calculateTotals();
        }

        catSelect.addEventListener('change', filterProducts);
        prodSelect.addEventListener('change', updateProductMeta);

        row.querySelectorAll('.qty-input, .cost-input, .tax-percent-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-row-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('Purchase order must contain at least one line item.');
            }
        });

        updateProductMeta();
    }

    document.querySelectorAll('.item-row').forEach(initRowEvents);

    addRowBtn.addEventListener('click', function() {
        const firstRow = document.querySelector('.item-row');
        const newRow = firstRow.cloneNode(true);

        newRow.querySelectorAll('input').forEach(input => {
            if (input.classList.contains('qty-input')) input.value = 1;
            else if (input.classList.contains('tax-percent-input')) input.value = '0.00';
            else if (!input.readOnly) input.value = '';
        });

        newRow.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
            if (select.classList.contains('product-select')) {
                select.name = `products[${rowIndex}][id]`;
            }
        });

        newRow.querySelector('.qty-input').name = `products[${rowIndex}][qty]`;
        newRow.querySelector('.cost-input').name = `products[${rowIndex}][cost]`;
        newRow.querySelector('.tax-percent-input').name = `products[${rowIndex}][tax_percent]`;

        itemsContainer.appendChild(newRow);
        initRowEvents(newRow);
        rowIndex++;
    });

    document.getElementById('discount_amount').addEventListener('input', calculateTotals);
    document.getElementById('shipping_cost').addEventListener('input', calculateTotals);

    document.getElementById('payment_status').addEventListener('change', function() {
        const paidInput = document.getElementById('paid_amount');
        if (this.value === 'paid') {
            const grandTotalText = document.getElementById('displayGrandTotal').textContent.replace('₹', '');
            paidInput.value = parseFloat(grandTotalText) || 0;
        } else if (this.value === 'unpaid') {
            paidInput.value = '0';
        }
    });

    calculateTotals();
});
</script>
@endpush
@endsection
