<!doctype html>
<html class="no-js" lang="en">

    @include('partials.dashboard-head')

<body class="g-sidenav-show  bg-gray-100">
    @include('partials.dashboard-nav')

    <!-- Flash Messages -->
    @if(session('success'))
    <div data-flash="success" style="display: none;">{{ session('success') }}</div>
    @endif

    @if(session('error'))
    <div data-flash="error" style="display: none;">{{ session('error') }}</div>
    @endif

    @if(session('warning'))
    <div data-flash="warning" style="display: none;">{{ session('warning') }}</div>
    @endif

    @if(session('info'))
    <div data-flash="info" style="display: none;">{{ session('info') }}</div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer>

  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>

          

    </footer>
      <!-- Github buttons -->
        <script async defer src="https://buttons.github.io/buttons.js"></script>
        <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
        <script src="{{ asset('assets/core/soft-ui-dashboard.js') }}"></script>
        <script src="{{ asset('assets/core/soft-ui-dashboard.map.js') }}"></script>
        <script src="{{ asset('assets/core/soft-ui-dashboard.min.js') }}"></script>
        <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
        <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
        <script src="{{ asset('assets/js/plugins/chartjs.min.js') }}"></script>

</body>
</html>
