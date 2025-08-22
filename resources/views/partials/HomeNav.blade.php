<nav class="navbar navbar-expand-lg shadow-sm" style="background-color:#0A192F;">
  <div class="container-fluid">
    
    <!-- Brand / Logo -->
    <img src="{{ asset('assets/banner.jpg') }}" 
         alt="Logo" 
         style="height:40px; border-radius:50%; box-shadow:0 0 8px rgba(255,193,7,0.5);">

    <a class="navbar-brand fw-bold text-uppercase text-light ms-2" href="#">
      <i class="bi bi-diagram-3 text-warning"></i> Aken
    </a>

    <!-- Toggler -->
    <button class="navbar-toggler text-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link active text-warning fw-semibold" aria-current="page" href="#">
            <i class="bi bi-house-door"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-light" href="#"><i class="bi bi-people"></i> Members</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-light" href="#"><i class="bi bi-currency-exchange"></i> Earnings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-light" href="#"><i class="bi bi-diagram-2"></i> Network</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle text-light" href="#" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-grid"></i> More
          </a>
          <ul class="dropdown-menu" style="background-color:#112240;">
            <li><a class="dropdown-item text-light" href="#">About Us</a></li>
            <li><a class="dropdown-item text-light" href="#">Contact</a></li>
            <li><hr class="dropdown-divider bg-warning"></li>
            <li><a class="dropdown-item text-light" href="#">Support</a></li>
          </ul>
        </li>
      </ul>

      <!-- Auth buttons -->
      @if (Route::has('login'))
        @auth
          <a href="{{ url('/dashboard') }}" class="btn btn-outline-warning shadow-sm">
            <i class="bi bi-speedometer2"></i> Dashboard
          </a>
        @else
          <div class="d-flex">
            <a href="{{ route('login') }}" class="btn btn-outline-warning me-2 shadow-sm">
              <i class="bi bi-box-arrow-in-right"></i> Login
            </a>
            @if (Route::has('register'))
              <a href="{{ route('register') }}" class="btn btn-warning text-dark fw-semibold shadow-sm">
                <i class="bi bi-person-plus"></i> Register
              </a>
            @endif
          </div>
        @endauth
      @endif

    </div>
  </div>
</nav>
