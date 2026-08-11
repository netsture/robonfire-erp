@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="crm-console">
    <!-- Header -->
    <div class="crm-console-header">
        <div>
            <h1 class="crm-console-title">Business Dashboard Overview</h1>
            <p class="crm-console-subtitle">Real-time monitoring of operations, transactions, and financial flows.</p>
        </div>
        <div class="crm-console-actions">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200/50 w-fit">
                <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                Tenant Portal Active
            </span>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="crm-stats-grid crm-stats-grid-4">
        <!-- Inward Value -->
        <div class="crm-stat-card crm-stat-blue">
            <div class="crm-stat-card-top">
                <p>TOTAL PURCHASES (INWARD)</p>
                <span class="crm-stat-icon"><i class="bi bi-currency-rupee"></i></span>
            </div>
            <h3>₹{{ number_format($totalInwardsVal, 2) }}</h3>
        </div>

        <!-- Inward Count -->
        <div class="crm-stat-card crm-stat-green">
            <div class="crm-stat-card-top">
                <p>INWARD LOGS</p>
                <span class="crm-stat-icon"><i class="bi bi-box-arrow-in-down"></i></span>
            </div>
            <h3>{{ number_format($inwardCount) }}</h3>
        </div>

        <!-- Outward Count -->
        <div class="crm-stat-card crm-stat-orange">
            <div class="crm-stat-card-top">
                <p>OUTWARD LOGS</p>
                <span class="crm-stat-icon"><i class="bi bi-box-arrow-up"></i></span>
            </div>
            <h3>{{ number_format($outwardCount) }}</h3>
        </div>

        <!-- Active Parties -->
        <div class="crm-stat-card crm-stat-teal">
            <div class="crm-stat-card-top">
                <p>ACTIVE PARTIES</p>
                <span class="crm-stat-icon"><i class="bi bi-people"></i></span>
            </div>
            <h3>{{ number_format($activeParties) }}</h3>
        </div>
    </div>

    <!-- Chart Panel -->
    <div class="crm-chart-row">
        <div class="crm-chart-card crm-chart-wide">
            <div class="crm-chart-card-head">
                <div>
                    <h3>Monthly Purchases Trends</h3>
                    <p>Inward Total Value with Tax - {{ date('Y') }}</p>
                </div>
            </div>
            <div class="crm-chart-area" style="position: relative; height: 320px; width: 100%;">
                <canvas id="purchaseChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Render Chart.js
    var ctx = document.getElementById('purchaseChart').getContext('2d');
    var chartData = @json($chartData);
    
    // Create subtle gradient for fills
    var gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Inward Purchases (₹)',
                data: chartData,
                borderColor: '#2563eb',
                borderWidth: 3,
                backgroundColor: gradient,
                fill: true,
                tension: 0.3,
                pointBackgroundColor: '#2563eb',
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(226, 232, 240, 0.6)'
                    },
                    ticks: {
                        callback: function(value) {
                            return '₹' + value.toLocaleString('en-IN');
                        },
                        font: {
                            family: 'Outfit'
                        }
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            family: 'Outfit'
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
