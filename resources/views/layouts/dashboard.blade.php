<!doctype html>
<html class="no-js" lang="en">

    @include('partials.DashboardHead')

<body class="g-sidenav-show  bg-gray-100">
    @include('partials.DashboardNav')

    <main>
        @yield('content')
    </main>

    @include('partials.DashboardCore')
</body>
</html>
