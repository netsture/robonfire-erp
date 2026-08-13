@extends('layouts.app')

@section('title', 'New Sales Order')
@section('page_title', 'Point of Sale (POS) Order')
@section('page_subtitle', 'Process customer sale, auto-deduct inventory stock & issue tax invoice')

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
        <div class="col-12 col-lg-8">
            <div class="card card-custom border-0 p-4 mb-3">
                <h5 class="fw-bold font-outfit text-dark mb-3">Customer & Order Details</h5>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <label for="customer_id" class="form-label fw-semibold text-dark">Select Customer <span class="text-danger">*</span></label>
                        <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                            <option value="">Choose Customer...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                            @endforeach
                        </select>
                        @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="invoice_number" class="form-label fw-semibold text-dark">Invoice # <span class="text-danger">*</span></label>
                        <input type="text" class="form-control font-monospace" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $autoInvoice) }}" required>
                    </div>

                    <div class="col-12 col-md-3">
                        <label for="sale_date" class="form-label fw-semibold text-dark">Sale Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="sale_date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}" required>
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
                                <th style="width: 45%;">Product</th>
                                <th style="width: 20%;">Qty</th>
                                <th style="width: 25%;">Selling Price ($)</th>
                                <th style="width: 10%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="itemsContainer">
                            <tr class="item-row">
                                <td>
                                    <select name="products[0][id]" class="form-select product-select" required>
                                        <option value="">Select Product...</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}" data-price="{{ $prod->selling_price }}" data-stock="{{ $prod->stock_quantity }}">
                                                {{ $prod->name }} (Stock: {{ $prod->stock_quantity }} {{ $prod->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="products[0][qty]" class="form-control qty-input" min="1" value="1" required>
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="products[0][price]" class="form-control price-input" value="0.00" required>
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
                <h5 class="fw-bold font-outfit text-dark mb-3">Billing Calculation</h5>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted">Subtotal:</span>
                    <span class="fw-bold font-outfit text-dark fs-5" id="subtotalDisplay">$0.00</span>
                </div>

                <div class="mb-3">
                    <label for="discount_amount" class="form-label small fw-semibold">Discount ($)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="discount_amount" name="discount_amount" value="0.00">
                </div>

                <div class="mb-3">
                    <label for="tax_amount" class="form-label small fw-semibold">Tax Amount ($)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="tax_amount" name="tax_amount" value="0.00">
                </div>

                <div class="mb-3">
                    <label for="shipping_cost" class="form-label small fw-semibold">Shipping / Delivery Fee ($)</label>
                    <input type="number" step="0.01" class="form-control form-control-sm" id="shipping_cost" name="shipping_cost" value="0.00">
                </div>

                <hr>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold text-dark fs-5">Grand Total:</span>
                    <span class="fw-bold font-outfit text-primary fs-3" id="grandTotalDisplay">$0.00</span>
                </div>

                <div class="mb-3">
                    <label for="paid_amount" class="form-label small fw-semibold text-dark">Amount Received ($) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="paid_amount" name="paid_amount" value="0.00" required>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label small fw-semibold">Order Notes</label>
                    <textarea class="form-control form-control-sm" id="notes" name="notes" rows="2" placeholder="Payment method or delivery notes"></textarea>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 font-outfit fw-semibold mt-2">
                    <i class="bi bi-printer me-1"></i> Issue Order & Print Invoice
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

        const productsOptions = `@foreach($products as $prod)<option value="{{ $prod->id }}" data-price="{{ $prod->selling_price }}" data-stock="{{ $prod->stock_quantity }}">{{ $prod->name }} (Stock: {{ $prod->stock_quantity }} {{ $prod->unit }})</option>@endforeach`;

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
                    <input type="number" step="0.01" name="products[${rowCount}][price]" class="form-control price-input" value="0.00" required>
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
                const price = selectedOption.getAttribute('data-price') || 0;
                const row = e.target.closest('.item-row');
                row.querySelector('.price-input').value = parseFloat(price).toFixed(2);
                calculateTotals();
            }
        });

        document.getElementById('itemsContainer').addEventListener('input', calculateTotals);
        document.getElementById('discount_amount').addEventListener('input', calculateTotals);
        document.getElementById('tax_amount').addEventListener('input', calculateTotals);
        document.getElementById('shipping_cost').addEventListener('input', calculateTotals);

        function calculateTotals() {
            let subtotal = 0;
            const rows = document.querySelectorAll('.item-row');
            rows.forEach(row => {
                const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
                const price = parseFloat(row.querySelector('.price-input').value) || 0;
                subtotal += (qty * price);
            });

            const discount = parseFloat(document.getElementById('discount_amount').value) || 0;
            const tax = parseFloat(document.getElementById('tax_amount').value) || 0;
            const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;
            const grandTotal = Math.max(0, subtotal - discount + tax + shipping);

            document.getElementById('subtotalDisplay').textContent = '$' + subtotal.toFixed(2);
            document.getElementById('grandTotalDisplay').textContent = '$' + grandTotal.toFixed(2);
            document.getElementById('paid_amount').value = grandTotal.toFixed(2);
        }
    });
</script>
@endpush
