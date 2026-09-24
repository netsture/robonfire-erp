<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Return Products Report - {{ auth()->user()->firm->name ?? 'ERP Solution' }}</title>
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
                <span class="small opacity-75">Returned Products & Material Summary Report</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Return Products Report</h4>
                <span class="font-monospace opacity-75">Generated : {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="border rounded p-3 bg-light text-center">
                    <div class="row font-outfit fw-bold">
                        <div class="col-3">Total Return Entries: {{ number_format($totalReturnCount) }}</div>
                        <div class="col-3 border-start border-end">Total Units Returned: {{ number_format($totalUnitsReturned) }}</div>
                        <div class="col-3 border-end">Subtotal Value: ₹{{ number_format($totalSubtotal, 2) }}</div>
                        <div class="col-3">Grand Total Value: ₹{{ number_format($totalGrandTotal, 2) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-dark mb-2">Returned Material List</h6>
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 35px;">#</th>
                    <th>Return No</th>
                    <th class="text-center">Return Date</th>
                    <th>Customer Name</th>
                    <th>Project Name</th>
                    <th>Return Reason</th>
                    <th class="text-end">Subtotal (₹)</th>
                    <th class="text-end">Tax (₹)</th>
                    <th class="text-end">Discount (₹)</th>
                    <th class="text-end">Shipping Cost (₹)</th>
                    <th class="text-end">Grand Total (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $index => $return)
                    <tr>
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td class="font-monospace fw-semibold">{{ $return->return_number }}</td>
                        <td class="text-center font-monospace">{{ $return->return_date ? \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') : 'N/A' }}</td>
                        <td>{{ $return->customer->company_name ?? 'N/A' }}</td>
                        <td>{{ $return->project_name ?? 'N/A' }}</td>
                        <td>{{ $return->return_reason ?? 'N/A' }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($return->subtotal, 2) }}</td>
                        <td class="text-end font-monospace">+₹{{ number_format($return->tax_amount, 2) }}</td>
                        <td class="text-end font-monospace">-₹{{ number_format($return->discount_amount ?? 0, 2) }}</td>
                        <td class="text-end font-monospace">+₹{{ number_format($return->shipping_cost ?? 0, 2) }}</td>
                        <td class="text-end fw-bold font-monospace">₹{{ number_format($return->grand_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center py-3 text-muted">No return records found.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($returns) > 0)
                <tfoot class="table-light border-top font-outfit fw-bold">
                    <tr>
                        <td colspan="6" class="text-end">TOTALS:</td>
                        <td class="text-end font-monospace">₹{{ number_format($returns->sum('subtotal'), 2) }}</td>
                        <td class="text-end font-monospace">+₹{{ number_format($totalTaxAmount, 2) }}</td>
                        <td class="text-end font-monospace">-₹{{ number_format($totalDiscountAmount, 2) }}</td>
                        <td class="text-end font-monospace">+₹{{ number_format($totalShippingCost, 2) }}</td>
                        <td class="text-end font-monospace">₹{{ number_format($totalGrandTotal, 2) }}</td>
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
