<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Stock Ledger Report - {{ auth()->user()->firm->name ?? 'ERP Solution' }}</title>
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
                <span class="small opacity-75">Product-Wise Stock Movement Audit Ledger</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Stock Movement Report</h4>
                <span class="font-monospace opacity-75">Generated : {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        @if($selectedProduct)
            <div class="row mb-3">
                <div class="col-6">
                    <span class="text-muted small fw-bold text-uppercase">SELECTED PRODUCT</span>
                    <h5 class="fw-bold text-dark mt-1 mb-1">{{ $selectedProduct->name }}</h5>
                    <div class="small text-muted">Category: <strong>{{ $selectedProduct->category->name ?? 'N/A' }}</strong> | Brand: <strong>{{ $selectedProduct->brand->name ?? 'N/A' }}</strong></div>
                    <div class="small text-muted">HSN Code: <strong>{{ $selectedProduct->hsn_code ?? 'N/A' }}</strong> | Unit: <strong>{{ $selectedProduct->unit ?? 'Pcs' }}</strong></div>
                </div>
                <div class="col-6 text-end">
                    <span class="text-muted small fw-bold text-uppercase">PRICING & CURRENT STOCK</span>
                    <div class="small text-dark mt-1">Current Stock Qty: <strong>{{ number_format($summary['current_stock']) }} {{ $selectedProduct->unit ?? 'Pcs' }}</strong></div>
                    <div class="small text-dark">Cost Price: <strong>₹{{ number_format($summary['cost_price'], 2) }}</strong> | Selling Price: <strong>₹{{ number_format($summary['selling_price'], 2) }}</strong></div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="border rounded p-3 bg-light text-center">
                        <div class="row font-outfit fw-bold">
                            <div class="col-4">Purchased: {{ number_format($summary['total_purchased_qty']) }} Units</div>
                            <div class="col-4 border-start border-end">Sold: {{ number_format($summary['total_sold_qty']) }} Units</div>
                            <div class="col-4">Returned: {{ number_format($summary['total_returned_qty']) }} Units</div>
                        </div>
                    </div>
                </div>
            </div>

            <h6 class="fw-bold text-dark mb-2">Stock Movement History</h6>
            <table class="table table-bordered align-middle mb-4">
                <thead class="table-light">
                    <tr>
                        <th class="text-center" style="width: 35px;">#</th>
                        <th class="text-center">Date</th>
                        <th>Type</th>
                        <th class="text-center">In / Out</th>
                        <th>Ref / Invoice No</th>
                        <th>Party / User</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Unit Rate (₹)</th>
                        <th class="text-end">Total Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movementHistory as $index => $item)
                        <tr>
                            <td class="text-center fw-bold">{{ $index + 1 }}</td>
                            <td class="text-center font-monospace">{{ \Carbon\Carbon::parse($item['timestamp'])->format('d-m-Y') }}</td>
                            <td>{{ $item['type'] }}</td>
                            <td class="text-center fw-bold">
                                @if($item['direction'] === 'IN')
                                    <span class="badge bg-success text-white px-2">IN</span>
                                @else
                                    <span class="badge bg-danger text-white px-2">OUT</span>
                                @endif
                            </td>
                            <td class="font-monospace fw-semibold">{{ $item['ref_no'] }}</td>
                            <td>{{ $item['party'] }}</td>
                            <td class="text-center fw-bold">{{ number_format($item['quantity']) }}</td>
                            <td class="text-end font-monospace">₹{{ number_format($item['rate'], 2) }}</td>
                            <td class="text-end fw-bold font-monospace">₹{{ number_format($item['amount'], 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-3 text-muted">No stock movement recorded for this product.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        @else
            <div class="alert alert-info text-center my-4">No product selected for stock report.</div>
        @endif

        <div class="mt-5 text-center text-muted small border-top pt-3">
            Generated by {{ auth()->user()->name }} | {{ auth()->user()->firm->name ?? 'ERP Solution' }}
        </div>
    </div>
</body>
</html>
