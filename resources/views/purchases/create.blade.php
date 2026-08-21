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
        <div class="col-12 col-xl-9 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Purchase Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <label for="supplier_id" class="form-label fw-semibold text-dark">Supplier / Vendor <span class="text-danger">*</span></label>
                        <select class="form-select @error('supplier_id') is-invalid @enderror" id="supplier_id" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name') }}" placeholder="e.g. PRJ-METRO-001" required>
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="invoice_number" class="form-label fw-semibold text-dark">Invoice No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $autoInv) }}" required>
                        @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-2">
                        <label for="purchase_date" class="form-label fw-semibold text-dark">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="purchase_date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    </div>
                </div>

                <!-- Products Selector Line Items -->
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
                                <th style="min-width: 140px;">Category</th>
                                <th style="min-width: 200px;">Product Item (Brand)</th>
                                <th style="min-width: 100px;">HSN Code</th>
                                <th style="min-width: 90px;">Unit</th>
                                <th style="min-width: 80px;">Qty</th>
                                <th style="min-width: 110px;">Unit Cost (₹)</th>
                                <th style="min-width: 90px;">Tax (%)</th>
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
                                            <select class="form-select form-select-sm category-select">
                                                <option value="">All Categories</option>
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
                                            <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Unit">
                                        </td>
                                        <td>
                                            <input type="number" name="products[{{ $index }}][qty]" class="form-control form-control-sm qty-input" min="1" value="{{ $oldProduct['qty'] ?? 1 }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="products[{{ $index }}][cost]" class="form-control form-control-sm cost-input" value="{{ $oldProduct['cost'] ?? '0.00' }}" required>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="products[{{ $index }}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="{{ $oldProduct['tax_percent'] ?? '0.00' }}" min="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm row-subtotal-input bg-light" readonly value="₹0.00">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm row-total-tax-input bg-light fw-bold text-dark" readonly value="₹0.00">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-light border text-danger remove-row-btn"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <tr class="item-row">
                                    <td>
                                        <select class="form-select form-select-sm category-select">
                                            <option value="">All Categories</option>
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select name="products[0][id]" class="form-select form-select-sm product-select" required>
                                            <option value="">Select Product...</option>
                                            @foreach($products as $prod)
                                                <option value="{{ $prod->id }}" 
                                                        data-category="{{ $prod->category_id }}" 
                                                        data-hsn="{{ $prod->hsn_code ?? '' }}" 
                                                        data-unit="{{ $prod->unit }}" 
                                                        data-cost="{{ $prod->cost_price }}" 
                                                        data-tax="{{ $prod->tax_percent }}">
                                                    {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Unit">
                                    </td>
                                    <td>
                                        <input type="number" name="products[0][qty]" class="form-control form-control-sm qty-input" min="1" value="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="products[0][cost]" class="form-control form-control-sm cost-input" value="0.00" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="products[0][tax_percent]" class="form-control form-control-sm tax-percent-input" value="0.00" min="0">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm row-subtotal-input bg-light" readonly value="₹0.00">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm row-total-tax-input bg-light fw-bold text-dark" readonly value="₹0.00">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-light border text-danger remove-row-btn"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Summary & Totals Column -->
        <div class="col-12 col-xl-3 col-lg-4">
            <div class="card card-custom border-0 p-4">
                <h5 class="fw-bold font-outfit text-dark mb-3">Order Summary</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold font-outfit text-dark fs-6" id="subtotalDisplay">₹0.00</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Tax:</span>
                    <span class="fw-bold font-outfit text-dark fs-6" id="taxDisplay">₹0.00</span>
                </div>

                <div class="mb-3">
                    <label for="discount_amount" class="form-label small fw-semibold">Discount (₹)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', '0.00') }}">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping Cost (₹)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', '0.00') }}">
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark fs-5">Grand Total:</span>
                    <span class="fw-bold font-outfit text-primary fs-3" id="grandTotalDisplay">₹0.00</span>
                </div>

                <div class="mb-3">
                    <label for="paid_amount" class="form-label small fw-semibold text-dark">Amount Paid (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', '0.00') }}" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label small fw-semibold">Notes / Vendor References</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="e.g. Fire safety stock received via Flame Express Logistics">{{ old('notes') }}</textarea>
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
        let rowCount = {{ old('products') ? count(old('products')) : 1 }};

        const categoryOptions = `@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach`;
        const productsOptions = `@foreach($products as $prod)<option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" data-hsn="{{ $prod->hsn_code ?? '' }}" data-unit="{{ $prod->unit }}" data-cost="{{ $prod->cost_price }}" data-tax="{{ $prod->tax_percent }}">{{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</option>@endforeach`;

        function filterProductsInRow(row) {
            const catSelect = row.querySelector('.category-select');
            const prodSelect = row.querySelector('.product-select');
            const selectedCatId = catSelect.value;

            let hasSelectedValid = false;
            Array.from(prodSelect.options).forEach(option => {
                if (!option.value) return; // Keep "Select Product..." option
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
                row.querySelector('.cost-input').value = '0.00';
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
                const cost = selectedOption.getAttribute('data-cost') || 0;
                const tax = selectedOption.getAttribute('data-tax') || 0;

                const catSelect = row.querySelector('.category-select');
                if (catSelect && catId) {
                    catSelect.value = catId;
                }

                row.querySelector('.hsn-input').value = hsn;
                row.querySelector('.unit-input').value = unit;
                row.querySelector('.cost-input').value = parseFloat(cost).toFixed(2);
                row.querySelector('.tax-percent-input').value = parseFloat(tax).toFixed(2);
            } else {
                row.querySelector('.hsn-input').value = '';
                row.querySelector('.unit-input').value = '';
                row.querySelector('.cost-input').value = '0.00';
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
                    <select class="form-select form-select-sm category-select">
                        <option value="">All Categories</option>
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
                    <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Unit">
                </td>
                <td>
                    <input type="number" name="products[${rowCount}][qty]" class="form-control form-control-sm qty-input" min="1" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="products[${rowCount}][cost]" class="form-control form-control-sm cost-input" value="0.00" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="products[${rowCount}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="0.00" min="0">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm row-subtotal-input bg-light" readonly value="₹0.00">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm row-total-tax-input bg-light fw-bold text-dark" readonly value="₹0.00">
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-light border text-danger remove-row-btn"><i class="bi bi-trash"></i></button>
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
                e.target.classList.contains('cost-input') || 
                e.target.classList.contains('tax-percent-input')) {
                calculateTotals();
            }
        });

        document.getElementById('discount_amount').addEventListener('input', calculateTotals);
        document.getElementById('shipping_cost').addEventListener('input', calculateTotals);

        function calculateTotals() {
            let subtotal = 0;
            let totalTax = 0;

            const rows = document.querySelectorAll('.item-row');
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const cost = parseFloat(row.querySelector('.cost-input').value) || 0;
                const taxPercent = parseFloat(row.querySelector('.tax-percent-input').value) || 0;

                const rowSubtotal = qty * cost;
                const rowTax = rowSubtotal * (taxPercent / 100);
                const rowTotalTax = rowSubtotal + rowTax;

                row.querySelector('.row-subtotal-input').value = '₹' + rowSubtotal.toFixed(2);
                row.querySelector('.row-total-tax-input').value = '₹' + rowTotalTax.toFixed(2);

                subtotal += rowSubtotal;
                totalTax += rowTax;
            });

            const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
            const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
            const grandTotal = Math.max(0, subtotal + totalTax - discount + shipping);

            document.getElementById('subtotalDisplay').textContent = '₹' + subtotal.toFixed(2);
            document.getElementById('taxDisplay').textContent = '₹' + totalTax.toFixed(2);
            document.getElementById('grandTotalDisplay').textContent = '₹' + grandTotal.toFixed(2);
            document.getElementById('paid_amount').value = grandTotal.toFixed(2);
        }

        // Initial auto-fill for old input rows if redirected back with error
        document.querySelectorAll('.item-row').forEach(row => {
            const prodSelect = row.querySelector('.product-select');
            if (prodSelect && prodSelect.value) {
                handleProductChange(row);
            }
        });

        // Initial calculation
        calculateTotals();
    });
</script>
@endpush

