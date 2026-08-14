<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - ERP SYSTEM</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --header-height: 70px;
            --primary-accent: #6366f1;
            --primary-hover: #4f46e5;
            --body-bg: #f8fafc;
            --card-border-radius: 1rem;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--body-bg);
            color: #334155;
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--sidebar-bg);
            color: #94a3b8;
            z-index: 1040;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.05);
        }

        #sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(15, 23, 42, 0.95);
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
        }

        .sidebar-menu {
            padding: 1.25rem 0.85rem;
            flex: 1;
            overflow-y: auto;
        }

        .sidebar-heading {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
            font-weight: 700;
            padding: 1rem 0.75rem 0.4rem 0.75rem;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            padding: 0.65rem 0.85rem;
            color: #94a3b8;
            border-radius: 0.65rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            margin-bottom: 0.2rem;
        }

        .nav-link-custom i {
            font-size: 1.15rem;
            margin-right: 0.85rem;
            transition: transform 0.2s ease;
        }

        .nav-link-custom:hover {
            color: #f8fafc;
            background: rgba(255, 255, 255, 0.06);
            transform: translateX(3px);
        }

        .nav-link-custom.active {
            color: #fff;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
        }

        .nav-link-custom.active i {
            color: #fff;
        }

        /* Top Navbar */
        #topbar {
            height: var(--header-height);
            margin-left: var(--sidebar-width);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            transition: all 0.3s ease;
        }

        #topbar.full-width {
            margin-left: 0;
        }

        /* Main Content Container */
        #main-content {
            margin-left: var(--sidebar-width);
            min-height: calc(100vh - var(--header-height));
            padding: 2rem 1.75rem;
            transition: all 0.3s ease;
        }

        #main-content.full-width {
            margin-left: 0;
        }

        /* Card Styling */
        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: var(--card-border-radius);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-custom:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.06);
        }

        .metric-card {
            border-radius: var(--card-border-radius);
            border: 1px solid rgba(226, 232, 240, 0.8);
            padding: 1.5rem;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }

        .metric-icon {
            width: 52px;
            height: 52px;
            border-radius: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .badge-role {
            font-size: 0.725rem;
            font-weight: 600;
            padding: 0.35em 0.75em;
            border-radius: 2rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* Responsive Layout Adjustments */
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.show {
                margin-left: 0;
            }
            #topbar, #main-content {
                margin-left: 0 !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <aside id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-icon me-2">
                <i class="bi bi-box-seam-fill"></i>
            </div>
            <div>
                <h5 class="mb-0 text-white font-outfit fw-bold">ERP SYSTEM</h5>
                <!--<span class="text-muted small" style="font-size: 0.7rem;">v1.0 Enterprise</span>-->
            </div>
        </div>

        <div class="sidebar-menu">
            <div class="sidebar-heading">Core</div>
            <a href="{{ route('dashboard') }}" class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>

            <div class="sidebar-heading">Parties</div>
            <a href="{{ route('customers.index') }}" class="nav-link-custom {{ request()->routeIs('customers*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Customers</span>
            </a>
            <a href="{{ route('suppliers.index') }}" class="nav-link-custom {{ request()->routeIs('suppliers*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </a>

            <div class="sidebar-heading">Inventory & Catalog</div>
            <a href="{{ route('categories.index') }}" class="nav-link-custom {{ request()->routeIs('categories*') ? 'active' : '' }}">
                <i class="bi bi-tags-fill"></i>
                <span>Categories</span>
            </a>
            <a href="{{ route('brands.index') }}" class="nav-link-custom {{ request()->routeIs('brands*') ? 'active' : '' }}">
                <i class="bi bi-award-fill"></i>
                <span>Brands</span>
            </a>
            <a href="{{ route('products.index') }}" class="nav-link-custom {{ request()->routeIs('products*') ? 'active' : '' }}">
                <i class="bi bi-box-seam"></i>
                <span>Products</span>
            </a>
            <!--<a href="{{ route('inventory.index') }}" class="nav-link-custom {{ request()->routeIs('inventory*') ? 'active' : '' }}">
                <i class="bi bi-arrow-repeat"></i>
                <span>Stock Adjustments</span>
            </a>-->

            <div class="sidebar-heading">Transactions</div>
            <a href="{{ route('purchases.index') }}" class="nav-link-custom {{ request()->routeIs('purchases*') ? 'active' : '' }}">
                <i class="bi bi-cart-plus-fill"></i>
                <span>Purchases</span>
            </a>
            <a href="{{ route('sales.index') }}" class="nav-link-custom {{ request()->routeIs('sales*') ? 'active' : '' }}">
                <i class="bi bi-bag-check-fill"></i>
                <span>Sales Orders</span>
            </a>

            <div class="sidebar-heading">Administration</div>
            @if(Auth::user()->isSuperAdmin())
                <a href="{{ route('firms.index') }}" class="nav-link-custom {{ request()->routeIs('firms*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill text-warning"></i>
                    <span>Firms</span>
                </a>
            @endif
            <a href="{{ route('users.index') }}" class="nav-link-custom {{ request()->routeIs('users*') ? 'active' : '' }}">
                <i class="bi bi-person-gear"></i>
                <span>User Accounts</span>
            </a>
            <a href="{{ route('roles.index') }}" class="nav-link-custom {{ request()->routeIs('roles*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock-fill"></i>
                <span>Roles & Permissions</span>
            </a>
            <a href="{{ route('reports.index') }}" class="nav-link-custom {{ request()->routeIs('reports*') ? 'active' : '' }}">
                <i class="bi bi-bar-chart-line-fill"></i>
                <span>Analytics & Reports</span>
            </a>
        </div>

        <div class="p-3 border-top border-secondary border-opacity-20 text-center">
            <span class="small text-muted"><i class="bi bi-clock me-1"></i> {{ date('M d, Y') }}</span>
        </div>
    </aside>

    <!-- Topbar -->
    <header id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button id="sidebarToggle" class="btn btn-light btn-sm rounded-circle p-2 border">
                <i class="bi bi-list fs-5"></i>
            </button>
            @if(Auth::user()->firm)
                <div class="d-none d-md-flex align-items-center bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 border border-primary border-opacity-20 small font-outfit fw-semibold">
                    <i class="bi bi-building me-2"></i>{{ Auth::user()->firm->name }}
                </div>
            @elseif(Auth::user()->isSuperAdmin())
                <div class="d-none d-md-flex align-items-center bg-dark text-white rounded-pill px-3 py-1 small font-outfit fw-semibold">
                    <i class="bi bi-globe me-2 text-warning"></i>Global Superadmin Context
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-indigo-subtle text-primary border border-primary-subtle badge-role">
                <i class="bi bi-shield-check me-1"></i>{{ Auth::user()->role ?? 'Admin' }}
            </span>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light rounded-pill border d-flex align-items-center gap-2 px-3 py-1" type="button" data-bs-toggle="dropdown">
                    <div class="rounded-circle bg-indigo text-primary fw-bold d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #e0e7ff;">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="fw-semibold text-dark small d-none d-sm-inline">{{ Auth::user()->name ?? 'User' }}</span>
                    <i class="bi bi-chevron-down small text-muted"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2 rounded-3">
                    <li class="px-3 py-2 border-bottom">
                        <div class="fw-semibold text-dark">{{ Auth::user()->name }}</div>
                        <div class="text-muted small">{{ Auth::user()->email }}</div>
                    </li>
                    <li>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2 py-2">
                                <i class="bi bi-box-arrow-right"></i> Sign Out
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Main Content Wrapper -->
    <main id="main-content">
        <!-- Page Header & Action Bar -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h3 class="fw-bold mb-1 text-dark font-outfit">@yield('page_title', 'Dashboard')</h3>
                <p class="text-muted small mb-0">@yield('page_subtitle', 'System Overview & Financial Operations')</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                @yield('header_actions')
            </div>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-octagon-fill fs-5 me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                    <div>{{ session('warning') }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('topbar').classList.toggle('full-width');
            document.getElementById('main-content').classList.toggle('full-width');
        });
    </script>
    @stack('scripts')
</body>
</html>
