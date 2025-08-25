<aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
  <div class="sidenav-header">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
       aria-hidden="true" id="iconSidenav"></i>
    <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
      <img src="{{ asset('logo.ico') }}" class="navbar-brand-img h-100" alt="main_logo">
      <span class="ms-1 font-weight-bold">AKEN</span>
    </a>
  </div>
  <hr class="horizontal dark mt-0">

  <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
    <ul class="navbar-nav">

      <!-- Dashboard -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <!-- Dashboard Icon -->
            <svg width="12px" height="12px" viewBox="0 0 45 40" xmlns="http://www.w3.org/2000/svg">
              <title>shop</title>
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

      <!-- Tables -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard.table') ? 'active' : '' }}" href="{{ route('dashboard.table') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <!-- Tables Icon -->
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>office</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M12.25,17.5H8.75V1.75C8.75,0.8 9.5,0 10.5,0H31.5C32.5,0 33.25,0.8 33.25,1.75V12.25H29.75V3.5H12.25V17.5Z"/>
                <path class="color-background"
                      d="M40.25,14H24.5C23.5,14 22.75,14.8 22.75,15.75V38.5H19.25V22.75C19.25,21.8 18.5,21 17.5,21H1.75
                         C0.8,21 0,21.8 0,22.75V40.25C0,41.2 0.8,42 1.75,42H40.25C41.2,42 42,41.2 42,40.25V15.75C42,14.8 41.2,14 40.25,14Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Tables</span>
        </a>
      </li>

      <!-- Bonus Details -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard.billing') ? 'active' : '' }}" href="{{ route('dashboard.billing') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <!-- Credit Card Icon -->
            <svg width="12px" height="12px" viewBox="0 0 43 36" xmlns="http://www.w3.org/2000/svg">
              <title>credit-card</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background opacity-6"
                      d="M43,10.75V3.58C43,1.6 41.4,0 39.4,0H3.6C1.6,0 0,1.6 0,3.58V10.75H43Z"/>
                <path class="color-background"
                      d="M0,16.13V32.25C0,34.23 1.6,35.83 3.6,35.83H39.4C41.4,35.83 43,34.23 43,32.25V16.13H0ZM19.7,26.88H7.17V23.29H19.7V26.88Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Bonus Details</span>
        </a>
      </li>

      <!-- Network -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard.network*') ? 'active' : '' }}" href="{{ Route('dashboard.network') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <!-- 3D Box Icon -->
            <svg width="12px" height="12px" viewBox="0 0 42 42" xmlns="http://www.w3.org/2000/svg">
              <title>box-3d</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background"
                      d="M22.8,19.3L38.9,11.2C39.4,11 39.6,10.4 39.3,9.9L20.3,0.1C19.9-0.05 19.5-0.05 19.1,0.1L3.1,8.1C2.6,8.4 2.4,9 2.7,9.5L21.9,19.3C22.1,19.5 22.5,19.5 22.8,19.3Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Network</span>
        </a>
      </li>

      <!-- Notifications -->
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard.notification*') ? 'active' : '' }}" href="{{ Route('dashboard.notification') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <!-- Settings Icon -->
            <svg width="12px" height="12px" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg">
              <title>settings</title>
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
          <span class="nav-link-text ms-1">Notifications</span>
        </a>
      </li>

      <!-- Profile -->
      <li class="nav-item mt-3">
        <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Account pages</h6>
      </li>
      <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard.profile') ? 'active' : '' }}" href="{{ route('dashboard.profile') }}">
          <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
            <!-- User Icon -->
            <svg width="12px" height="12px" viewBox="0 0 46 42" xmlns="http://www.w3.org/2000/svg">
              <title>customer-support</title>
              <g fill="#FFFFFF" fill-rule="nonzero">
                <path class="color-background"
                      d="M13,28C17.4,28 21,22.5 21,18C21,13.6 17.4,10 13,10C8.6,10 5,13.6 5,18C5,22.5 8.6,28 13,28Z"/>
                <path class="color-background"
                      d="M22.9,32.9C20.8,32 17.3,31 13,31C8.7,31 5.2,32 3.1,32.9C1.2,33.6 0,35.4 0,37.5V41C0,41.6 0.4,42 1,42H25
                         C25.6,42 26,41.6 26,41V37.5C26,35.4 24.8,33.6 22.9,32.9Z"/>
              </g>
            </svg>
          </div>
          <span class="nav-link-text ms-1">Profile</span>
        </a>
      </li>
      <!-- Logout -->
      <li class="nav-item">
        <form action="{{ route('logout') }}" method="POST">
          @csrf
          <button type="submit" class="nav-link btn btn-link text-start w-100">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <!-- Logout Icon SVG -->
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

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm">
                    <a class="opacity-5 text-dark" href="javascript:;">Pages</a>
                </li>
                @if(request()->routeIs('dashboard'))
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Dashboard</li>
                @elseif(request()->routeIs('dashboard.table'))
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Tables</li>
                @elseif(request()->routeIs('dashboard.billing'))
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Bonus Details</li>
                @elseif(request()->routeIs('dashboard.network*'))
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Network</li>
                @elseif(request()->routeIs('dashboard.notification*'))
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Notifications</li>
                @elseif(request()->routeIs('dashboard.profile'))
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Profile</li>
                @else
                    <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Unknown</li>
                @endif
            </ol>

            <h6 class="font-weight-bolder mb-0">
                @if(request()->routeIs('dashboard'))
                    Dashboard
                @elseif(request()->routeIs('dashboard.table'))
                    Tables
                @elseif(request()->routeIs('dashboard.billing'))
                    Bonus Details
                @elseif(request()->routeIs('dashboard.network*'))
                    Network
                @elseif(request()->routeIs('dashboard.notification*'))
                    Notifications
                @elseif(request()->routeIs('dashboard.profile'))
                    Profile
                @else
                    Page
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
          <ul class="navbar-nav  justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a class="btn btn-outline-primary btn-sm mb-0 me-3" target="_blank" href="https://www.creative-tim.com/builder?ref=navbar-soft-ui-dashboard">Online Builder</a>
            </li>
            <li class="nav-item d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
                <form action="{{ route('logout') }}" method="POST">
                   @csrf
                  <button type="submit" class="nav-link btn btn-link text-start w-100"> 
                  <span class="nav-link-text ms-1">Log out</span>
                  </button>
               </form>
              </a>
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
            <li class="nav-item px-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0">
                <i class="fa fa-cog fixed-plugin-button-nav cursor-pointer"></i>
              </a>
            </li>
            <li class="nav-item dropdown pe-2 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa fa-bell cursor-pointer"></i>
              </a>
              <ul class="dropdown-menu  dropdown-menu-end  px-2 py-3 me-sm-n4" aria-labelledby="dropdownMenuButton">
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <img src="../assets/img/team-2.jpg" class="avatar avatar-sm  me-3 ">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          <span class="font-weight-bold">New message</span> from Laur
                        </h6>
                        <p class="text-xs text-secondary mb-0 ">
                          <i class="fa fa-clock me-1"></i>
                          13 minutes ago
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
                <li class="mb-2">
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="my-auto">
                        <img src="../assets/img/small-logos/logo-spotify.svg" class="avatar avatar-sm bg-gradient-dark  me-3 ">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          <span class="font-weight-bold">New album</span> by Travis Scott
                        </h6>
                        <p class="text-xs text-secondary mb-0 ">
                          <i class="fa fa-clock me-1"></i>
                          1 day
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
                <li>
                  <a class="dropdown-item border-radius-md" href="javascript:;">
                    <div class="d-flex py-1">
                      <div class="avatar avatar-sm bg-gradient-secondary  me-3  my-auto">
                        <svg width="12px" height="12px" viewBox="0 0 43 36" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                          <title>credit-card</title>
                          <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                            <g transform="translate(-2169.000000, -745.000000)" fill="#FFFFFF" fill-rule="nonzero">
                              <g transform="translate(1716.000000, 291.000000)">
                                <g transform="translate(453.000000, 454.000000)">
                                  <path class="color-background" d="M43,10.7482083 L43,3.58333333 C43,1.60354167 41.3964583,0 39.4166667,0 L3.58333333,0 C1.60354167,0 0,1.60354167 0,3.58333333 L0,10.7482083 L43,10.7482083 Z" opacity="0.593633743"></path>
                                  <path class="color-background" d="M0,16.125 L0,32.25 C0,34.2297917 1.60354167,35.8333333 3.58333333,35.8333333 L39.4166667,35.8333333 C41.3964583,35.8333333 43,34.2297917 43,32.25 L43,16.125 L0,16.125 Z M19.7083333,26.875 L7.16666667,26.875 L7.16666667,23.2916667 L19.7083333,23.2916667 L19.7083333,26.875 Z M35.8333333,26.875 L28.6666667,26.875 L28.6666667,23.2916667 L35.8333333,23.2916667 L35.8333333,26.875 Z"></path>
                                </g>
                              </g>
                            </g>
                          </g>
                        </svg>
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="text-sm font-weight-normal mb-1">
                          Payment successfully completed
                        </h6>
                        <p class="text-xs text-secondary mb-0 ">
                          <i class="fa fa-clock me-1"></i>
                          2 days
                        </p>
                      </div>
                    </div>
                  </a>
                </li>
              </ul>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->