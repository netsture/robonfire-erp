@extends('layouts.app')

@section('title', 'New Returnable Entry')
@section('page_title', 'Returnable Entry')
@section('page_subtitle', 'Record returned products from customer/project and update inventory stock')

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
    <a href="{{ route('returnable.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Returnable Entry
    </a>
@endsection

@section('content')
<form action="{{ route('returnable.store') }}" method="POST" id="returnForm">
    @csrf
    
    <div class="row g-3">
        <!-- Main Form Column -->
        <div class="col-12 col-xl-9 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Customer & Return Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="customer_id" class="form-label fw-semibold text-dark">Select Customer Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Choose Customer Name</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="return_number" class="form-label fw-semibold text-dark">Challan No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('return_number') is-invalid @enderror" id="return_number" name="return_number" value="{{ old('return_number') }}" required>
                        @error('return_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="return_date" class="form-label fw-semibold text-dark">Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="return_date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name') }}" placeholder="Enter Project Name" required>
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="return_reason" class="form-label fw-semibold text-dark">Reason for Return <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('return_reason') is-invalid @enderror" id="return_reason" name="return_reason" value="{{ old('return_reason') }}" placeholder="Enter Reason for Return" required>
                        @error('return_reason') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Products Selector Line Items -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold font-outfit text-dark mb-0">Return Line Items</h6>
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
                                <th style="min-width: 100px;">Selling Price(₹)</th>
                                <th style="min-width: 80px;">Tax (%)</th>
                                <th style="min-width: 110px;">Total (₹)</th>
                                <th style="min-width: 110px;">Total w/ Tax</th>
                                <th style="width: 50px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            @if(old('products'))
                                @foreach(old('products') as $index => $oldProduct)
                                    @php
                                        $selProd = $products->firstWhere('id', $oldProduct['id'] ?? null);
                                        $selCatId = $selProd ? $selProd->category_id : null;
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
                                                            {{ (isset($oldProduct['id']) && $oldProduct['id'] == $prod->id) ? 'selected' : '' }}>
                                                        {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type">
                                        </td>
                                        <td>
                                            <input type="number" name="products[{{ $index }}][qty]" class="form-control form-control-sm qty-input" min="1" value="{{ $oldProduct['qty'] ?? 1 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="products[{{ $index }}][price]" class="form-control form-control-sm price-input" value="{{ $oldProduct['price'] ?? '' }}" placeholder="e.g. 150.00" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" min="0" name="products[{{ $index }}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="{{ $oldProduct['tax_percent'] ?? 0 }}" placeholder="0.00">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm line-total bg-light fw-bold" readonly value="0.00">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm line-total-tax bg-light fw-bold text-primary" readonly value="0.00">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="item-row">
                                    <td>
                                        <select class="form-select form-select-sm category-select" required>
                                            <option value="">Select Category...</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="products[0][id]" class="form-select form-select-sm product-select" required>
                                            <option value="">Select Product...</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" data-hsn="{{ $prod->hsn_code ?? '' }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->selling_price }}" data-tax="{{ $prod->tax_percent }}">
                                                    {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type">
                                    </td>
                                    <td>
                                        <input type="number" name="products[0][qty]" class="form-control form-control-sm qty-input" min="1" value="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="products[0][price]" class="form-control form-control-sm price-input" placeholder="e.g. 150.00" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" name="products[0][tax_percent]" class="form-control form-control-sm tax-percent-input" value="0.00" placeholder="0.00">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm line-total bg-light fw-bold" readonly value="0.00">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm line-total-tax bg-light fw-bold text-primary" readonly value="0.00">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <label for="notes" class="form-label fw-semibold text-dark">Notes / Return Condition Details</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Enter return material reason, condition or remarks...">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Column -->
        <div class="col-12 col-xl-3 col-lg-4">
            <div class="card card-custom border-0 p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold font-outfit text-dark mb-3">Return Summary</h5>

                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Items Subtotal:</span>
                    <span class="fw-bold text-dark" id="displaySubtotal">₹0.00</span>
                </div>

                <div class="mb-3">
                    <label for="discount_amount" class="form-label small text-muted mb-1">Discount Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', '0.00') }}">
                </div>

                <div class="mb-3">
                    <label for="tax_amount" class="form-label small text-muted mb-1">Total Tax Amount (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', '0.00') }}" placeholder="Auto calculated if empty">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small text-muted mb-1">Freight / Transport Charges (₹)</label>
                    <input type="number" step="0.01" min="0" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', '0.00') }}">
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between mb-4">
                    <span class="fw-bold text-dark fs-5">Grand Total:</span>
                    <span class="fw-bold text-primary fs-4" id="displayGrandTotal">₹0.00</span>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill font-outfit fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Save Return Entry
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('customer_id')) {
            new TomSelect('#customer_id', {
                create: false,
                placeholder: 'Choose Customer Name...',
                plugins: ['dropdown_input']
            });
        }

        let rowCount = document.querySelectorAll('.item-row').length;
        const categoryOptions = `@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach`;
        const productsOptions = `@foreach($products as $prod)<option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" data-hsn="{{ $prod->hsn_code ?? '' }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->selling_price }}" data-tax="{{ $prod->tax_percent }}">{{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</option>@endforeach`;

        function filterProductsInRow(row) {
            const catSelect = row.querySelector('.category-select');
            const prodSelect = row.querySelector('.product-select');
            const selectedCatId = catSelect.value;

            let hasSelectedValid = false;
            Array.from(prodSelect.options).forEach(option => {
                if (!option.value) return;
                const optCatId = option.getAttribute('data-category');
                if (!selectedCatId || optCatId === selectedCatId) {
                    option.hidden = false;
                    option.disabled = false;
                    if (option.selected) hasSelectedValid = true;
                } else {
                    option.hidden = true;
                    option.disabled = true;
                }
            });

            if (!hasSelectedValid && prodSelect.value !== '') {
                prodSelect.value = '';
                row.querySelector('.hsn-input').value = '';
                row.querySelector('.unit-input').value = '';
                row.querySelector('.price-input').value = '0.00';
                row.querySelector('.tax-percent-input').value = '0.00';
                calculateTotals();
            }
        }

        function handleProductChange(row) {
            const prodSelect = row.querySelector('.product-select');
            const selectedOption = prodSelect.options[prodSelect.selectedIndex];

            if (selectedOption && selectedOption.value) {
                const catId = selectedOption.getAttribute('data-category') || '';
                const hsn = selectedOption.getAttribute('data-hsn') || '';
                const unit = selectedOption.getAttribute('data-unit') || '';
                const price = selectedOption.getAttribute('data-price') || 0;
                const tax = selectedOption.getAttribute('data-tax') || 0;

                const catSelect = row.querySelector('.category-select');
                if (catSelect && catId) {
                    catSelect.value = catId;
                }

                row.querySelector('.hsn-input').value = hsn;
                row.querySelector('.unit-input').value = unit;
                row.querySelector('.price-input').value = parseFloat(price).toFixed(2);
                row.querySelector('.tax-percent-input').value = parseFloat(tax).toFixed(2);
            } else {
                row.querySelector('.hsn-input').value = '';
                row.querySelector('.unit-input').value = '';
                row.querySelector('.price-input').value = '0.00';
                row.querySelector('.tax-percent-input').value = '0.00';
            }
            calculateTotals();
        }

        document.getElementById('addRowBtn').addEventListener('click', function () {
            const container = document.getElementById('itemsContainer');
            const newRow = document.createElement('tr');
            newRow.className = 'item-row';
            newRow.innerHTML = `
                <td>
                    <select class="form-select form-select-sm category-select" required>
                        <option value="">Select Category...</option>
                        ${categoryOptions}
                    </select>
                </td>
                <td>
                    <select name="products[${rowCount}][id]" class="form-select form-select-sm product-select" required>
                        <option value="">Select Product...</option>
                        ${productsOptions}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type">
                </td>
                <td>
                    <input type="number" name="products[${rowCount}][qty]" class="form-control form-control-sm qty-input" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="products[${rowCount}][price]" class="form-control form-control-sm price-input" placeholder="e.g. 150.00" required>
                </td>
                <td>
                    <input type="number" step="0.01" min="0" name="products[${rowCount}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="0.00" placeholder="0.00">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm line-total bg-light fw-bold" readonly value="0.00">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm line-total-tax bg-light fw-bold text-primary" readonly value="0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="bi bi-trash"></i></button>
                </td>
            `;
            container.appendChild(newRow);
            rowCount++;
            calculateTotals();
        });

        document.getElementById('itemsContainer').addEventListener('change', function(e) {
            const row = e.target.closest('.item-row');
            if (!row) return;

            if (e.target.classList.contains('category-select')) {
                filterProductsInRow(row);
            } else if (e.target.classList.contains('product-select')) {
                handleProductChange(row);
            }
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

        document.getElementById('itemsContainer').addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input') || 
                e.target.classList.contains('price-input') || 
                e.target.classList.contains('tax-percent-input')) {
                calculateTotals();
            }
        });

        document.getElementById('discount_amount').addEventListener('input', calculateTotals);
        document.getElementById('shipping_cost').addEventListener('input', calculateTotals);
        document.getElementById('tax_amount').addEventListener('input', calculateTotals);

        function calculateTotals() {
            let subtotal = 0;
            let calculatedTax = 0;

            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
                const price = parseFloat(row.querySelector('.price-input')?.value || 0);
                const taxPercent = parseFloat(row.querySelector('.tax-percent-input')?.value || 0);

                const lineTotal = qty * price;
                const lineTax = (lineTotal * taxPercent) / 100;
                const lineTotalWithTax = lineTotal + lineTax;

                const lineTotalEl = row.querySelector('.line-total');
                const lineTotalTaxEl = row.querySelector('.line-total-tax');

                if (lineTotalEl) lineTotalEl.value = lineTotal.toFixed(2);
                if (lineTotalTaxEl) lineTotalTaxEl.value = lineTotalWithTax.toFixed(2);

                subtotal += lineTotal;
                calculatedTax += lineTax;
            });

            const discount = parseFloat(document.getElementById('discount_amount').value || 0);
            const shipping = parseFloat(document.getElementById('shipping_cost').value || 0);
            
            const userTaxInput = document.getElementById('tax_amount');
            let finalTax = calculatedTax;
            if (userTaxInput && userTaxInput.value !== '' && userTaxInput.dataset.userEdited === 'true') {
                finalTax = parseFloat(userTaxInput.value || 0);
            } else if (userTaxInput) {
                userTaxInput.value = calculatedTax.toFixed(2);
            }

            const grandTotal = subtotal - discount + finalTax + shipping;

            document.getElementById('displaySubtotal').innerText = '₹' + subtotal.toFixed(2);
            document.getElementById('displayGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
        }

        document.getElementById('tax_amount').addEventListener('change', function() {
            this.dataset.userEdited = 'true';
            calculateTotals();
        });

        // Initial auto-fill for old input rows if redirected back with error
        document.querySelectorAll('.item-row').forEach(row => {
            const prodSelect = row.querySelector('.product-select');
            if (prodSelect && prodSelect.value) {
                handleProductChange(row);
            }
        });

        calculateTotals();
    });
</script>
@endpush
