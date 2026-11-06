<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Activity Log Report - {{ auth()->user()->firm->name ?? 'ERP Solution' }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2rem; background: #fff; color: #1e293b; }
        .invoice-card { max-width: 1000px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 2rem; border-radius: 0.75rem; }
        .invoice-header-bg { background: linear-gradient(135deg, #0f172a 0%, #312e81 100%); color: #fff; padding: 1.25rem 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
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
                <span class="small opacity-75">Product Activity & Change Audit Trail Report</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Product Log Audit</h4>
                <span class="font-monospace opacity-75">Generated: {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12">
                <span class="text-muted small fw-bold text-uppercase">AUDIT SCOPE:</span>
                <h6 class="fw-bold text-dark mt-1 mb-1">
                    {{ $selectedUser ? 'Filtered by User: ' . $selectedUser->name . ' (' . ucfirst($selectedUser->role) . ')' : 'All System Users' }}
                </h6>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-12">
                <div class="border rounded p-3 bg-light text-center">
                    <div class="row fw-bold">
                        <div class="col-3">Total Logs: {{ number_format($totalLogs) }}</div>
                        <div class="col-3 border-start border-end text-success">Products Added: {{ number_format($totalCreated) }}</div>
                        <div class="col-3 border-end text-primary">Products Updated: {{ number_format($totalUpdated) }}</div>
                        <div class="col-3 text-danger">Products Deleted: {{ number_format($totalDeleted) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 110px;">Date & Time</th>
                        <th style="width: 80px;">Action</th>
                        <th>Product Details</th>
                        <th style="width: 140px;">Performed By</th>
                        <th>Change Summary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $index => $log)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                            <td class="font-monospace small">
                                {{ $log->created_at ? $log->created_at->format('d-m-Y H:i') : 'N/A' }}
                            </td>
                            <td>
                                @if($log->action === 'create')
                                    <span class="badge bg-success text-white">Added</span>
                                @elseif($log->action === 'update')
                                    <span class="badge bg-primary text-white">Updated</span>
                                @elseif($log->action === 'delete')
                                    <span class="badge bg-danger text-white">Deleted</span>
                                @else
                                    <span class="badge bg-secondary text-white">{{ ucfirst($log->action) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $log->product_name }}</div>
                                <div class="small text-muted">
                                    @if($log->product_identifier) Code: #{{ $log->product_identifier }} @endif
                                    @if($log->category_name) | Cat: {{ $log->category_name }} @endif
                                    @if($log->brand_name) | Brand: {{ $log->brand_name }} @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->user->name ?? 'System' }}</div>
                                <div class="small text-muted">{{ ucfirst($log->user->role ?? 'User') }}</div>
                            </td>
                            <td class="small">
                                {{ $log->description ?? 'N/A' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No product activity logs recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-3 border-top text-center text-muted small">
            This is an automated system audit log report generated from {{ auth()->user()->firm->name ?? 'ERP Solution' }}.
        </div>
    </div>

    <script>
        window.addEventListener('load', function() {
            // Auto open print dialog if not cancelled
            // window.print();
        });
    </script>
</body>
</html>

