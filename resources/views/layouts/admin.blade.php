<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sidebar-collapsed { width: 70px !important; }
        .sidebar-collapsed .nav-link { text-align: center; padding: 0.75rem 0; }
        .sidebar-collapsed .sidebar-title { display: none; }
        .sidebar-toggle { cursor: pointer; }
        .active-link { background: #495057; }
    </style>
</head>
<body>
    <div class="d-flex">
        {{-- Sidebar --}}
        <div id="sidebar" class="bg-dark text-white p-3 vh-100" style="width:250px; transition:width 0.2s;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold sidebar-title">Admin Panel</h4>
                <span class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">&#9776;</span>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active-link' : '' }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.users.index') }}" class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active-link' : '' }}">Users</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.bonus_rules.index') }}" class="nav-link text-white {{ request()->routeIs('admin.bonus_rules.*') ? 'active-link' : '' }}">Bonus Rules</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.earnings.index') }}" class="nav-link text-white {{ request()->routeIs('admin.earnings.*') ? 'active-link' : '' }}">Earnings</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.withdrawals.index') }}" class="nav-link text-white {{ request()->routeIs('admin.withdrawals.*') ? 'active-link' : '' }}">Withdrawals</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.referral_codes.index') }}" class="nav-link text-white {{ request()->routeIs('admin.referral_codes.*') ? 'active-link' : '' }}">Referral Codes</a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.genealogy.index') }}" class="nav-link text-white {{ request()->routeIs('admin.genealogy.*') ? 'active-link' : '' }}">Genealogy</a>
                </li>
            </ul>
        </div>

        {{-- Main Content --}}
        <div class="flex-grow-1">
            <nav class="navbar navbar-light bg-light shadow-sm px-4 d-flex justify-content-between">
                <span class="fw-bold">Admin Dashboard</span>
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        {{ Auth::user()->name ?? 'Admin' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger" style="border: none; background: none; width: 100%; text-align: left;">Logout</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </nav>
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
        }
    </script>
</body>
</html>