@extends('layouts.app')

@section('title', 'Edit Returnable Entry')
@section('page_title', 'Edit Returnable Entry: #' . $returnableMaterial->return_number)
@section('page_subtitle', 'Modify returnable entry details and line items with automatic inventory recalculation')

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
    .table .ts-control {
        border-radius: 0.25rem !important;
        padding: 0.28rem 0.65rem !important;
        font-size: 0.875rem !important;
        min-height: 31px !important;
        line-height: 1.4 !important;
    }
    .table .ts-wrapper {
        width: 100% !important;
    }
    .ts-dropdown .option {
        padding: 0.35rem 0.75rem !important;
        font-size: 0.875rem !important;
    }
</style>
@endpush

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
                        <select class="form-select @error('project_name') is-invalid @enderror" id="project_name" name="project_name" required>
                            <option value="">Select, Search or Type Project Name</option>
                            @foreach($projectNames as $proj)
                                <option value="{{ $proj }}" {{ old('project_name', $returnableMaterial->project_name) == $proj ? 'selected' : '' }}>{{ $proj }}</option>
                            @endforeach
                            @if($returnableMaterial->project_name && !$projectNames->contains($returnableMaterial->project_name))
                                <option value="{{ $returnableMaterial->project_name }}" selected>{{ $returnableMaterial->project_name }}</option>
                            @endif
                        </select>
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
                                <th style="min-width: 270px;">Product Item <br>(Brand) / Unique ID</th>
                                <th style="min-width: 100px;">HSN Code</th>
                                <th style="min-width: 60px;">Type</th>
                                <th style="min-width: 80px;">Qty</th>
                                <th style="min-width: 110px;">Selling Price(₹)</th>
                                <th style="min-width: 90px;">Tax (%)</th>
                                <th style="min-width: 110px;">Total (₹)</th>
                                <th style="min-width: 110px;">Total w/ Tax</th>
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
                                    $price = $item['price'] ?? ($item['unit_price'] ?? ($selProd ? $selProd->selling_price : 0));
                                    $taxPercent = $item['tax_percent'] ?? ($selProd ? $selProd->tax_percent : 0);
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
                                                        data-identifier="{{ $prod->product_identifier ?? '' }}"
                                                        data-hsn="{{ $prod->hsn_code ?? '' }}" 
                                                        data-unit="{{ $prod->unit }}" 
                                                        data-price="{{ $prod->selling_price }}"
                                                        data-tax="{{ $prod->tax_percent }}"
                                                        {{ $prodId == $prod->id ? 'selected' : '' }}>
                                                    {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}{{ $prod->product_identifier ? ' ['.$prod->product_identifier.']' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN" value="{{ $selProd->hsn_code ?? '' }}"></td>
                                    <td><input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type" value="{{ $selProd->unit ?? '' }}"></td>
                                    <td><input type="number" name="products[{{ $index }}][qty]" class="form-control form-control-sm qty-input" min="1" value="{{ $qty }}" required></td>
                                    <td><input type="number" step="any" min="0" name="products[{{ $index }}][price]" class="form-control form-control-sm price-input" value="{{ $price }}" placeholder="e.g. 150" required></td>
                                    <td><input type="number" step="any" min="0" name="products[{{ $index }}][tax_percent]" class="form-control form-control-sm tax-percent-input bg-light" value="{{ $taxPercent }}" readonly tabindex="-1" style="cursor: not-allowed;" placeholder="0"></td>
                                    <td><input type="text" class="form-control form-control-sm line-total bg-light fw-bold" readonly value="0.00"></td>
                                    <td><input type="text" class="form-control form-control-sm line-total-tax bg-light fw-bold text-primary" readonly value="0.00"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="bi bi-trash"></i></button>
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
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('customer_id')) {
        new TomSelect('#customer_id', {
            create: false,
            placeholder: 'Select or Search Customer Name',
            plugins: ['dropdown_input']
        });
    }

    if (document.getElementById('project_name')) {
        new TomSelect('#project_name', {
            create: true,
            createOnBlur: true,
            placeholder: 'Select, Search or Type Project Name',
            plugins: ['dropdown_input']
        });
    }

    const allProducts = [
        @foreach($products as $prod)
        {
            id: "{{ $prod->id }}",
            name: @json($prod->name),
            brand: @json($prod->brand ? $prod->brand->name : ''),
            identifier: @json($prod->product_identifier ?? ''),
            text: @json($prod->name . ($prod->brand ? ' (' . $prod->brand->name . ')' : '') . ($prod->product_identifier ? ' [' . $prod->product_identifier . ']' : '')),
            category_id: "{{ $prod->category_id ?? '' }}",
            hsn: @json($prod->hsn_code ?? ''),
            unit: @json($prod->unit ?? ''),
            price: {{ (float)($prod->selling_price ?? 0) }},
            tax: {{ (float)($prod->tax_percent ?? 0) }}
        },
        @endforeach
    ];

    const productsMap = {};
    allProducts.forEach(p => {
        productsMap[p.id] = p;
    });

    let rowCount = {{ count($itemsToDisplay) }};
    const itemsContainer = document.getElementById('itemsContainer');
    const addRowBtn = document.getElementById('addRowBtn');
    const categoryOptions = `@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach`;
    const productsOptions = `@foreach($products as $prod)<option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" data-identifier="{{ $prod->product_identifier ?? '' }}" data-hsn="{{ $prod->hsn_code ?? '' }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->selling_price }}" data-tax="{{ $prod->tax_percent }}">{{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}{{ $prod->product_identifier ? ' ['.$prod->product_identifier.']' : '' }}</option>@endforeach`;

    function syncSelectOption(selectEl, value, text) {
        if (!selectEl) return;
        let opt = selectEl.querySelector('option[value="' + value + '"]');
        if (!opt && value) {
            opt = document.createElement('option');
            opt.value = value;
            opt.textContent = text || value;
            selectEl.appendChild(opt);
        }
        selectEl.value = value;
        if (opt) {
            opt.selected = true;
        }
    }

    function initProductTomSelect(prodSelect) {
        if (!prodSelect || prodSelect.tomselect) return prodSelect ? prodSelect.tomselect : null;

        const row = prodSelect.closest('.item-row');
        const catSelect = row ? row.querySelector('.category-select') : null;
        const selectedCatId = catSelect ? catSelect.value : '';
        const initialVal = prodSelect.value || '';

        prodSelect.required = false;

        const filteredOptions = allProducts.filter(p => !selectedCatId || p.category_id === selectedCatId);

        const ts = new TomSelect(prodSelect, {
            valueField: 'id',
            labelField: 'text',
            searchField: ['text', 'name', 'brand', 'identifier'],
            options: filteredOptions,
            items: initialVal ? [initialVal] : [],
            create: false,
            placeholder: 'Search or Select Product...',
            plugins: ['dropdown_input'],
            dropdownParent: 'body',
            maxOptions: null,
            closeAfterSelect: true,
            sortField: {
                field: 'text',
                direction: 'asc'
            },
            render: {
                option: function(data, escape) {
                    return '<div class="py-1 px-2 d-flex justify-content-between align-items-center gap-2">' +
                        '<div>' +
                            '<span class="fw-medium text-dark">' + escape(data.name) + '</span>' +
                            (data.brand ? ' <span class="text-muted small">(' + escape(data.brand) + ')</span>' : '') +
                        '</div>' +
                        (data.identifier ? '<span class="badge text-dark fw-bold border border-warning font-monospace" style="background-color: #fef08a; color: #713f12 !important; font-size: 0.75rem;"><i class="bi bi-qr-code me-1"></i>' + escape(data.identifier) + '</span>' : '') +
                        '</div>';
                },
                item: function(data, escape) {
                    return '<div class="d-flex align-items-center gap-1 flex-wrap">' +
                        '<span>' + escape(data.name) + '</span>' +
                        (data.brand ? '<span class="text-muted small">(' + escape(data.brand) + ')</span>' : '') +
                        (data.identifier ? '<span class="badge text-dark fw-bold border border-warning font-monospace py-0 px-1 ms-1" style="background-color: #fef08a; color: #713f12 !important; font-size: 0.72rem;"><i class="bi bi-qr-code me-1"></i>' + escape(data.identifier) + '</span>' : '') +
                        '</div>';
                }
            },
            onChange: function(value) {
                const prodObj = productsMap[value];
                syncSelectOption(prodSelect, value, prodObj ? prodObj.text : value);
                handleProductChange(row, value, true);
            }
        });

        if (initialVal) {
            const prodObj = productsMap[initialVal];
            syncSelectOption(prodSelect, initialVal, prodObj ? prodObj.text : initialVal);
        }

        return ts;
    }

    function filterProductsInRow(row) {
        const catSelect = row.querySelector('.category-select');
        const prodSelect = row.querySelector('.product-select');
        if (!catSelect || !prodSelect) return;

        const selectedCatId = catSelect.value;
        const ts = prodSelect.tomselect;

        if (ts) {
            const currentVal = ts.getValue();
            ts.clearOptions();

            let hasSelectedValid = false;
            allProducts.forEach(prod => {
                if (!selectedCatId || prod.category_id === selectedCatId) {
                    ts.addOption(prod);
                    if (prod.id === currentVal) {
                        hasSelectedValid = true;
                    }
                }
            });

            if (currentVal && hasSelectedValid) {
                ts.setValue(currentVal, true);
                const prodObj = productsMap[currentVal];
                syncSelectOption(prodSelect, currentVal, prodObj ? prodObj.text : currentVal);
            } else {
                ts.setValue('', true);
                syncSelectOption(prodSelect, '', '');
                const hsnInput = row.querySelector('.hsn-input');
                const unitInput = row.querySelector('.unit-input');
                const priceInput = row.querySelector('.price-input');
                const taxInput = row.querySelector('.tax-percent-input');
                if (hsnInput) hsnInput.value = '';
                if (unitInput) unitInput.value = '';
                if (priceInput) priceInput.value = '0.00';
                if (taxInput) taxInput.value = '0.00';
                calculateTotals();
            }
            ts.refreshOptions(false);
        }
    }

    function handleProductChange(row, explicitVal, isUserChange = true) {
        const prodSelect = row.querySelector('.product-select');
        const ts = prodSelect ? prodSelect.tomselect : null;
        const prodId = (explicitVal !== undefined && explicitVal !== null && explicitVal !== '') 
            ? explicitVal 
            : (ts ? ts.getValue() : (prodSelect ? prodSelect.value : ''));

        if (prodId && productsMap[prodId]) {
            const prod = productsMap[prodId];
            const catSelect = row.querySelector('.category-select');

            if (catSelect && prod.category_id && (!catSelect.value || isUserChange)) {
                catSelect.value = prod.category_id;
            }

            syncSelectOption(prodSelect, prodId, prod.text);

            const hsnInput = row.querySelector('.hsn-input');
            const unitInput = row.querySelector('.unit-input');
            if (hsnInput) hsnInput.value = prod.hsn || '';
            if (unitInput) unitInput.value = prod.unit || '';

            if (isUserChange) {
                const priceInput = row.querySelector('.price-input');
                const taxInput = row.querySelector('.tax-percent-input');
                if (priceInput) priceInput.value = (parseFloat(prod.price) > 0) ? parseFloat(prod.price).toFixed(2) : '0.00';
                if (taxInput) taxInput.value = parseFloat(prod.tax || 0).toFixed(2);
            }
        } else {
            syncSelectOption(prodSelect, '', '');
            const hsnInput = row.querySelector('.hsn-input');
            const unitInput = row.querySelector('.unit-input');
            if (hsnInput) hsnInput.value = '';
            if (unitInput) unitInput.value = '';

            if (isUserChange) {
                const priceInput = row.querySelector('.price-input');
                const taxInput = row.querySelector('.tax-percent-input');
                if (priceInput) priceInput.value = '0.00';
                if (taxInput) taxInput.value = '0.00';
            }
        }
        calculateTotals();
    }

    if (addRowBtn) {
        addRowBtn.addEventListener('click', function () {
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
                    <input type="number" step="any" min="0" name="products[${rowCount}][price]" class="form-control form-control-sm price-input" placeholder="e.g. 150" required>
                </td>
                <td>
                    <input type="number" step="any" min="0" name="products[${rowCount}][tax_percent]" class="form-control form-control-sm tax-percent-input bg-light" value="0" readonly tabindex="-1" style="cursor: not-allowed;" placeholder="0">
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

            const newProdSelect = newRow.querySelector('.product-select');
            if (newProdSelect) {
                initProductTomSelect(newProdSelect);
            }

            rowCount++;
            calculateTotals();
        });
    }

    if (itemsContainer) {
        itemsContainer.addEventListener('change', function(e) {
            const row = e.target.closest('.item-row');
            if (!row) return;

            if (e.target.classList.contains('category-select')) {
                filterProductsInRow(row);
            }
        });

        itemsContainer.addEventListener('click', function(e) {
            if (e.target.closest('.remove-row-btn')) {
                const rows = document.querySelectorAll('.item-row');
                if (rows.length > 1) {
                    const rowToRemove = e.target.closest('.item-row');
                    const prodSelect = rowToRemove.querySelector('.product-select');
                    if (prodSelect && prodSelect.tomselect) {
                        prodSelect.tomselect.destroy();
                    }
                    rowToRemove.remove();
                    calculateTotals();
                } else {
                    alert('Return entry must contain at least one line item.');
                }
            }
        });

        itemsContainer.addEventListener('input', function(e) {
            if (e.target.classList.contains('qty-input') || 
                e.target.classList.contains('price-input') || 
                e.target.classList.contains('tax-percent-input')) {
                calculateTotals();
            }
        });
    }

    const discountInput = document.getElementById('discount_amount');
    const shippingInput = document.getElementById('shipping_cost');
    const taxAmountInput = document.getElementById('tax_amount');

    if (discountInput) discountInput.addEventListener('input', calculateTotals);
    if (shippingInput) shippingInput.addEventListener('input', calculateTotals);
    if (taxAmountInput) taxAmountInput.addEventListener('input', calculateTotals);

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

        const discount = parseFloat(document.getElementById('discount_amount')?.value || 0);
        const shipping = parseFloat(document.getElementById('shipping_cost')?.value || 0);

        const taxInput = document.getElementById('tax_amount');
        if (taxInput) {
            taxInput.value = calculatedTax.toFixed(2);
        }

        const grandTotal = Math.max(0, subtotal - discount + calculatedTax + shipping);

        const subtotalEl = document.getElementById('displaySubtotal');
        const taxEl = document.getElementById('displayTax');
        const grandTotalEl = document.getElementById('displayGrandTotal');

        if (subtotalEl) subtotalEl.innerText = '₹' + subtotal.toFixed(2);
        if (taxEl) taxEl.innerText = '₹' + calculatedTax.toFixed(2);
        if (grandTotalEl) grandTotalEl.innerText = '₹' + grandTotal.toFixed(2);
    }

    // Initialize TomSelect on existing rows and preserve price/tax
    document.querySelectorAll('.item-row').forEach(row => {
        const prodSelect = row.querySelector('.product-select');
        if (prodSelect) {
            initProductTomSelect(prodSelect);
            if (prodSelect.value) {
                handleProductChange(row, prodSelect.value, false);
            }
        }
    });

    const returnableForm = document.getElementById('returnableForm') || document.getElementById('returnForm');
    if (returnableForm) {
        returnableForm.addEventListener('submit', function(e) {
            const rows = document.querySelectorAll('.item-row');
            for (let i = 0; i < rows.length; i++) {
                const row = rows[i];
                const prodSelect = row.querySelector('.product-select');
                if (!prodSelect) continue;

                const ts = prodSelect.tomselect;
                const chosenVal = ts ? ts.getValue() : prodSelect.value;

                if (!chosenVal) {
                    e.preventDefault();
                    e.stopPropagation();
                    alert('Please select a Product Item for all line items.');
                    if (ts) {
                        ts.focus();
                    } else {
                        prodSelect.focus();
                    }
                    return false;
                }

                const prodObj = productsMap[chosenVal];
                syncSelectOption(prodSelect, chosenVal, prodObj ? prodObj.text : chosenVal);
            }
        });
    }

    calculateTotals();
});
</script>
@endpush
@endsection
