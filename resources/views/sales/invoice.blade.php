<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales Invoice - {{ $sale->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2.5rem; background: #fff; color: #1e293b; }
        .invoice-card { max-width: 850px; margin: 0 auto; border: 1px solid #e2e8f0; padding: 2.5rem; border-radius: 1rem; }
        .invoice-header-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #fff; padding: 1.5rem; border-radius: 0.75rem; margin-bottom: 2rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .invoice-card { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">Print Invoice</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header-bg d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold mb-0">ERP Solution</h2>
                <span class="small opacity-75">Enterprise Sales Invoice</span>
            </div>
            <div class="text-end">
                <h3 class="fw-bold mb-0">INVOICE</h3>
                <span class="font-monospace opacity-75">#{{ $sale->invoice_number }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <span class="text-muted small fw-bold uppercase">CUSTOMER / BILLED TO</span>
                <h5 class="fw-bold mb-1 mt-1">{{ $sale->customer->name }}</h5>
                @if($sale->customer->company_name)
                    <div>{{ $sale->customer->company_name }}</div>
                @endif
                <div>Phone: {{ $sale->customer->phone }}</div>
                <div>Address: {{ $sale->customer->address ?? 'N/A' }}</div>
            </div>
            <div class="col-6 text-end">
                <span class="text-muted small fw-bold uppercase">INVOICE SUMMARY</span>
                <div class="mt-1">Date: <strong>{{ $sale->sale_date }}</strong></div>
                @if($sale->project_name)
                    <div>Project: <strong>{{ $sale->project_name }}</strong></div>
                @endif
                @if($sale->vehicle_number)
                    <div>Vehicle No: <strong>{{ $sale->vehicle_number }}</strong></div>
                @endif
                <div>Payment Status: <span class="badge bg-dark text-uppercase px-3">{{ $sale->payment_status }}</span></div>
                <div>Issuer: {{ $sale->user->name ?? 'System' }}</div>
            </div>
        </div>

        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Product Item (Brand)</th>
                    <th>HSN Code</th>
                    <th>Unit</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Selling Price (₹)</th>
                    <th class="text-center">Tax (%)</th>
                    <th class="text-end">Total (₹)</th>
                    <th class="text-end">Total w/ Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    @php
                        $prod = $item->product;
                        $taxPct = $prod->tax_percent ?? 0;
                        $lineTax = ($item->subtotal * $taxPct) / 100;
                        $totalWithTax = $item->subtotal + $lineTax;
                    @endphp
                    <tr>
                        <td>{{ $prod->category->name ?? 'N/A' }}</td>
                        <td class="fw-semibold">{{ $prod->name ?? 'Product' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                        <td class="font-monospace small">{{ $prod->hsn_code ?? 'N/A' }}</td>
                        <td>{{ $prod->unit ?? 'Pcs' }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-center">{{ number_format($taxPct, 2) }}%</td>
                        <td class="text-end">₹{{ number_format($item->subtotal, 2) }}</td>
                        <td class="text-end fw-bold">₹{{ number_format($totalWithTax, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-5">
                <div class="border rounded-3 p-3 bg-light">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <strong>₹{{ number_format($sale->subtotal, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Discount:</span>
                        <span>-₹{{ number_format($sale->discount_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Tax:</span>
                        <span>+₹{{ number_format($sale->tax_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Shipping:</span>
                        <span>+₹{{ number_format($sale->shipping_cost, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5">
                        <strong>Grand Total:</strong>
                        <strong class="text-primary">₹{{ number_format($sale->grand_total, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Amount Paid:</span>
                        <strong class="text-success">₹{{ number_format($sale->paid_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center text-muted small border-top pt-3">
            Thank you for your business! | ERP Enterprise Solutions
        </div>
    </div>
</body>
</html>
