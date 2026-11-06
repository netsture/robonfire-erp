@extends('layouts.app')

@section('title', 'New Returnable Entry')
@section('page_title', 'Create Returnable Entry')
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
<form action="{{ route('returnable.store') }}" method="POST" id="returnForm">
    @csrf
    
    <div class="row g-3">
        <!-- Main Form Column -->
        <div class="col-12 col-xl-9 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Returnable Details</h5>

                <div class="row g-3 mb-4">
                    <!-- Row 1: Sales Challan No, Customer Name, Project Name -->
                    <div class="col-12 col-md-4">
                        <label for="sale_id" class="form-label fw-semibold text-dark">Select Sales Challan No</label>
                        <select class="form-select" id="sale_id" name="sale_id">
                            <option value="">Select or Search Sales Challan No</option>
                            @foreach($sales as $sale)
                                @php
                                    $saleItemsPayload = $sale->items->map(function($item) use ($sale) {
                                        $taxPct = (float)($item->tax_percent ?? 0);
                                        if ($taxPct == 0 && (float)($item->tax_amount ?? 0) > 0 && (float)$item->subtotal > 0) {
                                            $taxPct = round(((float)$item->tax_amount / (float)$item->subtotal) * 100, 2);
                                        } elseif ($taxPct == 0 && (float)$sale->tax_amount > 0 && (float)$sale->subtotal > 0) {
                                            if ($sale->items->count() === 1) {
                                                $taxPct = round(((float)$sale->tax_amount / (float)$sale->subtotal) * 100, 2);
                                            } elseif ($item->product && (float)$item->product->tax_percent > 0) {
                                                $taxPct = (float)$item->product->tax_percent;
                                            } else {
                                                $taxPct = round(((float)$sale->tax_amount / (float)$sale->subtotal) * 100, 2);
                                            }
                                        } elseif ($taxPct == 0 && $item->product) {
                                            $taxPct = (float)($item->product->tax_percent ?? 0);
                                        }

                                        $unitPrice = (float)($item->unit_price ?? ($item->product ? $item->product->selling_price : 0));
                                        $qty = (int)($item->quantity ?? 1);
                                        $subtotal = (float)($item->subtotal > 0 ? $item->subtotal : ($unitPrice * $qty));
                                        $taxAmount = (float)($item->tax_amount ?? round(($subtotal * $taxPct) / 100, 2));

                                        return [
                                            'id'          => $item->id,
                                            'product_id'  => $item->product_id,
                                            'quantity'    => $qty,
                                            'unit_price'  => $unitPrice,
                                            'subtotal'    => $subtotal,
                                            'tax_percent' => $taxPct,
                                            'tax_amount'  => $taxAmount,
                                            'category_id' => $item->product ? $item->product->category_id : null,
                                            'hsn'         => $item->product ? ($item->product->hsn_code ?? '') : '',
                                            'unit'        => $item->product ? ($item->product->unit ?? '') : '',
                                        ];
                                    });
                                @endphp
                                <option value="{{ $sale->id }}" 
                                        data-customer-id="{{ $sale->customer_id }}" 
                                        data-project-name="{{ $sale->project_name ?? '' }}"
                                        data-tax-amount="{{ $sale->tax_amount ?? 0 }}"
                                        data-items="{{ json_encode($saleItemsPayload) }}">
                                    {{ $sale->invoice_number }}
                                </option>
                            @endforeach
                        </select>
                        <!--<span class="text-muted small">Select a Sales Challan to auto-fill Customer, Project Name & Return Items.</span>-->
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="customer_id" class="form-label fw-semibold text-dark">Select Customer Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Select or Search Customer Name</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <select class="form-select @error('project_name') is-invalid @enderror" id="project_name" name="project_name" required>
                            <option value="">Select, Search or Type Project Name</option>
                            @foreach($projectNames as $proj)
                                <option value="{{ $proj }}" {{ old('project_name') == $proj ? 'selected' : '' }}>{{ $proj }}</option>
                            @endforeach
                        </select>
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Row 2: Return No, Return Date, Reason for Return -->
                    <div class="col-12 col-md-4">
                        <label for="return_number" class="form-label fw-semibold text-dark">Return No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('return_number') is-invalid @enderror" id="return_number" name="return_number" value="{{ old('return_number', $autoReturnNumber) }}" required>
                        @error('return_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="return_date" class="form-label fw-semibold text-dark">Return Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="return_date" name="return_date" value="{{ old('return_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-12 col-md-4">
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
                                                            data-identifier="{{ $prod->product_identifier ?? '' }}"
                                                            data-hsn="{{ $prod->hsn_code ?? '' }}" 
                                                            data-unit="{{ $prod->unit }}" 
                                                            data-price="{{ $prod->selling_price }}" 
                                                            data-tax="{{ $prod->tax_percent }}"
                                                            {{ (isset($oldProduct['id']) && $oldProduct['id'] == $prod->id) ? 'selected' : '' }}>
                                                        {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}{{ $prod->product_identifier ? ' ['.$prod->product_identifier.']' : '' }}
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
                                            <input type="number" min="0" name="products[{{ $index }}][price]" class="form-control form-control-sm price-input" value="{{ $oldProduct['price'] ?? '' }}" placeholder="e.g. 150" required>
                                        </td>
                                        <td>
                                            <input type="number" min="0" step="any" name="products[{{ $index }}][tax_percent]" class="form-control form-control-sm tax-percent-input bg-light" value="{{ $oldProduct['tax_percent'] ?? 0 }}" readonly tabindex="-1" style="cursor: not-allowed;" placeholder="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm line-total bg-light fw-bold" readonly value="0">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control form-control-sm line-total-tax bg-light fw-bold text-primary" readonly value="0">
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
                                                <option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" data-identifier="{{ $prod->product_identifier ?? '' }}" data-hsn="{{ $prod->hsn_code ?? '' }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->selling_price }}" data-tax="{{ $prod->tax_percent }}">
                                                    {{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}{{ $prod->product_identifier ? ' ['.$prod->product_identifier.']' : '' }}
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
                                        <input type="number" min="0" name="products[0][price]" class="form-control form-control-sm price-input" placeholder="e.g. 150" required>
                                    </td>
                                    <td>
                                        <input type="number" min="0" step="any" name="products[0][tax_percent]" class="form-control form-control-sm tax-percent-input bg-light" value="0" readonly tabindex="-1" style="cursor: not-allowed;" placeholder="0">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm line-total bg-light fw-bold" readonly value="0">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm line-total-tax bg-light fw-bold text-primary" readonly value="0">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-row-btn" title="Remove"><i class="bi bi-trash"></i></button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Summary Column -->
        <div class="col-12 col-xl-3 col-lg-4">
            <div class="card card-custom border-0 p-4 sticky-top" style="top: 90px;">
                <h5 class="fw-bold font-outfit text-dark mb-3">Return Summary</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold font-outfit text-dark fs-6" id="displaySubtotal">₹0.00</span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Total Tax:</span>
                    <span class="fw-bold font-outfit text-dark fs-6" id="displayTax">₹0.00</span>
                </div>
                <input type="hidden" id="tax_amount" name="tax_amount" value="{{ old('tax_amount', '0') }}">

                <div class="mb-3">
                    <label for="discount_amount" class="form-label small fw-semibold">Discount Amount (₹)</label>
                    <input type="number" min="0" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', '0') }}">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping Cost (₹)</label>
                    <input type="number" min="0" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', '0') }}">
                </div>

                <hr class="my-3">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark fs-5">Grand Total:</span>
                    <span class="fw-bold text-primary fs-4" id="displayGrandTotal">₹0.00</span>
                </div>

                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold text-dark">Return Order Notes / Remarks</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="3" placeholder="Enter return material reason, condition or remarks...">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill font-outfit fw-bold shadow-sm">
                    <i class="bi bi-check-circle me-1"></i> Submit Returnable Entry
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
        let customerTs = null;
        if (document.getElementById('customer_id')) {
            customerTs = new TomSelect('#customer_id', {
                create: false,
                placeholder: 'Select or Search Customer Name',
                plugins: ['dropdown_input']
            });
        }

        let projectTs = null;
        if (document.getElementById('project_name')) {
            projectTs = new TomSelect('#project_name', {
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

        let rowCount = document.querySelectorAll('.item-row').length;
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

        let saleTs = null;
        if (document.getElementById('sale_id')) {
            saleTs = new TomSelect('#sale_id', {
                create: false,
                placeholder: 'Select or Search Sales Challan No',
                plugins: ['dropdown_input']
            });

            saleTs.on('change', function (val) {
                if (!val) return;
                const optEl = document.querySelector('#sale_id option[value="' + val + '"]');
                if (optEl) {
                    const custId = optEl.getAttribute('data-customer-id');
                    const projName = optEl.getAttribute('data-project-name');

                    if (custId && customerTs) {
                        customerTs.setValue(custId);
                    }
                    if (projName && projectTs) {
                        if (!projectTs.options[projName]) {
                            projectTs.addOption({ value: projName, text: projName });
                        }
                        projectTs.setValue(projName);
                    }

                    const rawItems = optEl.getAttribute('data-items');
                    if (rawItems) {
                        try {
                            const items = JSON.parse(rawItems);
                            if (items && items.length > 0) {
                                populateSaleItems(items);
                            }
                        } catch(e) {}
                    }
                }
            });
        }

        function populateSaleItems(items) {
            const container = document.getElementById('itemsContainer');
            if (!container) return;

            container.querySelectorAll('.product-select').forEach(sel => {
                if (sel.tomselect) sel.tomselect.destroy();
            });

            container.innerHTML = '';
            rowCount = 0;

            items.forEach(item => {
                const priceVal = parseFloat(item.unit_price !== undefined ? item.unit_price : 0).toFixed(2);
                const taxVal = parseFloat(item.tax_percent !== undefined ? item.tax_percent : 0).toFixed(2);

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
                        <input type="text" class="form-control form-control-sm hsn-input bg-light" readonly placeholder="HSN" value="${item.hsn || ''}">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type" value="${item.unit || ''}">
                    </td>
                    <td>
                        <input type="number" name="products[${rowCount}][qty]" class="form-control form-control-sm qty-input" min="1" value="${item.quantity || 1}" required>
                    </td>
                    <td>
                        <input type="number" step="any" min="0" name="products[${rowCount}][price]" class="form-control form-control-sm price-input" value="${priceVal}" placeholder="e.g. 150" required>
                    </td>
                    <td>
                        <input type="number" step="any" min="0" name="products[${rowCount}][tax_percent]" class="form-control form-control-sm tax-percent-input bg-light" value="${taxVal}" readonly tabindex="-1" style="cursor: not-allowed;" placeholder="0">
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

                const catSelect = newRow.querySelector('.category-select');
                const prodSelect = newRow.querySelector('.product-select');
                if (prodSelect && item.product_id) {
                    prodSelect.value = item.product_id;
                    const selectedOpt = prodSelect.options[prodSelect.selectedIndex];
                    const catId = item.category_id || (selectedOpt ? selectedOpt.getAttribute('data-category') : '');
                    if (catSelect && catId) {
                        catSelect.value = catId;
                    }
                    if (selectedOpt) {
                        if (!newRow.querySelector('.hsn-input').value) {
                            newRow.querySelector('.hsn-input').value = selectedOpt.getAttribute('data-hsn') || '';
                        }
                        if (!newRow.querySelector('.unit-input').value) {
                            newRow.querySelector('.unit-input').value = selectedOpt.getAttribute('data-unit') || '';
                        }
                    }
                }

                if (prodSelect) {
                    initProductTomSelect(prodSelect);
                }

                rowCount++;
            });
            calculateTotals();
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
                    const hsnEl = row.querySelector('.hsn-input');
                    const unitEl = row.querySelector('.unit-input');
                    const priceEl = row.querySelector('.price-input');
                    const taxEl = row.querySelector('.tax-percent-input');
                    if (hsnEl) hsnEl.value = '';
                    if (unitEl) unitEl.value = '';
                    if (priceEl) priceEl.value = '0.00';
                    if (taxEl) taxEl.value = '0.00';
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

                const hsnEl = row.querySelector('.hsn-input');
                const unitEl = row.querySelector('.unit-input');
                if (hsnEl) hsnEl.value = prod.hsn || '';
                if (unitEl) unitEl.value = prod.unit || '';

                if (isUserChange) {
                    const priceEl = row.querySelector('.price-input');
                    const taxEl = row.querySelector('.tax-percent-input');
                    if (priceEl) priceEl.value = (parseFloat(prod.price) > 0) ? parseFloat(prod.price).toFixed(2) : '0.00';
                    if (taxEl) taxEl.value = parseFloat(prod.tax || 0).toFixed(2);
                }
            } else {
                syncSelectOption(prodSelect, '', '');
                const hsnEl = row.querySelector('.hsn-input');
                const unitEl = row.querySelector('.unit-input');
                if (hsnEl) hsnEl.value = '';
                if (unitEl) unitEl.value = '';

                if (isUserChange) {
                    const priceEl = row.querySelector('.price-input');
                    const taxEl = row.querySelector('.tax-percent-input');
                    if (priceEl) priceEl.value = '0.00';
                    if (taxEl) taxEl.value = '0.00';
                }
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
                    <input type="number" step="any" min="0" name="products[${rowCount}][tax_percent]" class="form-control form-control-sm tax-percent-input bg-light" value="0.00" readonly placeholder="0.00">
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

        document.getElementById('itemsContainer').addEventListener('change', function(e) {
            const row = e.target.closest('.item-row');
            if (!row) return;

            if (e.target.classList.contains('category-select')) {
                filterProductsInRow(row);
            }
        });

        document.getElementById('itemsContainer').addEventListener('click', function(e) {
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
            
            const taxInput = document.getElementById('tax_amount');
            if (taxInput) {
                taxInput.value = calculatedTax.toFixed(2);
            }

            const grandTotal = Math.max(0, subtotal - discount + calculatedTax + shipping);

            document.getElementById('displaySubtotal').innerText = '₹' + subtotal.toFixed(2);
            document.getElementById('displayTax').innerText = '₹' + calculatedTax.toFixed(2);
            document.getElementById('displayGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
        }

        // Initialize TomSelect on initial rows
        document.querySelectorAll('.item-row').forEach(row => {
            const prodSelect = row.querySelector('.product-select');
            if (prodSelect) {
                initProductTomSelect(prodSelect);
                if (prodSelect.value) {
                    handleProductChange(row, prodSelect.value, false);
                }
            }
        });

        const returnForm = document.getElementById('returnForm');
        if (returnForm) {
            returnForm.addEventListener('submit', function(e) {
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
