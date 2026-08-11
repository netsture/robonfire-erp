@extends('layouts.app')

@section('title', isset($purchase) ? 'Edit ' . $details['title'] : 'Create ' . $details['title'])

@section('styles')
<style>
    /* Styling for the Excel-like Grid */
    .form-header-grid {
        background-color: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-label-excel {
        font-weight: 600;
        font-size: 0.875rem;
        color: #475569;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('purchases.index', ['type' => $type]) }}">{{ $details['title'] }}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ isset($purchase) ? 'Edit' : 'Create' }}</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800">{{ isset($purchase) ? 'Edit' : 'Create' }} {{ $details['title'] }}</h1>
        </div>
        <a href="{{ route('purchases.index', ['type' => $type]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Alert Container -->
    <div class="alert alert-danger d-none" id="form-errors"></div>

    <form id="purchase-form">
        @csrf
        @if(isset($purchase))
            <input type="hidden" name="id" id="purchase_id" value="{{ $purchase->id }}">
        @endif

        <!-- Form Top Header Grid -->
        <div class="form-header-grid">
            <div class="row g-3">
                <!-- Party Name -->
                <div class="col-md-4">
                    <label for="party_id" class="form-label-excel">Party Name</label>
                    <select class="form-select select2-parties" id="party_id" name="party_id" required>
                        <option value="">Select Party...</option>
                        @foreach($parties as $party)
                            <option value="{{ $party->id }}" data-address="{{ $party->address }}" {{ (isset($purchase) && $purchase->party_id == $party->id) ? 'selected' : '' }}>
                                {{ $party->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date -->
                <div class="col-md-4">
                    <label for="date" class="form-label-excel">Date</label>
                    <input type="date" class="form-control" id="date" name="date" required value="{{ isset($purchase) ? $purchase->date->format('Y-m-d') : date('Y-m-d') }}">
                </div>

                <!-- Document Number (Bill No or Chalan No) -->
                <div class="col-md-4">
                    <label for="doc_number" class="form-label-excel">{{ $details['doc_label'] }}</label>
                    @if($type === 'inward')
                        <input type="text" class="form-control" id="bill_no" name="bill_no" required value="{{ $purchase->bill_no ?? '' }}" placeholder="Enter Bill No">
                    @else
                        <input type="text" class="form-control" id="chalan_no" name="chalan_no" required value="{{ $purchase->chalan_no ?? '' }}" placeholder="Enter Chalan No">
                    @endif
                </div>

                <!-- Address -->
                <div class="col-md-4">
                    <label for="address" class="form-label-excel">Address</label>
                    <input type="text" class="form-control" id="address" name="address" readonly value="{{ $purchase->address ?? '' }}" placeholder="Select a party to auto-fill address">
                </div>

                <!-- Vehicle (if applicable) -->
                @if($details['has_vehicle'])
                    <div class="col-md-4">
                        <label for="vehicle" class="form-label-excel">Vehicle Number</label>
                        <input type="text" class="form-control" id="vehicle" name="vehicle" required value="{{ $purchase->vehicle ?? '' }}" placeholder="e.g. GJ01HZ7154">
                    </div>
                @endif

                <!-- Reason (if applicable) -->
                @if($details['has_reason'])
                    <div class="col-md-4">
                        <label for="reason" class="form-label-excel">Reason</label>
                        <input type="text" class="form-control" id="reason" name="reason" required value="{{ $purchase->reason ?? '' }}" placeholder="e.g. Cancel order">
                    </div>
                @endif

                <!-- Project Name -->
                <div class="col-md-4">
                    <label for="project_name" class="form-label-excel">Project Name</label>
                    <input type="text" class="form-control" id="project_name" name="project_name" value="{{ $purchase->project_name ?? '' }}" placeholder="e.g. Tata - Sanad">
                </div>
            </div>
        </div>

        <!-- Spreadsheet Grid -->
        <div class="card card-premium">
            <div class="card-header">
                <span>Material Log / Items Grid</span>
                <button type="button" class="btn btn-sm btn-outline-success" id="add-row-btn">
                    <i class="bi bi-plus-lg"></i> Add Row
                </button>
            </div>
            <div class="card-body p-0">
                <div class="spreadsheet-container">
                    <table class="spreadsheet-table table">
                        <thead class="spreadsheet-header-{{ $type }}">
                            <tr>
                                <th style="width: 50px; text-align: center;">Sr No</th>
                                <th>Product Name</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>HSN Code</th>
                                <th style="width: 100px;">Qty</th>
                                @if($details['has_type_column'])
                                    <th style="width: 120px;">Type</th>
                                @endif
                                <th style="width: 120px;">Rate</th>
                                <th style="width: 100px;">Tax%</th>
                                <th style="width: 140px;">Total</th>
                                <th style="width: 150px;">Total With Tax</th>
                                <th style="width: 60px; text-align: center;">Act</th>
                            </tr>
                        </thead>
                        <tbody id="items-tbody">
                            @if(isset($purchase) && $purchase->items->count() > 0)
                                @foreach($purchase->items as $index => $item)
                                    <tr class="item-row">
                                        <td class="sr-no text-center font-weight-bold">{{ $index + 1 }}</td>
                                        <td>
                                            <input type="text" class="product-name" name="items[{{ $index }}][product_name]" required value="{{ $item->product_name }}" placeholder="ABC 6 KG Fire Extinguisher">
                                        </td>
                                        <td>
                                            <input type="text" class="brand" name="items[{{ $index }}][brand]" value="{{ $item->brand }}" placeholder="Kanex">
                                        </td>
                                        <td>
                                            <input type="text" class="category" name="items[{{ $index }}][category]" value="{{ $item->category }}" placeholder="Fire Extinguisher">
                                        </td>
                                        <td>
                                            <input type="text" class="hsn-code" name="items[{{ $index }}][hsn_code]" value="{{ $item->hsn_code }}" placeholder="8424">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="quantity" name="items[{{ $index }}][quantity]" required value="{{ $item->quantity }}" style="text-align: right;">
                                        </td>
                                        @if($details['has_type_column'])
                                            <td>
                                                <select class="item-type" name="items[{{ $index }}][type]" required>
                                                    <option value="Nos." {{ $item->type === 'Nos.' ? 'selected' : '' }}>Nos.</option>
                                                    <option value="KG" {{ $item->type === 'KG' ? 'selected' : '' }}>KG</option>
                                                    <option value="Mtr." {{ $item->type === 'Mtr.' ? 'selected' : '' }}>Mtr.</option>
                                                </select>
                                            </td>
                                        @endif
                                        <td>
                                            <input type="number" step="0.01" class="rate" name="items[{{ $index }}][rate]" required value="{{ $item->rate }}" style="text-align: right;">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" class="tax-percent" name="items[{{ $index }}][tax_percent]" required value="{{ $item->tax_percent }}" style="text-align: right;">
                                        </td>
                                        <td>
                                            <input type="text" class="total-val" readonly value="{{ number_format($item->total, 2, '.', '') }}" style="text-align: right; background-color: #f1f5f9; font-weight: 500;">
                                        </td>
                                        <td>
                                            <input type="text" class="total-with-tax-val" readonly value="{{ number_format($item->total_with_tax, 2, '.', '') }}" style="text-align: right; background-color: #f1f5f9; font-weight: 600;">
                                        </td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn" tabindex="-1">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @else
                                <!-- Default First Row -->
                                <tr class="item-row">
                                    <td class="sr-no text-center font-weight-bold">1</td>
                                    <td>
                                        <input type="text" class="product-name" name="items[0][product_name]" required placeholder="ABC 6 KG Fire Extinguisher">
                                    </td>
                                    <td>
                                        <input type="text" class="brand" name="items[0][brand]" placeholder="Kanex">
                                    </td>
                                    <td>
                                        <input type="text" class="category" name="items[0][category]" placeholder="Fire Extinguisher">
                                    </td>
                                    <td>
                                        <input type="text" class="hsn-code" name="items[0][hsn_code]" placeholder="8424">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="quantity" name="items[0][quantity]" required value="0" style="text-align: right;">
                                    </td>
                                    @if($details['has_type_column'])
                                        <td>
                                            <select class="item-type" name="items[0][type]" required>
                                                <option value="Nos.">Nos.</option>
                                                <option value="KG">KG</option>
                                                <option value="Mtr.">Mtr.</option>
                                            </select>
                                        </td>
                                    @endif
                                    <td>
                                        <input type="number" step="0.01" class="rate" name="items[0][rate]" required value="0" style="text-align: right;">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" class="tax-percent" name="items[0][tax_percent]" required value="18" style="text-align: right;">
                                    </td>
                                    <td>
                                        <input type="text" class="total-val" readonly value="0.00" style="text-align: right; background-color: #f1f5f9; font-weight: 500;">
                                    </td>
                                    <td>
                                        <input type="text" class="total-with-tax-val" readonly value="0.00" style="text-align: right; background-color: #f1f5f9; font-weight: 600;">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-link text-danger remove-row-btn" tabindex="-1">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8fafc; font-weight: 600;">
                                <td colspan="5" class="text-end">Grand Totals:</td>
                                <td id="summary-qty" style="text-align: right; padding-right: 12px;">0</td>
                                @if($details['has_type_column'])
                                    <td></td>
                                @endif
                                <td></td>
                                <td></td>
                                <td id="summary-total" style="text-align: right; padding-right: 12px; font-weight: 600;">₹0.00</td>
                                <td id="summary-total-with-tax" style="text-align: right; padding-right: 12px; font-weight: 700; color: #1e3a8a;">₹0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <!-- Form Actions Floating Bar -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary px-5 py-2.5 shadow">
                <i class="bi bi-save me-1"></i> Save Material Log
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 for Parties
    $('.select2-parties').select2({
        theme: 'bootstrap-5',
        placeholder: 'Select Party...'
    });

    // Handle Address Auto-fill
    $('#party_id').on('change', function() {
        let selectedOption = $(this).find(':selected');
        let address = selectedOption.data('address') || '';
        $('#address').val(address);
    });

    // Row counter for dynamic naming indices
    var rowCount = {{ isset($purchase) ? $purchase->items->count() : 1 }};

    // Add Row Click Action
    $('#add-row-btn').click(function() {
        let tbody = $('#items-tbody');
        let templateRow = tbody.find('.item-row').first().clone();
        
        // Reset values
        templateRow.find('input[type="text"]').val('');
        templateRow.find('input[type="number"]').val(0);
        templateRow.find('.tax-percent').val(18); // default tax
        templateRow.find('.total-val').val('0.00');
        templateRow.find('.total-with-tax-val').val('0.00');
        
        // Re-index names
        templateRow.find('input, select').each(function() {
            let name = $(this).attr('name');
            if (name) {
                let newName = name.replace(/\[\d+\]/, '[' + rowCount + ']');
                $(this).attr('name', newName);
            }
        });
        
        tbody.append(templateRow);
        rowCount++;
        recalculateAll();
    });

    // Remove Row Click Action
    $(document).on('click', '.remove-row-btn', function() {
        if ($('#items-tbody .item-row').length > 1) {
            $(this).closest('.item-row').remove();
            recalculateAll();
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Row Limit',
                text: 'You must have at least one item row.'
            });
        }
    });

    // Recalculate values on input changes
    $(document).on('input', '.quantity, .rate, .tax-percent', function() {
        let row = $(this).closest('.item-row');
        calculateRow(row);
        recalculateTotals();
    });

    function calculateRow(row) {
        let qty = parseFloat(row.find('.quantity').val()) || 0;
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let taxPercent = parseFloat(row.find('.tax-percent').val()) || 0;

        let total = qty * rate;
        let totalWithTax = total + (total * taxPercent / 100);

        row.find('.total-val').val(total.toFixed(2));
        row.find('.total-with-tax-val').val(totalWithTax.toFixed(2));
    }

    function recalculateAll() {
        // Re-index all sr-nos
        $('#items-tbody .item-row').each(function(index) {
            $(this).find('.sr-no').text(index + 1);
            
            // Sync input names indices to match order
            $(this).find('input, select').each(function() {
                let name = $(this).attr('name');
                if (name) {
                    let newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
            
            calculateRow($(this));
        });
        recalculateTotals();
    }

    function recalculateTotals() {
        let grandTotal = 0;
        let grandTotalWithTax = 0;
        let totalQty = 0;

        $('#items-tbody .item-row').each(function() {
            let qty = parseFloat($(this).find('.quantity').val()) || 0;
            let total = parseFloat($(this).find('.total-val').val()) || 0;
            let totalWithTax = parseFloat($(this).find('.total-with-tax-val').val()) || 0;

            totalQty += qty;
            grandTotal += total;
            grandTotalWithTax += totalWithTax;
        });

        $('#summary-qty').text(totalQty.toFixed(2));
        $('#summary-total').text('₹' + grandTotal.toFixed(2));
        $('#summary-total-with-tax').text('₹' + grandTotalWithTax.toFixed(2));
    }

    // Initialize totals on page load
    recalculateTotals();

    // Form Submit Action
    $('#purchase-form').submit(function(e) {
        e.preventDefault();
        $('#form-errors').addClass('d-none').html('');
        
        let url = "{{ isset($purchase) ? route('purchases.update', ['type' => $type, 'id' => $purchase->id]) : route('purchases.store', ['type' => $type]) }}";
        
        $.ajax({
            url: url,
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Saved!',
                        text: response.message
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let errorHtml = '<ul>';
                    $.each(errors, function(key, value) {
                        errorHtml += '<li>' + value[0] + '</li>';
                    });
                    errorHtml += '</ul>';
                    $('#form-errors').removeClass('d-none').html(errorHtml);
                    window.scrollTo(0, 0);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.responseJSON.message || 'Something went wrong.'
                    });
                }
            }
        });
    });
});
</script>
@endsection
