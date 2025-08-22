<!doctype html>
<html class="no-js" lang="en">

    @include('partials.HomeHead')

<body>
    @include('partials.HomeNav')

    <main>
        @yield('content')
    </main>

    @include('partials.HomeFooter')

    <script src="{{ asset('resources/js/app.js') }}"></script>
</body>
</html>
