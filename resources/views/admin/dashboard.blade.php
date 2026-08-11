@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="crm-console">
    <!-- Header -->
    <div class="crm-console-header">
        <div>
            <h1 class="crm-console-title">Super Admin Control Center</h1>
            <p class="crm-console-subtitle">Consolidated status monitoring of multi-tenant instances and databases.</p>
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="crm-stats-grid crm-stats-grid-3">
        <!-- Total Tenants -->
        <div class="crm-stat-card crm-stat-blue">
            <div class="crm-stat-card-top">
                <p>TOTAL TENANTS</p>
                <span class="crm-stat-icon"><i class="bi bi-building"></i></span>
            </div>
            <h3>{{ number_format($totalTenants) }}</h3>
        </div>

        <!-- Active Tenants -->
        <div class="crm-stat-card crm-stat-green">
            <div class="crm-stat-card-top">
                <p>ACTIVE TENANTS</p>
                <span class="crm-stat-icon"><i class="bi bi-check-circle"></i></span>
            </div>
            <h3>{{ number_format($activeTenants) }}</h3>
        </div>

        <!-- Inactive Tenants -->
        <div class="crm-stat-card crm-stat-orange">
            <div class="crm-stat-card-top">
                <p>INACTIVE TENANTS</p>
                <span class="crm-stat-icon"><i class="bi bi-x-circle"></i></span>
            </div>
            <h3>{{ number_format($inactiveTenants) }}</h3>
        </div>
    </div>

    <!-- System Console Overview -->
    <div class="crm-chart-row mt-4">
        <div class="crm-chart-card crm-chart-wide">
            <div class="crm-chart-card-head border-b border-slate-100 pb-4 mb-4">
                <div>
                    <h3>System Workspace Overview</h3>
                    <p>Administrative actions and Multi-Tenant DB configuration helper.</p>
                </div>
            </div>
            <div class="space-y-4">
                <h5 class="text-base font-semibold text-slate-800">Welcome, Super Administrator</h5>
                <p class="text-sm text-slate-500 leading-relaxed max-w-3xl">
                    From this control panel, you can manage the tenant companies in the ERP system. You can activate, deactivate, or create new tenants. Creating a tenant automatically provisions their Database tenancy workspace and seeds an initial Administrator login for them.
                </p>
                <div class="pt-3">
                    <a href="{{ route('admin.tenants.index') }}" class="crm-btn inline-flex items-center gap-2 text-decoration-none">
                        <i class="bi bi-gear-fill"></i>
                        <span>Manage Tenants</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
