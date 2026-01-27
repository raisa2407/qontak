<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Qontak Omnichannel')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4f46e5;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #06b6d4;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f8fafc;
        }
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
            overflow-y: auto;
        }
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            font-size: 1.25rem;
            font-weight: 700;
        }
        .sidebar-menu {
            padding: 1rem 0;
        }
        .sidebar-item {
            padding: 0.75rem 1.5rem;
            color: #cbd5e1;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s;
            border-left: 3px solid transparent;
        }
        .sidebar-item:hover {
            background: rgba(255,255,255,0.05);
            color: white;
        }
        .sidebar-item.active {
            background: rgba(79, 70, 229, 0.2);
            color: white;
            border-left-color: var(--primary);
        }
        .sidebar-section {
            padding: 1rem 1.5rem 0.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94a3b8;
        }
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            min-height: 100vh;
        }
        .navbar-top {
            background: white;
            padding: 1rem 2rem;
            margin: -2rem -2rem 2rem -2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-header {
            margin-bottom: 2rem;
        }
        .page-title {
            font-size: 1.875rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .page-subtitle {
            color: #64748b;
            margin-top: 0.25rem;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            font-weight: 600;
            border-radius: 12px 12px 0 0 !important;
        }
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            font-weight: 500;
            transition: all 0.2s;
        }
        .btn-primary {
            background: var(--primary);
            border: none;
        }
        .btn-primary:hover {
            background: #4338ca;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.3);
        }
        .badge {
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            font-weight: 500;
            font-size: 0.75rem;
        }
        .table {
            margin: 0;
        }
        .table thead th {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            color: #64748b;
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem;
        }
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .table tbody tr:hover {
            background: #f8fafc;
        }
        .alert {
            border: none;
            border-radius: 8px;
            padding: 1rem 1.25rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            padding: 0.625rem 0.875rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        .form-label {
            font-weight: 500;
            color: #475569;
            margin-bottom: 0.5rem;
        }
        .stat-card {
            background: linear-gradient(135deg, var(--primary) 0%, #6366f1 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        .stat-label {
            opacity: 0.9;
            font-size: 0.875rem;
        }
        .nav-tabs {
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 0;
        }
        .nav-tabs .nav-link {
            border: none;
            color: #64748b;
            padding: 1rem 1.5rem;
            font-weight: 500;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .nav-tabs .nav-link:hover {
            border-color: transparent;
            color: var(--primary);
        }
        .nav-tabs .nav-link.active {
            color: var(--primary);
            background: transparent;
            border-bottom-color: var(--primary);
        }
        .tab-content {
            padding: 0;
        }
        .tab-content > .tab-pane {
            padding-top: 1.5rem;
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="bi bi-chat-dots-fill"></i> Qontak Hub
        </div>
        <div class="sidebar-menu">
            <a href="{{ route('dashboard.index') }}" class="sidebar-item {{ request()->routeIs('dashboard.index') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('rooms.index') }}" class="sidebar-item {{ request()->routeIs('rooms.*') && !request()->routeIs('rooms.expired') ? 'active' : '' }}">
                <i class="bi bi-inbox"></i>
                <span>Inbox</span>
            </a>
            
            <div class="sidebar-section">Channels</div>
            <a href="{{ route('integrations.index') }}" class="sidebar-item {{ request()->routeIs('integrations.index') && !request()->has('target_channel') ? 'active' : '' }}">
                <i class="bi bi-grid"></i>
                <span>All Channels</span>
            </a>
            <a href="{{ route('integrations.index', ['target_channel' => 'wa']) }}" class="sidebar-item {{ request()->get('target_channel') == 'wa' ? 'active' : '' }}">
                <i class="bi bi-whatsapp"></i>
                <span>WhatsApp</span>
            </a>
            <a href="{{ route('integrations.index', ['target_channel' => 'fb']) }}" class="sidebar-item {{ request()->get('target_channel') == 'fb' ? 'active' : '' }}">
                <i class="bi bi-facebook"></i>
                <span>Facebook</span>
            </a>
            <a href="{{ route('integrations.index', ['target_channel' => 'ig']) }}" class="sidebar-item {{ request()->get('target_channel') == 'ig' ? 'active' : '' }}">
                <i class="bi bi-instagram"></i>
                <span>Instagram</span>
            </a>
            <a href="{{ route('integrations.index', ['target_channel' => 'email']) }}" class="sidebar-item {{ request()->get('target_channel') == 'email' ? 'active' : '' }}">
                <i class="bi bi-envelope"></i>
                <span>Email</span>
            </a>
            
            <div class="sidebar-section">Interactions</div>
            <a href="{{ route('interactions.message.index') }}" class="sidebar-item {{ request()->routeIs('interactions.message.*') ? 'active' : '' }}">
                <i class="bi bi-chat-dots"></i>
                <span>Message Interactions</span>
            </a>
            <a href="{{ route('interactions.room.index') }}" class="sidebar-item {{ request()->routeIs('interactions.room.*') ? 'active' : '' }}">
                <i class="bi bi-door-open"></i>
                <span>Room Interactions</span>
            </a>
            
            <div class="sidebar-section">Management</div>
            <a href="{{ route('users.index') }}" class="sidebar-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Users</span>
            </a>
            <a href="{{ route('rooms.expired') }}" class="sidebar-item {{ request()->routeIs('rooms.expired') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i>
                <span>Expired Rooms</span>
            </a>
        </div>
    </div>

    <div class="main-content">
        <div class="navbar-top">
            <div>
                <h1 class="page-title">@yield('title', 'Dashboard')</h1>
                @yield('subtitle')
            </div>
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-bell"></i>
                </button>
                <button class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-gear"></i>
                </button>
                <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="bi bi-check-circle me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-circle me-2"></i>
                <strong>Whoops!</strong> There were some problems with your input.
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>