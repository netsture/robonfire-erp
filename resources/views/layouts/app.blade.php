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

    <!-- Flatpickr Datepicker CSS & JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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

        /* Dark Image & Icon Symbol Placeholder System */
        .dark-symbol-avatar {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            color: #f8fafc !important;
            border: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.35) !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-weight: 700 !important;
            flex-shrink: 0 !important;
            transition: all 0.25s ease !important;
        }

        .dark-symbol-avatar:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 6px 15px rgba(15, 23, 42, 0.45) !important;
        }

        /* Entity Specific Dark Symbols */
        .dark-symbol-product {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.3) !important;
        }

        .dark-symbol-category {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%) !important;
            color: #a5b4fc !important;
            border: 1px solid rgba(165, 180, 252, 0.3) !important;
        }

        .dark-symbol-brand {
            background: linear-gradient(135deg, #111827 0%, #1f2937 100%) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(251, 191, 36, 0.3) !important;
        }

        .dark-symbol-firm {
            background: linear-gradient(135deg, #0f172a 0%, #064e3b 100%) !important;
            color: #34d399 !important;
            border: 1px solid rgba(52, 211, 153, 0.3) !important;
        }

        .dark-symbol-supplier {
            background: linear-gradient(135deg, #0f172a 0%, #0369a1 100%) !important;
            color: #38bdf8 !important;
            border: 1px solid rgba(56, 189, 248, 0.3) !important;
        }

        .dark-symbol-customer {
            background: linear-gradient(135deg, #0f172a 0%, #4338ca 100%) !important;
            color: #c084fc !important;
            border: 1px solid rgba(192, 132, 252, 0.3) !important;
        }

        .dark-symbol-user {
            background: linear-gradient(135deg, #0f172a 0%, #831843 100%) !important;
            color: #f472b6 !important;
            border: 1px solid rgba(244, 114, 182, 0.3) !important;
        }

        .dark-symbol-role {
            background: linear-gradient(135deg, #0f172a 0%, #312e81 100%) !important;
            color: #818cf8 !important;
            border: 1px solid rgba(129, 140, 248, 0.3) !important;
        }

        .dark-symbol-report {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;
            color: #fbbf24 !important;
            border: 1px solid rgba(251, 191, 36, 0.3) !important;
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
            z-index: 1050;
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

        /* Mobile Backdrop Overlay */
        .sidebar-backdrop {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(2px);
            z-index: 1040;
            transition: opacity 0.3s ease;
        }

        .sidebar-backdrop.show {
            display: block;
        }

        /* Responsive Layout Adjustments */
        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.show {
                margin-left: 0 !important;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.25) !important;
            }
            #topbar, #main-content {
                margin-left: 0 !important;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>

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
            @if(Auth::user()->isSuperAdmin())
                <a href="{{ route('firms.index') }}" class="nav-link-custom {{ request()->routeIs('firms*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill text-warning"></i>
                    <span>Firms</span>
                </a>
            @endif

            <div class="sidebar-heading">Parties</div>            
            <a href="{{ route('suppliers.index') }}" class="nav-link-custom {{ request()->routeIs('suppliers*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span>Suppliers</span>
            </a>
            <a href="{{ route('customers.index') }}" class="nav-link-custom {{ request()->routeIs('customers*') ? 'active' : '' }}">
                <i class="bi bi-people-fill"></i>
                <span>Customers</span>
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
                <span>Add Products</span>
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
            <a href="{{ route('returnable.index') }}" class="nav-link-custom {{ request()->routeIs('returnable*') ? 'active' : '' }}">
                <i class="bi bi-box-arrow-in-left"></i>
                <span>Returnable Entry</span>
            </a>

            <div class="sidebar-heading">Administration</div>
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
            @php
                $headerRoleStr = strtolower(trim(Auth::user()->role ?? 'User'));
                $headerAvatarStyle = 'background-color: rgba(187, 59, 159, 0.15); color: #bb3b9f;';
                if (in_array($headerRoleStr, ['superadmin', 'super admin', 'super-admin'])) {
                    $headerAvatarStyle = 'background-color: rgba(220, 53, 69, 0.15); color: #dc3545;';
                } elseif (in_array($headerRoleStr, ['admin', 'administrator'])) {
                    $headerAvatarStyle = 'background-color: rgba(79, 70, 229, 0.15); color: #4f46e5;';
                } elseif (in_array($headerRoleStr, ['manager', 'supervisor'])) {
                    $headerAvatarStyle = 'background-color: rgba(255, 193, 7, 0.2); color: #856404;';
                } elseif (in_array($headerRoleStr, ['sales', 'staff', 'biller'])) {
                    $headerAvatarStyle = 'background-color: rgba(13, 202, 240, 0.15); color: #0dcaf0;';
                }
            @endphp

            @if(in_array($headerRoleStr, ['superadmin', 'super admin', 'super-admin']))
                <span class="badge bg-danger text-white rounded-pill px-3 py-1 fw-semibold">
                    <i class="bi bi-shield-lock-fill me-1"></i>{{ Auth::user()->role ?? 'Superadmin' }}
                </span>
            @elseif(in_array($headerRoleStr, ['admin', 'administrator']))
                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #4f46e5;">
                    <i class="bi bi-shield-check me-1"></i>{{ Auth::user()->role ?? 'Admin' }}
                </span>
            @elseif(in_array($headerRoleStr, ['manager', 'supervisor']))
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-semibold">
                    <i class="bi bi-person-badge-fill me-1"></i>{{ Auth::user()->role ?? 'Manager' }}
                </span>
            @elseif(in_array($headerRoleStr, ['sales', 'staff', 'biller']))
                <span class="badge bg-info text-white rounded-pill px-3 py-1 fw-semibold">
                    <i class="bi bi-person-workspace me-1"></i>{{ Auth::user()->role ?? 'Sales' }}
                </span>
            @elseif(in_array($headerRoleStr, ['user', 'standard user', 'customer']))
                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #bb3b9f;">
                    <i class="bi bi-person-check-fill me-1"></i>{{ Auth::user()->role ?? 'User' }}
                </span>
            @else
                <span class="badge text-white rounded-pill px-3 py-1 fw-semibold" style="background-color: #bb3b9f;">
                    <i class="bi bi-person-fill me-1"></i>{{ Auth::user()->role ?? 'User' }}
                </span>
            @endif

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-light rounded-pill border d-flex align-items-center gap-2 px-3 py-1" type="button" data-bs-toggle="dropdown">
                    <div class="rounded-circle dark-symbol-avatar dark-symbol-user" style="width: 34px; height: 34px; font-size: 0.9rem;">
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
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const topbar = document.getElementById('topbar');
            const mainContent = document.getElementById('main-content');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');

            function toggleSidebar() {
                if (window.innerWidth < 992) {
                    sidebar?.classList.toggle('show');
                    sidebarBackdrop?.classList.toggle('show');
                } else {
                    sidebar?.classList.toggle('collapsed');
                    topbar?.classList.toggle('full-width');
                    mainContent?.classList.toggle('full-width');
                }
            }

            sidebarToggle?.addEventListener('click', function (e) {
                e.stopPropagation();
                toggleSidebar();
            });

            sidebarBackdrop?.addEventListener('click', function () {
                sidebar?.classList.remove('show');
                sidebarBackdrop?.classList.remove('show');
            });

            document.querySelectorAll('#sidebar .nav-link-custom').forEach(link => {
                link.addEventListener('click', function () {
                    if (window.innerWidth < 992) {
                        sidebar?.classList.remove('show');
                        sidebarBackdrop?.classList.remove('show');
                    }
                });
            });

            if (typeof flatpickr !== 'undefined') {
                flatpickr('.datepicker-ddmmyyyy', {
                    dateFormat: 'd-m-Y',
                    allowInput: true
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
