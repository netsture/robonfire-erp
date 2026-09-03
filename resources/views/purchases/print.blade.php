<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Receipt / Invoice - {{ $purchase->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2rem; background: #fff; color: #1e293b; }
        .invoice-card { max-width: 900px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 2rem; border-radius: 0.75rem; }
        .invoice-header-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #fff; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .invoice-card { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="bi bi-printer"></i> Print Purchase Receipt</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>

    <div class="invoice-card">
        <div class="invoice-header-bg d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0">{{ $purchase->firm->name ?? 'ERP Solution' }}</h3>
                <span class="small opacity-75">Procurement Receipt & Invoice</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Purchase Invoice</h4>
                <span class="font-monospace opacity-75">Invoice #: {{ $purchase->invoice_number }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <span class="text-muted small fw-bold text-uppercase tracking-wider">SUPPLIER / VENDOR</span>
                <h5 class="fw-bold text-dark mt-2 mb-1">{{ $purchase->supplier->company_name ?? 'N/A' }}</h5>
                @if($purchase->supplier && $purchase->supplier->address)
                    <div class="small text-muted mb-1"><strong>Address:</strong> {{ $purchase->supplier->address }}</div>
                @endif
                @if($purchase->supplier && $purchase->supplier->gst_number)
                    <div class="small text-muted mb-1"><strong>GST No:</strong> {{ $purchase->supplier->gst_number }}</div>
                @endif
                @if($purchase->supplier && $purchase->supplier->phone)
                    <div class="small text-muted mb-1"><strong>Contact No:</strong> {{ $purchase->supplier->phone }}</div>
                @endif
                @if($purchase->supplier && $purchase->supplier->email)
                    <div class="small text-muted mb-1"><strong>Email:</strong> {{ $purchase->supplier->email }}</div>
                @endif
            </div>
            <div class="col-6 text-end">
                <span class="text-muted small fw-bold text-uppercase tracking-wider">PURCHASE DETAILS</span>
                <div class="small text-dark mt-2 mb-1">Purchase Date: <strong>{{ $purchase->purchase_date }}</strong></div>
                <div class="small text-dark mb-1">Invoice No: <strong>#{{ $purchase->invoice_number }}</strong></div>
                @if($purchase->project_name)
                    <div class="small text-dark mb-1">Project Name: <strong>{{ $purchase->project_name }}</strong></div>
                @endif
                <div class="small text-dark mb-1">Payment Status: 
                    @if($purchase->payment_status === 'paid')
                        <span class="badge bg-success text-uppercase px-2">PAID</span>
                    @elseif($purchase->payment_status === 'unpaid')
                        <span class="badge bg-danger text-uppercase px-2">UNPAID</span>
                    @else
                        <span class="badge bg-warning text-dark text-uppercase px-2">PENDING</span>
                    @endif
                </div>
                <div class="small text-dark mb-1">Recorded By: <strong>{{ $purchase->user->name ?? 'Admin' }}</strong></div>
            </div>
        </div>

        <h6 class="fw-bold text-dark mb-2">Order Line Items</h6>
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th>Category</th>
                    <th>Product Item (Brand)</th>
                    <th>HSN Code</th>
                    <th>Type</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Cost (₹)</th>
                    <th class="text-end">Total (₹)</th>
                    <th class="text-center">Tax (%)</th>
                    <th class="text-end">Tax (₹)</th>
                    <th class="text-end">Total w/ Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    @php
                        $prod = $item->product;
                        $taxPct = $prod->tax_percent ?? 0;
                        $lineTax = ($item->subtotal * $taxPct) / 100;
                        $totalWithTax = $item->subtotal + $lineTax;
                    @endphp
                    <tr>
                        <td>{{ $prod->category->name ?? 'N/A' }}</td>
                        <td class="fw-semibold">{{ $prod->name ?? 'Product' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                        <td class="font-monospace small text-muted">{{ $prod->hsn_code ?? 'N/A' }}</td>
                        <td>{{ $prod->unit ?? 'Pcs' }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                        <td class="text-end">₹{{ number_format($item->unit_cost, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($item->subtotal, 2) }}</td>
                        <td class="text-center">{{ number_format($taxPct, 2) }}%</td>
                        <td class="text-end text-muted font-monospace">₹{{ number_format($lineTax, 2) }}</td>
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
                        <strong>₹{{ number_format($purchase->subtotal, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Tax Amount:</span>
                        <span>+₹{{ number_format($purchase->tax_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Discount:</span>
                        <span>-₹{{ number_format($purchase->discount_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Shipping Cost:</span>
                        <span>+₹{{ number_format($purchase->shipping_cost, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fs-5 mb-1">
                        <strong>Grand Total:</strong>
                        <strong class="text-primary">₹{{ number_format($purchase->grand_total, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Amount Paid:</span>
                        <strong class="text-success">₹{{ number_format($purchase->paid_amount, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        @if($purchase->notes)
            <div class="mt-4 p-3 bg-light rounded border">
                <span class="text-muted small fw-bold d-block mb-1">NOTES</span>
                <div class="small">{{ $purchase->notes }}</div>
            </div>
        @endif

        <div class="mt-5 text-center text-muted small border-top pt-3">
            Thank you for your business! | {{ $purchase->firm->name ?? 'ERP Solution' }}
        </div>
    </div>
</body>
</html>
