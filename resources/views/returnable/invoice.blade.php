<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Invoice No - {{ $returnableMaterial->return_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2rem; background: #fff; color: #1e293b; }
        .invoice-card { max-width: 900px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 2rem; border-radius: 0.75rem; }
        .invoice-header-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #fff; padding: 1.25rem 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .table td, .table th { padding: 0.45rem 0.5rem; font-size: 12px; }
        
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
                font-size: 11px !important;
            }
            tr {
                page-break-inside: avoid !important;
            }
            thead {
                display: table-header-group;
            }
            .row.justify-content-end, .mt-4, .mt-5 {
                page-break-inside: avoid !important;
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
        <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="bi bi-printer"></i> Print Invoice</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header-bg d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0">{{ $returnableMaterial->firm->name ?? 'ERP Solution' }}</h3>
                <span class="small opacity-75">Return Material Delivery Challan & Invoice</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Return Invoice</h4>
                <span class="font-monospace opacity-75">Return No: {{ $returnableMaterial->return_number }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <span class="text-muted small fw-bold text-uppercase tracking-wider">CUSTOMER DETAILS</span>
                <h5 class="fw-bold text-dark mt-2 mb-1">{{ $returnableMaterial->customer->company_name ?? 'N/A' }}</h5>
                @if($returnableMaterial->customer && $returnableMaterial->customer->address)
                    <div class="small text-muted mb-1"><strong>Address:</strong> {{ $returnableMaterial->customer->address }}</div>
                @endif
                @if($returnableMaterial->customer && ($returnableMaterial->customer->gst_number ?? $returnableMaterial->customer->gstin))
                    <div class="small text-muted mb-1"><strong>Gst No:</strong> {{ $returnableMaterial->customer->gst_number ?? $returnableMaterial->customer->gstin }}</div>
                @endif
                @if($returnableMaterial->customer && $returnableMaterial->customer->phone)
                    <div class="small text-muted mb-1"><strong>Contact No:</strong> {{ $returnableMaterial->customer->phone }}</div>
                @endif
                @if($returnableMaterial->customer && $returnableMaterial->customer->email)
                    <div class="small text-muted mb-1"><strong>Email:</strong> {{ $returnableMaterial->customer->email }}</div>
                @endif
            </div>
            <div class="col-6 text-end">
                <span class="text-muted small fw-bold text-uppercase tracking-wider">RETURN DETAILS</span>
                <div class="small text-dark mt-2 mb-1">Return Date: <strong>{{ $returnableMaterial->return_date }}</strong></div>
                <div class="small text-dark mb-1">Return No: <strong>#{{ $returnableMaterial->return_number }}</strong></div>
                @if($returnableMaterial->project_name)
                    <div class="small text-dark mb-1">Project Name: <strong>{{ $returnableMaterial->project_name }}</strong></div>
                @endif
                @if($returnableMaterial->return_reason)
                    <div class="small text-dark mb-1">Reason for Return: <strong>{{ $returnableMaterial->return_reason }}</strong></div>
                @endif
                <div class="small text-dark mb-1">Recorded By: <strong>{{ $returnableMaterial->user->name ?? 'Admin' }}</strong></div>
            </div>
        </div>

        <h6 class="fw-bold text-dark mb-2">Returned Line Items</h6>
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Product Item (Brand)</th>
                    <th>HSN Code</th>
                    <th>Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Price (₹)</th>
                    <th class="text-end">Total (₹)</th>
                    <th class="text-center">Tax (%)</th>
                    <th class="text-end">Tax (₹)</th>
                    <th class="text-end">Total w/ Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach($returnableMaterial->items as $item)
                    @php
                        $prod = $item->product;
                        $taxPct = $prod->tax_percent ?? 0;
                        $lineTax = ($item->subtotal * $taxPct) / 100;
                        $totalWithTax = $item->subtotal + $lineTax;
                    @endphp
                    <tr>
                        <td>{{ $prod->category->name ?? 'N/A' }}</td>
                        <td class="fw-semibold">{{ $prod->name ?? 'Product' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                        <td class="font-monospace small text-dark">{{ $prod->hsn_code ?? 'N/A' }}</td>
                        <td>{{ $prod->unit ?? 'Pcs' }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($item->subtotal, 2) }}</td>
                        <td class="text-center">{{ number_format($taxPct, 2) }}%</td>
                        <td class="text-end text-dark font-monospace">₹{{ number_format($lineTax, 2) }}</td>
                        <td class="text-end fw-bold font-monospace">₹{{ number_format($totalWithTax, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-5">
                <div class="border rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <strong>₹{{ number_format($returnableMaterial->subtotal, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Tax Amount:</span>
                        <span>+₹{{ number_format($returnableMaterial->tax_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Discount Amount:</span>
                        <span>-₹{{ number_format($returnableMaterial->discount_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Shipping Cost:</span>
                        <span>+₹{{ number_format($returnableMaterial->shipping_cost, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5 mb-1">
                        <strong>Grand Total:</strong>
                        <strong class="text-primary">₹{{ number_format($returnableMaterial->grand_total, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($returnableMaterial->notes)
            <div class="mt-4 p-3 bg-light rounded border">
                <span class="text-muted small fw-bold d-block mb-1">RETURN REMARKS / NOTES</span>
                <div class="small">{{ $returnableMaterial->notes }}</div>
            </div>
        @endif

        <div class="mt-5 text-center text-muted small border-top pt-3">
            Thank you for your business! | {{ $returnableMaterial->firm->name ?? 'ERP Solution' }}
        </div>
    </div>
</body>
</html>
