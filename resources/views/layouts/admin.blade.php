<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="d-flex">
        {{-- Sidebar --}}
        <div class="bg-dark text-white p-3 vh-100" style="width:250px;">
            <h4 class="fw-bold mb-4">Admin Panel</h4>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="{{ route('admin.dashboard') }}" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item"><a href="{{ route('admin.users.index') }}" class="nav-link text-white">Users</a></li>
                <li class="nav-item"><a href="{{ route('admin.packages.index') }}" class="nav-link text-white">Packages</a></li>
                <li class="nav-item"><a href="{{ route('admin.bonus_rules.index') }}" class="nav-link text-white">Bonus Rules</a></li>
                <li class="nav-item"><a href="{{ route('admin.earnings.index') }}" class="nav-link text-white">Earnings</a></li>
                <li class="nav-item"><a href="{{ route('admin.withdrawals.index') }}" class="nav-link text-white">Withdrawals</a></li>
                <li class="nav-item"><a href="{{ route('admin.genealogy.index') }}" class="nav-link text-white">Genealogy</a></li>
            </ul>
        </div>

        {{-- Main Content --}}
        <div class="flex-grow-1">
            <nav class="navbar navbar-light bg-light shadow-sm px-4">
                <span class="fw-bold">Admin Dashboard</span>
                <div>
                    <a href="{{ route('logout') }}" class="btn btn-sm btn-outline-danger">Logout</a>
                </div>
            </nav>
            <main class="p-4">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
