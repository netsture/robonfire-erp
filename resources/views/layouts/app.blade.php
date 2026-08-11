@php
    $user = auth()->user();
    $initials = collect(explode(' ', $user->name))->map(fn ($w) => strtoupper(substr($w, 0, 1)))->take(2)->join('');
    $companyName = $user->tenant_id ? $user->tenant->name : 'Super Admin Workspace';
    $branchName = $user->tenant_id ? 'Ahmedabad' : 'System Control';
    $fyStart = now()->month >= 4 ? now()->year : now()->year - 1;
    $fyEnd = substr((string) ($fyStart + 1), -2);
    $roleName = $user->hasRole('Super Admin') ? 'Super Admin' : 'Tenant Admin';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — RobonfireERP</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS (kept for compatibility with inner bootstrap elements/datatables/modals) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <!-- SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS & Theme v4 -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom CSS (specific page elements only) -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    @yield('styles')
</head>
<body class="crm-app-body">
    <script>
        (function () {
            try {
                if (localStorage.getItem('crm-sidebar-collapsed') === '1') {
                    document.body.classList.add('sidebar-collapsed');
                }
            } catch (e) {}
        })();
    </script>

    <!-- Header -->
    <header class="crm-header">
        <div class="crm-header-brand-zone">
            <button type="button"
                    class="crm-sidebar-toggle"
                    id="crm-sidebar-toggle"
                    title="Toggle menu"
                    aria-label="Toggle sidebar"
                    aria-controls="crm-sidebar"
                    aria-expanded="false">
                <i class="bi bi-list"></i>
            </button>

            <a href="{{ route('dashboard') }}" class="crm-brand">
                <div class="crm-brand-icon">
                    <i class="bi bi-shield-fill-check text-red-500 text-xl leading-none"></i>
                </div>
                <span class="crm-brand-text">Robonfire<span class="text-red-500">ERP</span></span>
            </a>
        </div>

        <div class="crm-header-body">
            <div class="crm-header-meta">
                <div class="crm-meta-pill" title="Company">
                    <span class="crm-meta-pill-label"><i class="bi bi-buildings"></i> Company</span>
                    <span class="crm-meta-pill-value">{{ $companyName }}</span>
                </div>
                <div class="crm-meta-pill" title="Branch">
                    <span class="crm-meta-pill-label"><i class="bi bi-geo-alt"></i> Branch</span>
                    <span class="crm-meta-pill-value crm-meta-pill-value--muted">{{ $branchName }}</span>
                </div>
                <div class="crm-meta-pill" title="Period">
                    <span class="crm-meta-pill-label"><i class="bi bi-calendar3"></i> Period</span>
                    <span class="crm-meta-pill-value crm-meta-pill-value--period">
                        FY <span>{{ $fyStart }}-{{ $fyEnd }}</span>
                    </span>
                </div>
            </div>

            <div class="crm-header-right">
                <div class="crm-header-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Quick Find..." id="crm-quick-find" autocomplete="off">
                    <kbd class="crm-search-kbd">Ctrl+K</kbd>
                </div>

                <div class="crm-header-actions">
                    <a href="#" class="crm-icon-btn" title="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="crm-badge-dot">3</span>
                    </a>
                    <a href="#" class="crm-icon-btn" title="Messages">
                        <i class="bi bi-envelope"></i>
                        <span class="crm-badge-dot">2</span>
                    </a>
                    <button type="button" class="crm-icon-btn" title="Full Screen" onclick="toggleFullscreen()">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>

                    <div class="crm-user-menu" x-data="{ open: false }" @click.outside="open = false">
                        <button type="button" class="crm-user-trigger" @click="open = !open" title="{{ $user->name }}">
                            <span class="crm-user-avatar">{{ $initials }}</span>
                            <span class="crm-user-info">
                                <span class="crm-user-name">{{ $user->name }}</span>
                                <span class="crm-user-role">{{ $roleName }}</span>
                            </span>
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="crm-user-dropdown" x-show="open" x-cloak x-transition>
                            <div class="crm-user-dropdown-head">
                                <strong>{{ $user->name }}</strong>
                                <span>{{ $user->email }}</span>
                            </div>
                            <a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"><i class="bi bi-box-arrow-right"></i> Sign Out</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div class="crm-app-shell">
        <!-- Sidebar -->
        <aside class="crm-sidebar" id="crm-sidebar">
            <nav>
                @if(auth()->user()->hasRole('Super Admin'))
                    <div class="crm-nav-section">
                        <div class="crm-nav-section-title">Administration</div>
                        <a href="{{ route('admin.tenants.index') }}" class="crm-nav-link {{ request()->routeIs('admin.tenants.*') ? 'active' : '' }}" title="Tenants">
                            <i class="bi bi-building"></i><span class="crm-nav-label">Tenants</span>
                        </a>
                    </div>
                @else
                    <!-- Tenant Admin Menu -->
                    <div class="crm-nav-section">
                        <div class="crm-nav-section-title">Operations</div>
                        
                        <a href="{{ route('dashboard') }}" class="crm-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" title="Dashboard">
                            <i class="bi bi-speedometer2"></i><span class="crm-nav-label">Dashboard</span>
                        </a>
                        
                        <a href="{{ route('parties.index') }}" class="crm-nav-link {{ request()->routeIs('parties.index') ? 'active' : '' }}" title="Parties">
                            <i class="bi bi-people"></i><span class="crm-nav-label">Parties</span>
                        </a>
                        
                        <a href="{{ route('purchases.index', ['type' => 'inward']) }}" class="crm-nav-link {{ request()->is('purchases/inward*') ? 'active' : '' }}" title="Inward - Purchase">
                            <i class="bi bi-box-arrow-in-down"></i><span class="crm-nav-label">Inward - Purchase</span>
                        </a>
                        
                        <a href="{{ route('purchases.index', ['type' => 'outward']) }}" class="crm-nav-link {{ request()->is('purchases/outward*') ? 'active' : '' }}" title="Outward">
                            <i class="bi bi-box-arrow-up"></i><span class="crm-nav-label">Outward</span>
                        </a>
                        
                        <a href="{{ route('purchases.index', ['type' => 'returnable_material']) }}" class="crm-nav-link {{ request()->is('purchases/returnable_material*') ? 'active' : '' }}" title="Returnable Material">
                            <i class="bi bi-arrow-left-right"></i><span class="crm-nav-label">Returnable Material</span>
                        </a>
                        
                        <a href="{{ route('purchases.index', ['type' => 'returnable_chalan']) }}" class="crm-nav-link {{ request()->is('purchases/returnable_chalan*') ? 'active' : '' }}" title="Returnable Chalan">
                            <i class="bi bi-file-earmark-text"></i><span class="crm-nav-label">Returnable Chalan</span>
                        </a>
                    </div>
                @endif
            </nav>
        </aside>

        <!-- Sidebar Mobile Backdrop -->
        <button type="button"
                class="crm-sidebar-backdrop"
                id="crm-sidebar-backdrop"
                aria-label="Close navigation menu"></button>

        <!-- Main Content -->
        <main class="crm-main">
            @hasSection('breadcrumb')
                <nav class="crm-breadcrumb">@yield('breadcrumb')</nav>
            @endif

            @hasSection('toolbar')
                <div class="crm-page-toolbar">@yield('toolbar')</div>
            @endif

            @yield('content')
        </main>
    </div>

    <!-- Standard Scripts -->
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @stack('scripts')
    
    <script>
    function toggleFullscreen() {
        if (!document.fullscreenElement) document.documentElement.requestFullscreen();
        else document.exitFullscreen();
    }

    $(document).ready(function() {
        // Setup Ajax header CSRF token
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const toggle = document.getElementById('crm-sidebar-toggle');
        const sidebar = document.getElementById('crm-sidebar');
        const backdrop = document.getElementById('crm-sidebar-backdrop');
        const mobileSidebar = window.matchMedia('(max-width: 768px)');

        const closeMobileSidebar = () => {
            document.body.classList.remove('sidebar-mobile-open');
            toggle?.setAttribute('aria-expanded', 'false');
        };

        toggle?.addEventListener('click', () => {
            if (mobileSidebar.matches) {
                const isOpen = document.body.classList.toggle('sidebar-mobile-open');
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
                return;
            }

            document.body.classList.toggle('sidebar-collapsed');
            toggle.setAttribute(
                'aria-expanded',
                document.body.classList.contains('sidebar-collapsed') ? 'false' : 'true'
            );
            try {
                localStorage.setItem(
                    'crm-sidebar-collapsed',
                    document.body.classList.contains('sidebar-collapsed') ? '1' : '0'
                );
            } catch (e) {}
        });

        if (!mobileSidebar.matches) {
            toggle?.setAttribute(
                'aria-expanded',
                document.body.classList.contains('sidebar-collapsed') ? 'false' : 'true'
            );
        }

        backdrop?.addEventListener('click', closeMobileSidebar);

        sidebar?.addEventListener('click', (event) => {
            if (mobileSidebar.matches && event.target.closest('a')) {
                closeMobileSidebar();
            }
        });

        mobileSidebar.addEventListener('change', closeMobileSidebar);

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && document.body.classList.contains('sidebar-mobile-open')) {
                closeMobileSidebar();
                toggle?.focus();
            }
        });

        // Quick Search shortcut autofocus
        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                document.getElementById('crm-quick-find')?.focus();
            }
        });
    });
    </script>
    
    @yield('scripts')
</body>
</html>
