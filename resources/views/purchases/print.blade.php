<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Receipt - {{ $purchase->reference_no }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: sans-serif; padding: 2rem; background: #fff; color: #000; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4">
        <button onclick="window.print()" class="btn btn-primary px-4 me-2">Print Receipt</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close Window</button>
    </div>

    <div class="border p-4 rounded-3">
        <div class="row align-items-center mb-4">
            <div class="col-6">
                <h3 class="fw-bold mb-0">ERP SYSTEM</h3>
                <span class="text-muted">Procurement Receipt</span>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold text-uppercase">#{{ $purchase->reference_no }}</h4>
                <div class="small">Date: {{ $purchase->purchase_date }}</div>
            </div>
        </div>

        <hr>

        <div class="row mb-4">
            <div class="col-6">
                <strong>Supplier / Vendor:</strong><br>
                {{ $purchase->supplier->name }}<br>
                {{ $purchase->supplier->company_name }}<br>
                Phone: {{ $purchase->supplier->phone }}
            </div>
            <div class="col-6 text-end">
                <strong>Status:</strong> {{ strtoupper($purchase->payment_status) }}<br>
                <strong>Received By:</strong> {{ $purchase->user->name ?? 'Admin' }}
            </div>
        </div>

        <table class="table table-bordered mb-4">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>SKU</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Unit Cost</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? 'N/A' }}</td>
                        <td>{{ $item->product->sku ?? '' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-end">${{ number_format($item->unit_cost, 2) }}</td>
                        <td class="text-end">${{ number_format($item->subtotal, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row justify-content-end">
            <div class="col-5">
                <div class="d-flex justify-content-between">
                    <span>Subtotal:</span>
                    <strong>${{ number_format($purchase->subtotal, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Discount:</span>
                    <span>-${{ number_format($purchase->discount_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Shipping Cost:</span>
                    <span>+${{ number_format($purchase->shipping_cost, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-5">
                    <strong>Grand Total:</strong>
                    <strong>${{ number_format($purchase->grand_total, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Amount Paid:</span>
                    <strong>${{ number_format($purchase->paid_amount, 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
