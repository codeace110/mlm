@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
  <div class="row">
    <!-- Profile Card (User Info + Referral Code) -->
    <div class="col-xl-4 col-lg-5 col-md-6 mb-4">
      <div class="card card-profile shadow-xl border-radius-xl">
        <div class="card-body text-center">
          <!-- Profile Image -->
          <img src="{{ asset('assets/img/team-2.jpg') }}" alt="Profile picture"
               class="rounded-circle shadow border border-2 border-white mb-3"
               width="120" height="120">

          <!-- User Info -->
          <h5 class="mb-1">{{ auth()->user()->name ?? 'John Doe' }}</h5>
          <p class="text-sm text-muted mb-2">
            {{ auth()->user()->email ?? 'example@email.com' }}
          </p>

          <!-- Edit Profile Button -->
          <a href="{{ route('profile.edit') }}" class="btn btn-sm bg-gradient-primary mb-3">
            Edit Profile
          </a>

          <!-- Referral Code Section -->
          @if(auth()->user()->referral_code)
            <div class="card bg-light border-0 shadow-sm p-3 rounded-3">
              <h6 class="text-muted">Your Referral Code</h6>
              <div class="d-flex justify-content-center align-items-center">
                <input type="text" 
                       id="referralCode" 
                       class="form-control text-center fw-bold border-0 bg-transparent"
                       style="max-width: 180px;" 
                       value="{{ auth()->user()->referral_code }}" 
                       readonly>
                <button class="btn btn-sm btn-outline-primary ms-2" onclick="copyReferral()">
                  Copy
                </button>
              </div>
            </div>
          @else
            <!-- If no referral code -->
            <div class="card bg-light border-0 shadow-sm p-3 rounded-3">
              <h6 class="text-muted">No Referral Code Yet</h6>
              <a href="{{ route('dashboard.package') }}" class="btn btn-sm bg-gradient-success mt-2">
                Get a Product Package
              </a>
            </div>
          @endif

        </div>
      </div>
    </div>

    <!-- Profile Details (Editable Info) -->
    <div class="col-xl-8 col-lg-7 col-md-6 mb-4">
      <div class="card shadow-xl border-radius-xl">
        <div class="card-header pb-0 px-3">
          <h6 class="mb-0">Profile Information</h6>
        </div>
        <div class="card-body pt-4 p-3">
          <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PUT')

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" id="name" name="name"
                       class="form-control border px-3"
                       value="{{ old('name', auth()->user()->name) }}">
              </div>

              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" id="email" name="email"
                       class="form-control border px-3"
                       value="{{ old('email', auth()->user()->email) }}">
              </div>
            </div>

            <div class="mb-3">
              <label for="about" class="form-label">About Me</label>
              <textarea id="about" name="about" rows="3"
                        class="form-control border px-3">{{ old('about', auth()->user()->about ?? 'Write something about yourself...') }}</textarea>
            </div>

            <div class="text-end">
              <button type="submit" class="btn bg-gradient-primary">
                Save Changes
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<!-- Copy Script -->
<script>
  function copyReferral() {
    let copyText = document.getElementById("referralCode");
    copyText.select();
    copyText.setSelectionRange(0, 99999); // for mobile
    navigator.clipboard.writeText(copyText.value);
    alert("Referral code copied: " + copyText.value);
  }
</script>
@endsection
