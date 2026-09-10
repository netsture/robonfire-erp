@extends('layouts.app')

@section('title', 'Edit Returnable Entry')
@section('page_title', 'Edit Returnable Entry: #' . $returnableMaterial->return_number)
@section('page_subtitle', 'Modify returnable entry details and line items with automatic inventory recalculation')

@section('header_actions')
    <a href="{{ route('returnable.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Returnable Entry
    </a>
@endsection

@section('content')
<form action="{{ route('returnable.update', $returnableMaterial) }}" method="POST" id="returnableForm">
    @csrf
    @method('PUT')
    
    <div class="row g-3">
        <!-- Main Form Column -->
        <div class="col-12 col-xl-9 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Edit Returnable Material Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="customer_id" class="form-label fw-semibold text-dark">Select Customer Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Select Customer Name</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $returnableMaterial->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="return_number" class="form-label fw-semibold text-dark">Return No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('return_number') is-invalid @enderror" id="return_number" name="return_number" value="{{ old('return_number', $returnableMaterial->return_number) }}" required>
                        @error('return_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="return_date" class="form-label fw-semibold text-dark">Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="return_date" name="return_date" value="{{ old('return_date', $returnableMaterial->return_date) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name', $returnableMaterial->project_name) }}" placeholder="Enter Project Name" required>
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="return_reason" class="form-label fw-semibold text-dark">Reason for Return <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('return_reason') is-invalid @enderror" id="return_reason" name="return_reason" value="{{ old('return_reason', $returnableMaterial->return_reason) }}" placeholder="Enter Reason for Return" required>
                        @error('return_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Line Items Table -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold font-outfit text-dark mb-0">Return Line Items</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill" id="addRowBtn">
                        <i class="bi bi-plus-circle me-1"></i> Add Return Line Item
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
                                <th style="min-width: 100px;">Unit Price(₹)</th>
                                <th style="min-width: 110px;">Subtotal (₹)</th>
                                <th style="width: 50px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            @php
                                $itemsToDisplay = old('products') ?? $returnableMaterial->items->toArray();
                            @endphp
                            @foreach($itemsToDisplay as $index => $item)
                                @php
                                    $prodId = $item['product_id'] ?? ($item['id'] ?? null);
                                    $selProd = $products->firstWhere('id', $prodId);
                                    $selCatId = $selProd ? $selProd->category_id : null;
                                    $qty = $item['quantity'] ?? ($item['qty'] ?? 1);
                                    $price = $item['unit_price'] ?? ($item['price'] ?? ($selProd ? $selProd->selling_price : 0));
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
                                                        data-price="{{ $prod->selling_price }}"
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
                                    <td><input type="number" step="0.01" min="0.00" name="products[{{ $index }}][unit_price]" class="form-control form-control-sm price-input" value="{{ $price }}" required></td>
                                    <td><input type="text" class="form-control form-control-sm row-subtotal-input bg-light fw-bold text-dark" readonly value="₹0.00"></td>
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
                <h5 class="fw-bold font-outfit text-dark mb-3">Return Summary</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold font-outfit text-dark fs-6" id="displaySubtotal">₹0.00</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Tax:</span>
                    <span class="fw-bold font-outfit text-dark fs-6" id="displayTax">₹0.00</span>
                </div>
                <input type="hidden" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', $returnableMaterial->tax_amount) }}">

                <div class="mb-3">
                    <label for="discount_amount" class="form-label small fw-semibold">Discount Amount (₹)</label>
                    <input type="number" min="0" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="{{ (float)old('discount_amount', $returnableMaterial->discount_amount) == 0 ? 0 : old('discount_amount', $returnableMaterial->discount_amount) }}">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping Cost (₹)</label>
                    <input type="number" min="0" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="{{ (float)old('shipping_cost', $returnableMaterial->shipping_cost) == 0 ? 0 : old('shipping_cost', $returnableMaterial->shipping_cost) }}">
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark">Grand Total:</span>
                    <span class="fs-4 fw-bold text-primary" id="displayGrandTotal">₹0.00</span>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold text-dark">Return Order     Notes / Remarks</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="3" placeholder="Optional remarks...">{{ old('notes', $returnableMaterial->notes) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 font-outfit fw-semibold">
                    <i class="bi bi-check-circle me-1"></i> Update Return Entry
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
        let calculatedTax = 0;

        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
            const price = parseFloat(row.querySelector('.price-input').value) || 0;
            const prodSelect = row.querySelector('.product-select');
            const selectedOpt = prodSelect ? prodSelect.options[prodSelect.selectedIndex] : null;
            const taxPercent = selectedOpt ? parseFloat(selectedOpt.dataset.tax || 0) : 0;

            const rowSubtotal = qty * price;
            const rowTax = (rowSubtotal * taxPercent) / 100;

            subtotal += rowSubtotal;
            calculatedTax += rowTax;

            row.querySelector('.row-subtotal-input').value = '₹' + rowSubtotal.toFixed(2);
        });

        const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
        const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;

        const taxInput = document.getElementById('tax_amount');
        if (taxInput) {
            taxInput.value = calculatedTax.toFixed(2);
        }

        const grandTotal = Math.max(0, subtotal - discount + calculatedTax + shipping);

        document.getElementById('displaySubtotal').textContent = '₹' + subtotal.toFixed(2);
        document.getElementById('displayTax').textContent = '₹' + calculatedTax.toFixed(2);
        document.getElementById('displayGrandTotal').textContent = '₹' + grandTotal.toFixed(2);
    }

    function initRowEvents(row) {
        const catSelect = row.querySelector('.category-select');
        const prodSelect = row.querySelector('.product-select');

        function filterProducts() {
            const catId = catSelect.value;
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
                if (!row.querySelector('.price-input').value) {
                    row.querySelector('.price-input').value = selected.dataset.price || '0.00';
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

        row.querySelectorAll('.qty-input, .price-input').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        row.querySelector('.remove-row-btn').addEventListener('click', function() {
            if (document.querySelectorAll('.item-row').length > 1) {
                row.remove();
                calculateTotals();
            } else {
                alert('Return entry must contain at least one line item.');
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
            else if (!input.readOnly) input.value = '';
        });

        newRow.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
            if (select.classList.contains('product-select')) {
                select.name = `products[${rowIndex}][id]`;
            }
        });

        newRow.querySelector('.qty-input').name = `products[${rowIndex}][qty]`;
        newRow.querySelector('.price-input').name = `products[${rowIndex}][unit_price]`;

        itemsContainer.appendChild(newRow);
        initRowEvents(newRow);
        rowIndex++;
    });

    document.getElementById('discount_amount').addEventListener('input', calculateTotals);
    document.getElementById('shipping_cost').addEventListener('input', calculateTotals);

    calculateTotals();
});
</script>
@endpush
@endsection
