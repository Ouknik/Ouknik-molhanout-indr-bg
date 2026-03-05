<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | {{ config('app.name', 'Molhanout') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: {{ \App\Models\AppSetting::getValue('primary_color', '#2E7D32') }};
            --secondary: {{ \App\Models\AppSetting::getValue('secondary_color', '#FF8F00') }};
            --accent: {{ \App\Models\AppSetting::getValue('accent_color', '#1565C0') }};
            --font-family: '{{ \App\Models\AppSetting::getValue('font_family', 'Cairo') }}', sans-serif;
        }

        * { font-family: var(--font-family); }

        body { background-color: #f4f6f9; }

        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary), #1B5E20);
            color: #fff;
            position: fixed;
            top: 0;
            {{ app()->getLocale() === 'ar' ? 'right: 0;' : 'left: 0;' }}
            z-index: 1000;
            transition: all 0.3s;
        }

        .sidebar .brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar .brand h4 { margin: 0; font-weight: 700; }

        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 10px;
            transition: all 0.2s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        .sidebar .nav-link i {
            width: 24px;
            {{ app()->getLocale() === 'ar' ? 'margin-left: 10px;' : 'margin-right: 10px;' }}
        }

        .main-content {
            {{ app()->getLocale() === 'ar' ? 'margin-right: 260px;' : 'margin-left: 260px;' }}
            padding: 20px;
        }

        .top-bar {
            background: #fff;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-card {
            background: #fff;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s;
        }

        .stat-card:hover { transform: translateY(-2px); }

        .stat-card .icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .stat-card .value {
            font-size: 1.8rem;
            font-weight: 700;
        }

        .btn-primary { background-color: var(--primary); border-color: var(--primary); }
        .btn-primary:hover { background-color: color-mix(in srgb, var(--primary), black 15%); }
        .btn-secondary { background-color: var(--secondary); border-color: var(--secondary); }

        .badge-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .table th { font-weight: 600; white-space: nowrap; }

        @media (max-width: 768px) {
            .sidebar { width: 100%; position: relative; min-height: auto; }
            .main-content { margin-left: 0; margin-right: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
    {{-- Sidebar --}}
    <aside class="sidebar">
        <div class="brand">
            <h4><i class="fas fa-store"></i> Molhanout</h4>
            <small>Admin Panel</small>
        </div>
        <nav class="mt-3">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> {{ __('Dashboard') }}
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fas fa-users"></i> {{ __('Users') }}
            </a>
            <a href="{{ route('admin.products.index') }}" class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                <i class="fas fa-box-open"></i> {{ __('Products') }}
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="fas fa-tags"></i> {{ __('Categories') }}
            </a>
            <a href="{{ route('admin.orders.index') }}" class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="fas fa-shopping-cart"></i> {{ __('Orders') }}
            </a>
            <a href="{{ route('admin.disputes.index') }}" class="nav-link {{ request()->routeIs('admin.disputes.*') ? 'active' : '' }}">
                <i class="fas fa-exclamation-triangle"></i> {{ __('Disputes') }}
            </a>
            <a href="{{ route('admin.settings.index') }}" class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}">
                <i class="fas fa-cog"></i> {{ __('Settings') }}
            </a>

            <hr class="border-light my-3 opacity-25">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent" style="cursor:pointer;">
                    <i class="fas fa-sign-out-alt"></i> {{ __('Logout') }}
                </button>
            </form>
        </nav>
    </aside>

    {{-- Main Content --}}
    <div class="main-content">
        <div class="top-bar">
            <h5 class="mb-0">@yield('page-title', 'Dashboard')</h5>
            <div class="d-flex align-items-center gap-3">
                {{-- Language Switcher --}}
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-globe"></i> {{ strtoupper(app()->getLocale()) }}
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?locale=fr">Français</a></li>
                        <li><a class="dropdown-item" href="?locale=ar">العربية</a></li>
                        <li><a class="dropdown-item" href="?locale=en">English</a></li>
                    </ul>
                </div>
                <span class="text-muted">{{ auth()->user()->name ?? 'Admin' }}</span>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
