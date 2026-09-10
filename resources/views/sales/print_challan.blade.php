<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Delivery Challan - {{ $sale->invoice_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2.5rem; background: #fff; color: #1e293b; }
        .challan-card { max-width: 850px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 2.5rem; border-radius: 0.75rem; }
        .challan-header-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #fff; padding: 1.5rem; border-radius: 0.5rem; margin-bottom: 2rem; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            .challan-card { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">Print Challan</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>

    <div class="challan-card">
        <div class="challan-header-bg d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0">{{ $sale->firm->name ?? 'ERP Solution' }}</h3>
                <span class="small opacity-75">Goods Delivery Challan</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Delivery Challan</h4>
                <span class="font-monospace opacity-75">Challan No : {{ $sale->invoice_number }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-6">
                <span class="text-muted small fw-bold text-uppercase tracking-wider">CUSTOMER DETAILS</span>
                <h5 class="fw-bold text-dark mt-2 mb-1">{{ $sale->customer->company_name ?? 'N/A' }}</h5>
                @if($sale->customer->address)
                    <div class="small text-muted mb-1"><strong>Address:</strong> {{ $sale->customer->address }}</div>
                @endif
                @if($sale->customer->gst_number)
                    <div class="small text-muted mb-1"><strong>Gst No:</strong> {{ $sale->customer->gst_number }}</div>
                @endif
                @if($sale->customer->phone)
                    <div class="small text-muted mb-1"><strong>Contact No:</strong> {{ $sale->customer->phone }}</div>
                @endif
                @if($sale->customer->email)
                    <div class="small text-muted mb-1"><strong>Email:</strong> {{ $sale->customer->email }}</div>
                @endif
            </div>
            <div class="col-6 text-end">
                <span class="text-muted small fw-bold text-uppercase tracking-wider">DELIVERY DETAILS</span>
                <div class="small text-dark mt-2 mb-1">Challan Date: <strong>{{ $sale->sale_date }}</strong></div>
                <div class="small text-dark mb-1">Challan No: <strong>#{{ $sale->invoice_number }}</strong></div>
                @if($sale->project_name)
                    <div class="small text-dark mb-1">Project Name: <strong>{{ $sale->project_name }}</strong></div>
                @endif
                @if($sale->vehicle_number)
                    <div class="small text-dark mb-1">Vehicle No: <strong>{{ $sale->vehicle_number }}</strong></div>
                @endif
                <div class="small text-dark mb-1">Recorded By: <strong>{{ $sale->user->name ?? 'Admin' }}</strong></div>
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
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    @php
                        $prod = $item->product;
                    @endphp
                    <tr>
                        <td>{{ $prod->category->name ?? 'N/A' }}</td>
                        <td class="fw-semibold">{{ $prod->name ?? 'Product' }}{{ $prod->brand ? ' ('.$prod->brand->name.')' : '' }}</td>
                        <td class="font-monospace small text-dark">{{ $prod->hsn_code ?? 'N/A' }}</td>
                        <td>{{ $prod->unit ?? 'Pcs' }}</td>
                        <td class="text-center fw-bold">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if($sale->notes)
            <div class="mt-4 p-3 bg-light rounded border">
                <span class="text-muted small fw-bold d-block mb-1">SALES ORDER NOTES</span>
                <div class="small">{{ $sale->notes }}</div>
            </div>
        @endif

        <div class="mt-5 pt-4 border-top d-flex justify-content-between text-muted small">
            <div>
                <span>Received By (Signature)</span>
            </div>
            <div>
                <span>Authorized Signatory</span>
            </div>
        </div>
    </div>
</body>
</html>
