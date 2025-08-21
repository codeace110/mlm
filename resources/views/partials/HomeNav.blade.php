<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
<!-- Brand / Logo -->
    <img  src="{{ asset('assets/banner.jpg') }}" alt="Logo" style="height:40px; border-radius:50%">

    <a class="navbar-brand fw-bold text-uppercase" href="#">
      <div>
        <i class="bi bi-diagram-3"></i> Aken
      </div>
    </a>


    <!-- Toggler -->
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
      aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Nav links -->
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">
            <i class="bi bi-house-door"></i> Home
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-people"></i> Members</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-currency-exchange"></i> Earnings</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#"><i class="bi bi-diagram-2"></i> Network</a>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
            aria-expanded="false">
            <i class="bi bi-grid"></i> More
          </a>
          <ul class="dropdown-menu dropdown-menu-dark">
            <li><a class="dropdown-item" href="#">About Us</a></li>
            <li><a class="dropdown-item" href="#">Contact</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="#">Support</a></li>
          </ul>
        </li>
      </ul>

      <!-- Auth buttons -->
      <div class="d-flex">
        <a href="#" class="btn btn-outline-light me-2">
          <i class="bi bi-box-arrow-in-right"></i> Login
        </a>
        <a href="#" class="btn btn-success">
          <i class="bi bi-person-plus"></i> Register
        </a>
      </div>
    </div>
  </div>
</nav>
