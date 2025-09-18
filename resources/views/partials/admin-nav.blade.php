<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
       aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0" href="{{ route('admin.dashboard') }}">
      <img src="{{ asset('logo.ico') }}" class="navbar-brand-img h-100" alt="main_logo">
      <span class="ms-1 font-weight-bold">Admin Panel</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0">

  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">

      <!-- Dashboard -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 45 40" xmlns="http://www.w3.org/2000/svg">
              <title>Dashboard</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M46.7,10.7 L40.8,0.9 C40.5,0.4 39.9,0 39.2,0 H7.8 C7.1,0 6.5,0.4 6.2,0.9 L0.3,10.7 C0.1,11 0,11.4 0,11.8
                         C0,16.1 3.5,19.6 7.8,19.6 C9.8,19.6 11.6,18.9 13.1,17.6 C16,20.3 20.5,20.3 23.5,17.6 C26.5,20.3 31,20.3 33.9,17.6
                         C36.2,19.6 39.5,20.2 42.4,18.9 C45.2,17.6 47,14.8 47,11.8 C47,11.4 46.9,11 46.7,10.7 Z"/>
                <path class="color-background"
                      d="M39.2,22.5 C37.4,22.5 35.6,22 34,21.1 C31.1,22.7 27.9,22.9 25,21.8 C24.5,21.6 24,21.4 23.5,21.1
                         C20.7,22.7 17.5,22.9 14.5,21.8 C14,21.6 13.5,21.4 13.1,21.1 C11.4,22 9.6,22.5 7.8,22.5 C7.2,22.5 6.5,22.4 5.9,22.3V44.7
                         C5.9,45.9 6.8,46.9 7.8,46.9H19.6V33.6H27.4V46.9H39.2C40.2,46.9 41.1,45.9 41.1,44.7V22.3C40.5,22.4 39.8,22.5 39.2,22.5Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Dashboard</span>
        </a>
      </li>

      <!-- Management Section -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Management</h6>
      </li>

      <!-- Users -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.users.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>Users</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M12.25,17.5H8.75V1.75C8.75,0.8 9.5,0 10.5,0H31.5C32.5,0 33.25,0.8 33.25,1.75V12.25H29.75V3.5H12.25V17.5Z"/>
                <path class="color-background"
                      d="M40.25,14H24.5C23.5,14 22.75,14.8 22.75,15.75V38.5H19.25V22.75C19.25,21.8 18.5,21 17.5,21H1.75
                         C0.8,21 0,21.8 0,22.75V40.25C0,41.2 0.8,42 1.75,42H40.25C41.2,42 42,41.2 42,40.25V15.75C42,14.8 41.2,14 40.25,14Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Users</span>
        </a>
      </li>

      <!-- Genealogy -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.genealogy.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.genealogy.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>Genealogy</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M21,0C9.4,0 0,9.4 0,21C0,32.6 9.4,42 21,42C32.6,42 42,32.6 42,21C42,9.4 32.6,0 21,0ZM21,36C12.7,36 6,29.3 6,21C6,12.7 12.7,6 21,6C29.3,6 36,12.7 36,21C36,29.3 29.3,36 21,36Z"/>
                <circle class="color-background" cx="21" cy="21" r="6"/>
                <circle class="color-background opacity-6" cx="12" cy="12" r="3"/>
                <circle class="color-background opacity-6" cx="30" cy="12" r="3"/>
                <circle class="color-background opacity-6" cx="12" cy="30" r="3"/>
                <circle class="color-background opacity-6" cx="30" cy="30" r="3"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Genealogy</span>
        </a>
      </li>

      <!-- Referral Codes -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.referral_codes.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.referral_codes.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>Referral Codes</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M12.25,17.5H8.75V1.75C8.75,0.8 9.5,0 10.5,0H31.5C32.5,0 33.25,0.8 33.25,1.75V12.25H29.75V3.5H12.25V17.5Z"/>
                <path class="color-background"
                      d="M40.25,14H24.5C23.5,14 22.75,14.8 22.75,15.75V38.5H19.25V22.75C19.25,21.8 18.5,21 17.5,21H1.75
                         C0.8,21 0,21.8 0,22.75V40.25C0,41.2 0.8,42 1.75,42H40.25C41.2,42 42,41.2 42,40.25V15.75C42,14.8 41.2,14 40.25,14Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Referral Codes</span>
        </a>
      </li>

      <!-- Admin Codes -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.admin_codes.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.admin_codes.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
              <title>Admin Codes</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M31.6,23.2C31,23.3 30.5,23.3 30,23.3C29.5,23.3 29,23.3 28.5,23.2L22.4,30.7L29.9,38.3C32.2,40.6 36,40.6 38.3,38.3
                         C40.6,36 40.6,32.2 38.3,29.9L31.6,23.2Z"/>
                <path class="color-background"
                      d="M33.8,11.3L28.7,6.2L34.1,0.9C32.8,0.3 31.4,0 30,0C24.5,0 20,4.5 20,10C20,11 20.1,11.9 20.4,12.8L2.4,27.4
                         C1,28.7 0.1,30.6 0,32.6C0,34.6 0.7,36.5 2.1,37.9C3.5,39.3 5.3,40 7.2,40C9.3,40 11.2,39.1 12.6,37.6L27.2,19.6
                         C28.1,19.9 29,20 30,20C35.5,20 40,15.5 40,10C40,8.6 39.7,7.2 39.1,5.9L33.8,11.3Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Admin Codes</span>
        </a>
      </li>

      <!-- Financial Section -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Financial</h6>
      </li>

      <!-- Bonus Rules -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.bonus_rules.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.bonus_rules.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>Bonus Rules</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M21,0C9.4,0 0,9.4 0,21C0,32.6 9.4,42 21,42C32.6,42 42,32.6 42,21C42,9.4 32.6,0 21,0ZM21,36C12.7,36 6,29.3 6,21C6,12.7 12.7,6 21,6C29.3,6 36,12.7 36,21C36,29.3 29.3,36 21,36Z"/>
                <circle class="color-background" cx="21" cy="21" r="6"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Bonus Rules</span>
        </a>
      </li>

      <!-- Earnings -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.earnings.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.earnings.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>Earnings</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M43,10.75V3.58C43,1.6 41.4,0 39.4,0H3.6C1.6,0 0,1.6 0,3.58V10.75H43Z"/>
                <path class="color-background"
                      d="M0,16.13V32.25C0,34.23 1.6,35.83 3.6,35.83H39.4C41.4,35.83 43,34.23 43,32.25V16.13H0ZM19.7,26.88H7.17V23.29H19.7V26.88Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Earnings</span>
        </a>
      </li>

      <!-- Withdrawals -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('admin.withdrawals.*') ? 'active bg-gradient-primary text-white' : '' }}" href="{{ route('admin.withdrawals.index') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <svg width="12px" height="12px" viewBox="0 0 43 36" xmlns="http://www.w3.org/2000/svg">
              <title>Withdrawals</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M43,10.75V3.58C43,1.6 41.4,0 39.4,0H3.6C1.6,0 0,1.6 0,3.58V10.75H43Z"/>
                <path class="color-background"
                      d="M0,16.13V32.25C0,34.23 1.6,35.83 3.6,35.83H39.4C41.4,35.83 43,34.23 43,32.25V16.13H0ZM19.7,26.88H7.17V23.29H19.7V26.88Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Withdrawals</span>
        </a>
      </li>

      <!-- Account Section -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account</h6>
      </li>
      <li class="nav-item">
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="nav-link btn btn-link text-start w-100">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <svg width="20px" height="20px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <title>Logout</title>
                <path d="M16 17L21 12L16 7" stroke="#000000ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M21 12H9" stroke="#000000ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 5H5C4.44772 5 4 5.44772 4 6V18C4 18.5523 4.44772 19 5 19H12" stroke="#000000ff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
            <span class="nav-link-text ms-1">Log out</span>
          </button>
        </form>
      </li>
    </ul>
  </div>
</aside>

<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
  <!-- Navbar -->
  <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
    <div class="container-fluid py-1 px-3">
      <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
              <li class="breadcrumb-item text-sm">
                  <a class="opacity-5 text-dark" href="javascript:;">Admin</a>
              </li>
              @if(request()->routeIs('admin.dashboard'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
              @elseif(request()->routeIs('admin.users.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Users</li>
              @elseif(request()->routeIs('admin.bonus_rules.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Bonus Rules</li>
              @elseif(request()->routeIs('admin.earnings.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Earnings</li>
              @elseif(request()->routeIs('admin.withdrawals.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Withdrawals</li>
              @elseif(request()->routeIs('admin.genealogy.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Genealogy</li>
              @elseif(request()->routeIs('admin.admin_codes.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Admin Codes</li>
              @elseif(request()->routeIs('admin.referral_codes.index'))
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Referral Codes</li>
              @else
                  <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Page</li>
              @endif
          </ol>

          <h6 class="font-weight-bolder mb-0">
              @if(request()->routeIs('admin.dashboard'))
                  Dashboard
              @elseif(request()->routeIs('admin.users.index'))
                  Users Management
              @elseif(request()->routeIs('admin.bonus_rules.index'))
                  Bonus Rules
              @elseif(request()->routeIs('admin.earnings.index'))
                  Earnings Overview
              @elseif(request()->routeIs('admin.withdrawals.index'))
                  Withdrawals Management
              @elseif(request()->routeIs('admin.genealogy.index'))
                  Genealogy View
              @elseif(request()->routeIs('admin.admin_codes.index'))
                  Admin Codes
              @elseif(request()->routeIs('admin.referral_codes.index'))
                  Referral Codes
              @else
                  Admin Page
              @endif
          </h6>
      </nav>

      <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
        <div class="ms-md-auto pe-md-3 d-flex align-items-center">
          <div class="input-group">
            <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
            <input type="text" class="form-control" placeholder="Type here...">
          </div>
        </div>
        <ul class="navbar-nav justify-content-end">
          <!-- User Info -->
          <li class="nav-item dropdown d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="d-flex align-items-center">
                <img src="{{ asset('assets/img/team-1.jpg') }}" class="avatar avatar-sm me-2" alt="Profile">
                <span class="d-none d-lg-block">{{ Auth::user()->name ?? 'Admin' }}</span>
                <i class="fa fa-chevron-down ms-1 d-none d-lg-block"></i>
              </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end px-2 py-3" aria-labelledby="userDropdown">
              <li class="px-2">
                <div class="d-flex align-items-center">
                  <img src="{{ asset('assets/img/team-1.jpg') }}" class="avatar avatar-sm me-3" alt="Profile">
                  <div>
                    <h6 class="mb-0">{{ Auth::user()->name ?? 'Admin' }}</h6>
                    <p class="text-sm text-muted mb-0">{{ Auth::user()->email ?? '' }}</p>
                  </div>
                </div>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item">
                    <i class="fa fa-sign-out-alt me-2"></i>Log out
                  </button>
                </form>
              </li>
            </ul>
          </li>
          <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
            <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
              <div class="sidenav-toggler-inner">
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
                <i class="sidenav-toggler-line"></i>
              </div>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- End Navbar -->
</main>