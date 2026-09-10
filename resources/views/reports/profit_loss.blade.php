@extends('layouts.app')

@section('title', 'Detailed Profit & Loss Statement')
@section('page_title', 'Detailed Profit & Loss (Income Statement)')
@section('page_subtitle', 'Comprehensive income statement detailing sales revenues, procurement expenses, tax flows, and net profit margins')

@section('header_actions')
    <button onclick="window.print()" class="btn btn-primary rounded-pill px-3 font-outfit fw-medium me-2">
        <i class="bi bi-printer me-1"></i> Print Statement
    </button>
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Print Header Branding -->
<div class="d-none d-print-block mb-4 text-center">
    <h3 class="fw-bold font-outfit mb-1">{{ auth()->user()->firm->name ?? 'ERP Solution' }}</h3>
    <h5 class="text-muted font-outfit mb-0">Official Profit & Loss (Income Statement) Report</h5>
    <span class="small text-muted font-monospace">Generated on: {{ now()->format('d M Y, h:i A') }}</span>
    <hr class="my-3">
</div>

<!-- KPI Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Sales Revenue</span>
            <h3 class="fw-bold font-outfit text-primary mt-2 mb-0">₹{{ number_format($salesGrandTotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Total Procurement Cost</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">₹{{ number_format($purchaseGrandTotal, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Gross Operating Margin</span>
            <h3 class="fw-bold font-outfit text-dark mt-2 mb-0">₹{{ number_format($grossProfit, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
            <span class="text-white-50 small fw-semibold text-uppercase">Net Operating Profit</span>
            <h3 class="fw-bold font-outfit text-white mt-2 mb-0">₹{{ number_format($netProfit, 2) }}</h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Profit Margin %</span>
            <h3 class="fw-bold font-outfit {{ $profitMargin >= 0 ? 'text-success' : 'text-danger' }} mt-2 mb-0">
                {{ number_format($profitMargin, 1) }}%
            </h3>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-2">
        <div class="card card-custom border-0 p-3 bg-light">
            <span class="text-muted small fw-semibold text-uppercase">Net Cash Collections</span>
            <h3 class="fw-bold font-outfit text-success mt-2 mb-0">₹{{ number_format($salesPaid - $purchasePaid, 2) }}</h3>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4 no-print">
    <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 align-items-end">
        @if(auth()->user()->isSuperAdmin() && count($firms) > 0)
            <div class="col-12 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Firm</label>
                <select name="firm_id" class="form-select form-select-sm">
                    <option value="">All Firms</option>
                    @foreach($firms as $firm)
                        <option value="{{ $firm->id }}" {{ request('firm_id') == $firm->id ? 'selected' : '' }}>{{ $firm->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <div class="col-12 col-md-3">
            <label class="form-label small fw-semibold text-muted mb-1">Start Date (dd-mm-yyyy)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                <input type="text" name="start_date" class="form-control form-control-sm datepicker-ddmmyyyy" placeholder="dd-mm-yyyy" value="{{ request('start_date') ? (\Carbon\Carbon::canBeCreatedFromFormat(request('start_date'), 'd-m-Y') ? request('start_date') : \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y')) : '' }}" autocomplete="off">
            </div>
        </div>

        <div class="col-12 col-md-3">
            <label class="form-label small fw-semibold text-muted mb-1">End Date (dd-mm-yyyy)</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light"><i class="bi bi-calendar3"></i></span>
                <input type="text" name="end_date" class="form-control form-control-sm datepicker-ddmmyyyy" placeholder="dd-mm-yyyy" value="{{ request('end_date') ? (\Carbon\Carbon::canBeCreatedFromFormat(request('end_date'), 'd-m-Y') ? request('end_date') : \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y')) : '' }}" autocomplete="off">
            </div>
        </div>

        <div class="col-12 col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-funnel me-1"></i> Generate Statement</button>
            <a href="{{ route('reports.profit-loss') }}" class="btn btn-sm btn-light border" title="Reset Filters"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<!-- Net Profit Header Banner -->
<div class="card card-custom border-0 p-4 mb-4 {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <span class="text-white-50 small fw-semibold text-uppercase tracking-wider">NET OPERATING PROFIT / (LOSS)</span>
            <h1 class="fw-bold font-outfit mt-2 mb-0">₹{{ number_format($netProfit, 2) }}</h1>
            <span class="small text-white-50">
                Operating Profit Margin: <strong>{{ number_format($profitMargin, 2) }}%</strong> | Total Revenue: ₹{{ number_format($salesGrandTotal, 2) }} vs Total Expense: ₹{{ number_format($purchaseGrandTotal, 2) }}
            </span>
        </div>
        <div class="fs-1 text-white-50">
            <i class="bi {{ $netProfit >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i>
        </div>
    </div>
</div>

<!-- Detailed P&L Income Statement Table -->
<div class="card card-custom border-0 p-4">
    <h5 class="fw-bold font-outfit text-dark mb-3">Comprehensive Income Statement Ledger</h5>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width: 70%;">Financial Statement Account / Line Item</th>
                    <th class="text-end" style="width: 30%;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody class="fs-6">

                <!-- Section A: Operating Revenue & Inflows -->
                <tr class="table-light fw-bold text-uppercase">
                    <td colspan="2"><i class="bi bi-arrow-up-right-circle-fill me-2 text-success"></i>1. Operating Sales Revenue & Inflows ({{ $salesCount }} Orders)</td>
                </tr>
                <tr>
                    <td class="ps-4">Gross Sales Subtotal (Line Item Total)</td>
                    <td class="text-end font-monospace fw-semibold text-dark">₹{{ number_format($grossSales, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Less: Sales Discounts & Allowances</td>
                    <td class="text-end font-monospace text-danger">-₹{{ number_format($salesDiscounts, 2) }}</td>
                </tr>
                <tr class="fw-bold bg-light-subtle">
                    <td class="ps-4">Net Operating Sales Revenue</td>
                    <td class="text-end font-monospace text-primary">₹{{ number_format($netSales, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Plus: Sales Tax Collected from Customers</td>
                    <td class="text-end font-monospace text-info">+₹{{ number_format($salesTaxes, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Plus: Shipping & Delivery Charges Collected</td>
                    <td class="text-end font-monospace text-info">+₹{{ number_format($salesShipping, 2) }}</td>
                </tr>
                <tr class="table-primary fw-bold">
                    <td class="ps-4">TOTAL GROSS SALES REVENUE (INCL. TAX & FREIGHT)</td>
                    <td class="text-end font-monospace text-primary fs-6">₹{{ number_format($salesGrandTotal, 2) }}</td>
                </tr>

                <!-- Section B: Cost of Goods & Procurement (COGS) -->
                <tr class="table-light fw-bold text-uppercase">
                    <td colspan="2"><i class="bi bi-arrow-down-left-circle-fill me-2 text-danger"></i>2. Cost of Goods Sold & Procurement Expenses ({{ $purchasesCount }} Orders)</td>
                </tr>
                <tr>
                    <td class="ps-4">Gross Purchase Expense Subtotal</td>
                    <td class="text-end font-monospace text-dark">₹{{ number_format($grossPurchases, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Less: Purchase Discounts Received from Vendors</td>
                    <td class="text-end font-monospace text-success">-₹{{ number_format($purchaseDiscounts, 2) }}</td>
                </tr>
                <tr class="fw-bold bg-light-subtle">
                    <td class="ps-4">Net Procurement Cost</td>
                    <td class="text-end font-monospace text-danger">₹{{ number_format($netPurchases, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Plus: Procurement Tax Paid to Vendors</td>
                    <td class="text-end font-monospace text-muted">+₹{{ number_format($purchaseTaxes, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Plus: Freight & Transportation Costs Paid</td>
                    <td class="text-end font-monospace text-muted">+₹{{ number_format($purchaseShipping, 2) }}</td>
                </tr>
                <tr class="table-danger fw-bold">
                    <td class="ps-4">TOTAL GROSS PROCUREMENT EXPENDITURE (INCL. TAX & FREIGHT)</td>
                    <td class="text-end font-monospace text-danger fs-6">₹{{ number_format($purchaseGrandTotal, 2) }}</td>
                </tr>

                <!-- Section C: Cash Collections & Payables Position -->
                <tr class="table-light fw-bold text-uppercase">
                    <td colspan="2"><i class="bi bi-wallet2 me-2 text-primary"></i>3. Cash Collections & Payables Summary</td>
                </tr>
                <tr>
                    <td class="ps-4">Customer Collections Received</td>
                    <td class="text-end font-monospace text-success">₹{{ number_format($salesPaid, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Outstanding Customer Receivables (Sales Balance Due)</td>
                    <td class="text-end font-monospace text-warning">₹{{ number_format($salesReceivables, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4">Vendor Payments Disbursed</td>
                    <td class="text-end font-monospace text-dark">₹{{ number_format($purchasePaid, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Outstanding Vendor Payables (Purchase Balance Due)</td>
                    <td class="text-end font-monospace text-danger">₹{{ number_format($purchasePayables, 2) }}</td>
                </tr>

                <!-- Section D: Final Net Operating Profit / Loss Summary -->
                <tr class="table-dark fs-5 fw-bold">
                    <td class="py-3">NET OPERATING PROFIT / (LOSS)</td>
                    <td class="text-end py-3 font-monospace {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        ₹{{ number_format($netProfit, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .no-print, nav, sidebar, header, .btn, .header_actions { display: none !important; }
    body { background: #fff !important; padding: 0 !important; color: #000 !important; }
    .card { border: none !important; box-shadow: none !important; }
    .table { width: 100% !important; border: 1px solid #dee2e6 !important; }
}
</style>

@endsection
