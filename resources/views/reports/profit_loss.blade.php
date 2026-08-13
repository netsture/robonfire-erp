@extends('layouts.app')

@section('title', 'Profit & Loss Statement')
@section('page_title', 'Profit & Loss (Income Statement)')
@section('page_subtitle', 'Net financial performance breakdown (Total Sales Revenue - Total Procurement Cost)')

@section('header_actions')
    <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary rounded-pill px-3 font-outfit fw-medium">
        <i class="bi bi-arrow-left me-1"></i> Back to Reports
    </a>
@endsection

@section('content')

<!-- Filter Bar -->
<div class="card card-custom border-0 p-3 mb-4">
    <form method="GET" action="{{ route('reports.profit-loss') }}" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <label class="form-label small mb-1 fw-semibold">Start Date</label>
            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}">
        </div>
        <div class="col-12 col-md-4">
            <label class="form-label small mb-1 fw-semibold">End Date</label>
            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}">
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end gap-2 pt-4">
            <button type="submit" class="btn btn-primary btn-sm w-100 rounded-3">Generate Statement</button>
            <a href="{{ route('reports.profit-loss') }}" class="btn btn-light btn-sm border w-100 rounded-3">Reset</a>
        </div>
    </form>
</div>

<!-- Net Profit Header Banner -->
<div class="card card-custom border-0 p-4 mb-4 {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }} text-white">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <span class="text-white-50 small fw-semibold uppercase tracking-wider">NET OPERATING PROFIT / LOSS</span>
            <h1 class="fw-bold font-outfit mt-2 mb-0">${{ number_format($netProfit, 2) }}</h1>
            <span class="small text-white-50">Net earnings after deducting cost of sales</span>
        </div>
        <div class="fs-1 text-white-50">
            <i class="bi {{ $netProfit >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }}"></i>
        </div>
    </div>
</div>

<!-- Detailed P&L Statement Table -->
<div class="card card-custom border-0 p-4">
    <h5 class="fw-bold font-outfit text-dark mb-3">Income & Expense Ledger</h5>

    <div class="table-responsive">
        <table class="table table-bordered align-middle">
            <tbody class="fs-6">
                <!-- Revenue Group -->
                <tr class="table-light fw-bold">
                    <td colspan="2"><i class="bi bi-arrow-up-right me-2 text-success"></i>Revenue & Inflows</td>
                </tr>
                <tr>
                    <td class="ps-4">Gross Sales Revenue</td>
                    <td class="text-end fw-semibold text-dark">${{ number_format($grossSales, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Less: Sales Discounts Allowed</td>
                    <td class="text-end text-danger">-${{ number_format($salesDiscounts, 2) }}</td>
                </tr>
                <tr class="fw-bold bg-light-subtle">
                    <td class="ps-4">Net Operating Sales Revenue</td>
                    <td class="text-end text-success">${{ number_format($netSales, 2) }}</td>
                </tr>

                <!-- Expenses Group -->
                <tr class="table-light fw-bold">
                    <td colspan="2"><i class="bi bi-arrow-down-left me-2 text-danger"></i>Cost of Goods & Procurement (COGS)</td>
                </tr>
                <tr>
                    <td class="ps-4">Gross Purchases Expense</td>
                    <td class="text-end text-dark">${{ number_format($grossPurchases, 2) }}</td>
                </tr>
                <tr>
                    <td class="ps-4 text-muted">Less: Purchase Discounts Received</td>
                    <td class="text-end text-success">-${{ number_format($purchaseDiscounts, 2) }}</td>
                </tr>
                <tr class="fw-bold bg-light-subtle">
                    <td class="ps-4">Net Procurement Cost</td>
                    <td class="text-end text-danger">${{ number_format($netPurchases, 2) }}</td>
                </tr>

                <!-- Final Net Profit Summary -->
                <tr class="table-dark fs-5 fw-bold">
                    <td>NET OPERATING PROFIT / (LOSS)</td>
                    <td class="text-end {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                        ${{ number_format($netProfit, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection
