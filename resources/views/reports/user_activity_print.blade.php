<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Activity Report - {{ auth()->user()->firm->name ?? 'ERP Solution' }}</title>
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
                <span class="small opacity-75">User-Wise Stock & Entry Details Audit Report</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">User Activity Audit</h4>
                <span class="font-monospace opacity-75">Generated : {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <span class="text-muted small fw-bold text-uppercase">AUDIT SCOPE</span>
                <h5 class="fw-bold text-dark mt-1 mb-1">{{ $selectedUser ? $selectedUser->name . ' (Role: '. ucfirst($selectedUser->role) .')' : 'All System Users' }}</h5>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="border rounded p-3 bg-light text-center">
                    <div class="row font-outfit fw-bold">
                        <div class="col-3">Purchases Entered: {{ number_format($summary['purchase_count']) }}</div>
                        <div class="col-3 border-start border-end">Sales Entered: {{ number_format($summary['sale_count']) }}</div>
                        <div class="col-3 border-end">Returns Entered: {{ number_format($summary['return_count']) }}</div>
                        <div class="col-3">Total Activity Entries: {{ number_format($summary['total_entries']) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <h6 class="fw-bold text-dark mb-2">Recorded Activity Entries Ledger</h6>
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 35px;">#</th>
                    <th class="text-center">Date</th>
                    <th>Entry Type</th>
                    <th>Ref / Invoice No</th>
                    <th>Party / Contact</th>
                    <th>Items Summary</th>
                    <th>Entered By</th>
                    <th class="text-end">Total Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entriesLedger as $index => $item)
                    <tr>
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td class="text-center font-monospace">{{ \Carbon\Carbon::parse($item['raw_date'])->format('d-m-Y') }}</td>
                        <td class="fw-semibold">{{ $item['entry_type'] }}</td>
                        <td class="font-monospace fw-semibold">{{ $item['ref_no'] }}</td>
                        <td>{{ $item['party'] }}</td>
                        <td>{{ $item['products_summary'] }}</td>
                        <td>{{ $item['entered_by'] }}</td>
                        <td class="text-end fw-bold font-monospace">₹{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-3 text-muted">No activity records found for this user/criteria.</td>
                    </tr>
                @endforelse
            </tbody>
            @if(count($entriesLedger) > 0)
                <tfoot class="table-light border-top font-outfit fw-bold">
                    <tr>
                        <td colspan="7" class="text-end">TOTAL AMOUNT:</td>
                        <td class="text-end font-monospace">₹{{ number_format(collect($entriesLedger)->sum('amount'), 2) }}</td>
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
