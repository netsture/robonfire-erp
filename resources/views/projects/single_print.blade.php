<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Project Report - {{ $project->project_name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; padding: 2rem; background: #fff; color: #1e293b; }
        .invoice-card { max-width: 950px; margin: 0 auto; border: 1px solid #cbd5e1; padding: 2rem; border-radius: 0.75rem; }
        .invoice-header-bg { background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%); color: #fff; padding: 1.25rem 1.5rem; border-radius: 0.5rem; margin-bottom: 1.5rem; }
        .table td, .table th { padding: 0.5rem 0.6rem; font-size: 12px; }
        
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
                padding: 0.35rem 0.5rem !important;
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
        <button onclick="window.print()" class="btn btn-primary px-4 me-2"><i class="bi bi-printer"></i> Print Report</button>
        <button onclick="window.close()" class="btn btn-secondary px-4">Close</button>
    </div>

    <div class="invoice-card">
        <!-- Header Branding -->
        <div class="invoice-header-bg d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-0">{{ $project->firm->name ?? auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
                <span class="small opacity-75">Project Entry & Financial Expense Breakdown Report</span>
            </div>
            <div class="text-end">
                <h4 class="fw-bold mb-0 text-uppercase">Project Report</h4>
                <span class="font-monospace opacity-75">Generated : {{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</span>
            </div>
        </div>

        <!-- Project Overview Box -->
        <div class="border rounded p-3 mb-4 bg-light">
            <div class="row g-3">
                <div class="col-6 col-md-4">
                    <span class="text-muted small text-uppercase d-block fw-semibold">Project Name</span>
                    <strong class="text-dark fs-6">{{ $project->project_name }}</strong>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted small text-uppercase d-block fw-semibold">PO Number</span>
                    <span class="font-monospace fw-bold text-dark">{{ $project->po_number ?? 'N/A' }}</span>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted small text-uppercase d-block fw-semibold">PO Date</span>
                    <span class="fw-semibold text-dark">{{ $project->po_date ? $project->po_date->format('d-m-Y') : 'N/A' }}</span>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted small text-uppercase d-block fw-semibold">Organization / Firm</span>
                    <span class="fw-semibold text-dark">{{ $project->firm->name ?? 'N/A' }}</span>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted small text-uppercase d-block fw-semibold">Record Created Date</span>
                    <span class="font-monospace text-dark">{{ $project->created_at ? $project->created_at->format('d-m-Y') : 'N/A' }}</span>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted small text-uppercase d-block fw-semibold">Project Status</span>
                    <span class="badge bg-success text-white text-uppercase">ACTIVE</span>
                </div>
            </div>
        </div>

        <!-- Summary Metrics Row -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="border rounded p-3 text-center">
                    <div class="row font-outfit fw-bold">
                        <div class="col-4">
                            <span class="text-muted small d-block text-uppercase">PO Amount</span>
                            <span class="fs-5 text-dark">₹{{ number_format($project->po_amount, 2) }}</span>
                        </div>
                        <div class="col-4 border-start border-end">
                            <span class="text-muted small d-block text-uppercase">Total Expenses</span>
                            <span class="fs-5 text-danger">₹{{ number_format($totalExpenses, 2) }}</span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted small d-block text-uppercase">Remaining Balance</span>
                            <span class="fs-5 {{ $remainingBalance >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($remainingBalance, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expenses Table -->
        <h6 class="fw-bold text-dark mb-2">Itemized Project Expenses ({{ $project->expenses->count() }})</h6>
        <table class="table table-bordered align-middle mb-4">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 40px;">#</th>
                    <th style="width: 110px;">Date</th>
                    <th>Item Name</th>
                    <th>Description</th>
                    <th class="text-center" style="width: 120px;">Attachment</th>
                    <th class="text-end" style="width: 130px;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($project->expenses as $index => $expense)
                    <tr>
                        <td class="text-center fw-bold">{{ $index + 1 }}</td>
                        <td class="font-monospace">{{ $expense->expense_date ? $expense->expense_date->format('d-m-Y') : 'N/A' }}</td>
                        <td class="fw-semibold">{{ $expense->item_name }}</td>
                        <td class="text-muted">{{ $expense->description ?? '-' }}</td>
                        <td class="text-center small">
                            @if($expense->document_path)
                                <span class="badge bg-light text-primary border">Attached</span>
                            @else
                                <span class="badge bg-light text-muted border">No File</span>
                            @endif
                        </td>
                        <td class="text-end fw-bold text-danger">₹{{ number_format($expense->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No expenses recorded for this project entry.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($project->expenses->count() > 0)
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="5" class="text-end">Total Expenses:</td>
                        <td class="text-end text-danger fs-6">₹{{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                </tfoot>
            @endif
        </table>

        <!-- Footer -->
        <div class="mt-5 text-center text-muted small border-top pt-3">
            Generated by {{ auth()->user()->name }} | {{ $project->firm->name ?? auth()->user()->firm->name ?? 'ERP Solution' }}
        </div>
    </div>
</body>
</html>
