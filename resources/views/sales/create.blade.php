@extends('layouts.app')

@section('title', 'New Sales Order')
@section('page_title', 'Point of Sale Order')
@section('page_subtitle', 'Process customer sale, auto-deduct inventory stock & issue tax invoice')

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
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Sales
    </a>
@endsection

@section('content')
<form action="{{ route('sales.store') }}" method="POST" id="saleForm">
    @csrf
    
    <div class="row g-3">
        <!-- Main Form Column -->
        <div class="col-12 col-xl-9 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Customer & Order Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="customer_id" class="form-label fw-semibold text-dark mb-0">Select Customer Name <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-medium" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                                <i class="bi bi-plus-circle me-1"></i>Add New Customer
                            </button>
                        </div>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Choose Customer Name</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->company_name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="invoice_number" class="form-label fw-semibold text-dark">Challan No <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace @error('invoice_number') is-invalid @enderror" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="" required>
                        @error('invoice_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="sale_date" class="form-label fw-semibold text-dark">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="project_name" class="form-label fw-semibold text-dark">Project Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('project_name') is-invalid @enderror" id="project_name" name="project_name" value="{{ old('project_name') }}" placeholder="" required>
                        @error('project_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="vehicle_number" class="form-label fw-semibold text-dark">Vehicle Number</label>
                        <input type="text" class="form-control @error('vehicle_number') is-invalid @enderror" id="vehicle_number" name="vehicle_number" value="{{ old('vehicle_number') }}" placeholder="">
                        @error('vehicle_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <!-- Products Selector Line Items -->
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold font-outfit text-dark mb-0">Sales Line Items</h6>
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
                                            <input type="number" step="0.01" min="0.01" name="products[{{ $index }}][price]" class="form-control form-control-sm price-input" value="{{ $oldProduct['price'] ?? '' }}" placeholder="e.g. 150.00" required>
                                        </td>
                                        <td>
                                            <input type="number" name="products[{{ $index }}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="{{ $oldProduct['tax_percent'] ?? '0.00' }}" min="0">
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
                                                <option value="{{ $prod->id }}" 
                                                        data-category="{{ $prod->category_id }}" 
                                                        data-hsn="{{ $prod->hsn_code ?? '' }}" 
                                                        data-unit="{{ $prod->unit }}" 
                                                        data-price="{{ $prod->selling_price }}" 
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
                                        <input type="text" class="form-control form-control-sm unit-input bg-light" readonly placeholder="Type">
                                    </td>
                                    <td>
                                        <input type="number" name="products[0][qty]" class="form-control form-control-sm qty-input" min="1" value="1" required>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" name="products[0][price]" class="form-control form-control-sm price-input" value="" placeholder="e.g. 150.00" required>
                                    </td>
                                    <td>
                                        <input type="number" name="products[0][tax_percent]" class="form-control form-control-sm tax-percent-input" value="0.00" min="0">
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
                <h5 class="fw-bold font-outfit text-dark mb-3">Billing Calculation</h5>

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
                    <input type="number" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="{{ old('discount_amount', '0.00') }}">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping / Delivery Fee (₹)</label>
                    <input type="number" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="{{ old('shipping_cost', '0.00') }}">
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark fs-5">Grand Total:</span>
                    <span class="fw-bold font-outfit text-primary fs-3" id="grandTotalDisplay">₹0.00</span>
                </div>

                <div class="mb-3">
                    <label for="payment_status" class="form-label small fw-semibold text-dark">Payment Status <span class="text-danger">*</span></label>
                    <select class="form-select" id="payment_status" name="payment_status" required onchange="syncPaymentStatus()">
                        <option value="pending" {{ old('payment_status', 'pending') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ old('payment_status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="unpaid" {{ old('payment_status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="paid_amount" class="form-label small fw-semibold text-dark">Amount Received (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', '0.00') }}" required readonly>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label small fw-semibold">Order Notes</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="e.g. Fire extinguisher installation & testing delivery notes">{{ old('notes') }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 font-outfit fw-semibold mt-2">
                    <i class="bi bi-printer me-1"></i> Issue Order & Print Invoice
                </button>
            </div>
        </div>
    </div>
</form>

<!-- Modal: Quick Add Customer -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold font-outfit text-dark" id="addCustomerModalLabel">
                    <i class="bi bi-person-plus text-primary me-2"></i>Add New Customer
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddCustomerForm">
                @csrf
                <div class="modal-body p-4">
                    <div id="customerModalAlert" class="alert alert-danger d-none py-2 px-3 small"></div>

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="modal_company_name" class="form-label fw-semibold">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_company_name" name="company_name" required placeholder="e.g. BlazeGuard Protection Pvt Ltd">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modal_gst_number" class="form-label fw-semibold">GST Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_gst_number" name="gst_number" required placeholder="e.g. 27AAAAA0000A1Z5">
                        </div>

                        <div class="col-12">
                            <label for="modal_address" class="form-label fw-semibold">Billing Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="modal_address" name="address" rows="2" required placeholder="Billing address..."></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modal_phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="modal_phone" name="phone" required maxlength="10" minlength="10" pattern="[0-9]{10}" title="Please enter exactly 10 digits" oninput="this.value = this.value.replace(/[^0-9]/g, '');" placeholder="e.g. 9876543210">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modal_email" class="form-label fw-semibold">Email Address</label>
                            <input type="email" class="form-control" id="modal_email" name="email" placeholder="e.g. billing@company.com">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="modal_status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_status" name="status" required>
                                <option value="active" selected>Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pe-4 pb-4">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" id="saveCustomerBtn">
                        <i class="bi bi-plus-circle me-1"></i> Save & Select Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    let customerSelect;
    document.addEventListener('DOMContentLoaded', function () {
        if (document.getElementById('customer_id')) {
            customerSelect = new TomSelect('#customer_id', {
                create: false,
                placeholder: 'Search or Select Customer Name',
                plugins: ['dropdown_input']
            });
        }

        let rowCount = {{ old('products') ? count(old('products')) : 1 }};

        const categoryOptions = `@foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach`;
        const productsOptions = `@foreach($products as $prod)<option value="{{ $prod->id }}" data-category="{{ $prod->category_id }}" data-hsn="{{ $prod->hsn_code ?? '' }}" data-unit="{{ $prod->unit }}" data-price="{{ $prod->selling_price }}" data-tax="{{ $prod->tax_percent }}">{{ $prod->name }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</option>@endforeach`;

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
                    <input type="number" step="0.01" min="0.01" name="products[${rowCount}][price]" class="form-control form-control-sm price-input" value="" placeholder="e.g. 150.00" required>
                </td>
                <td>
                    <input type="number" name="products[${rowCount}][tax_percent]" class="form-control form-control-sm tax-percent-input" value="0.00" min="0">
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
                e.target.classList.contains('price-input') || 
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
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                const taxPercent = parseFloat(row.querySelector('.tax-percent-input').value) || 0;

                const rowSubtotal = qty * price;
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
            syncPaymentStatus(grandTotal);
        }

        function syncPaymentStatus(grandTotalValue) {
            const statusSelect = document.getElementById('payment_status');
            if (!statusSelect) return;
            const status = statusSelect.value;
            const paidInput = document.getElementById('paid_amount');

            if (grandTotalValue === undefined) {
                const grandTotalText = document.getElementById('grandTotalDisplay').textContent.replace('₹', '');
                grandTotalValue = parseFloat(grandTotalText) || 0;
            }

            if (status === 'paid') {
                paidInput.value = grandTotalValue.toFixed(2);
                paidInput.readOnly = true;
            } else {
                paidInput.value = '0.00';
                paidInput.readOnly = true;
            }
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

        // Quick Add Customer AJAX submit
        const customerForm = document.getElementById('quickAddCustomerForm');
        if (customerForm) {
            customerForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const alertBox = document.getElementById('customerModalAlert');
                const submitBtn = document.getElementById('saveCustomerBtn');

                alertBox.classList.add('d-none');
                alertBox.textContent = '';
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

                const formData = new FormData(customerForm);

                fetch("{{ route('customers.store') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(response => response.json().then(data => ({ status: response.status, body: data })))
                .then(res => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Save & Select Customer';

                    if (res.status === 200 || res.status === 201) {
                        if (res.body.success) {
                            if (customerSelect) {
                                customerSelect.addOption({ value: res.body.customer.id, text: res.body.customer.company_name });
                                customerSelect.setValue(res.body.customer.id);
                            } else {
                                const select = document.getElementById('customer_id');
                                const option = document.createElement('option');
                                option.value = res.body.customer.id;
                                option.textContent = res.body.customer.company_name;
                                option.selected = true;
                                select.appendChild(option);
                            }

                            customerForm.reset();
                            const modalEl = document.getElementById('addCustomerModal');
                            const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                            if (modal) {
                                modal.hide();
                            }
                        }
                    } else {
                        let errMsg = 'Company Name already exists.';
                        if (res.body.errors) {
                            const firstErrKey = Object.keys(res.body.errors)[0];
                            if (firstErrKey) {
                                errMsg = res.body.errors[firstErrKey].join(' ');
                            }
                        } else if (res.body.message) {
                            errMsg = res.body.message;
                        }
                        alertBox.textContent = errMsg;
                        alertBox.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-plus-circle me-1"></i> Save & Select Customer';
                    alertBox.textContent = 'Company Name already exists.';
                    alertBox.classList.remove('d-none');
                });
            });
        }
    });
</script>
@endpush
