<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Stock Valuation Report - {{ auth()->user()->firm->name ?? 'ERP Solution' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2rem; background: #fff; color: #1e293b; }
        .invoice-card { max-width: 950px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 2rem; border-radius: 0.75rem; }
        .invoice-header-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #fff; padding: 1.25rem 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .table td, .table th { padding: 0.45rem 0.5rem; font-size: 11px; }
        
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm;
            }
            *, ::after, ::before {
                box-sizing: border-box !important;
            }
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: #fff !important; }
            .invoice-card {
                border: 1px solid #cbd5e1 !important;
                padding: 1.25rem !important;
                max-width: 100% !important;
                margin: 0 !important;
                border-radius: 0.5rem !important;
            }
            .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }
            .invoice-header-bg {
                padding: 1rem !important;
                margin-bottom: 1rem !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .table {
                margin-bottom: 1rem !important;
                width: 100% !important;
                border-collapse: collapse !important;
            }
            .table-bordered,
            .table-bordered th,
            .table-bordered td {
                border: 1px solid #cbd5e1 !important;
            }
            .table td, .table th {
                padding: 0.3rem 0.45rem !important;
                font-size: 10px !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group;
            }
            .bg-light, .badge, .table-light {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="bi bi-printer"></i> Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header-bg d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
                <span class="small opacity-75">Inventory Valuation & Stock Status Report</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Inventory Report</h4>
                <span class="font-monospace opacity-75">Generated : {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="border rounded p-3 bg-light text-center">
                    <div class="row font-outfit fw-bold">
                        <div class="col-3">Total Products: {{ number_format($totalProducts) }}</div>
                        <div class="col-3 border-start border-end">Stock Units: {{ number_format($totalUnitsInStock) }}</div>
                        <div class="col-3 border-end">Cost Value: ₹{{ number_format($totalCostValue, 2) }}</div>
                        <div class="col-3">Retail Value: ₹{{ number_format($totalRetailValue, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-dark mb-2">Inventory Products List</h6>
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 35px;">#</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Brand</th>
                    <th>HSN Code</th>
                    <th class="text-center">Stock Qty</th>
                    <th class="text-end">Cost Price (₹)</th>
                    <th class="text-end">Selling Price (₹)</th>
                    <th class="text-end">Total Cost Value (₹)</th>
                    <th class="text-end">Total Retail Value (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $index => $product)
                    @php
                        $cVal = $product->stock_quantity * $product->cost_price;
                        $rVal = $product->stock_quantity * $product->selling_price;
                    @endphp
                    <tr>
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td class="fw-semibold">{{ $product->name }}</td>
                        <td>{{ $product->category->name ?? 'N/A' }}</td>
                        <td>{{ $product->brand->name ?? 'N/A' }}</td>
                        <td class="font-monospace">{{ $product->hsn_code ?? 'N/A' }}</td>
                        <td class="text-center fw-bold">{{ number_format($product->stock_quantity) }} {{ $product->unit ?? 'Pcs' }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($product->cost_price, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($product->selling_price, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($cVal, 2) }}</td>
                        <td class="text-end fw-bold font-monospace">₹{{ number_format($rVal, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-3 text-muted">No inventory products found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($products) > 0)
                <tfoot class="table-light border-top font-outfit fw-bold">
                    <tr>
                        <td colspan="6" class="text-end">TOTALS:</td>
                        <td colspan="2"></td>
                        <td class="text-end font-monospace">₹{{ number_format($totalCostValue, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($totalRetailValue, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <div class="mt-5 text-center text-muted small border-top pt-3">
            Generated by {{ auth()->user()->name }} | {{ auth()->user()->firm->name ?? 'ERP Solution' }}
        </div>
    </div>
</body>
</html>
