@extends('layouts.home')


@section('content')
  <!-- Hero Section -->
  <section class="bg-dark text-white text-center d-flex align-items-center" style="height:100vh; background:linear-gradient(to right, #0d6efd, #6610f2);">
    <div class="container">
      <h1 class="display-3 fw-bold">Grow Your Network. Multiply Your Earnings.</h1>
      <p class="lead mb-4">Join the future of MLM with our smart platform. Build your team, earn rewards, and achieve financial freedom.</p>
      <a href="#" class="btn btn-success btn-lg me-3"><i class="bi bi-person-plus"></i> Get Started</a>
      <a href="#features" class="btn btn-outline-light btn-lg"><i class="bi bi-info-circle"></i> Learn More</a>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-5">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">Why Choose Our MLM Platform?</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
              <i class="bi bi-graph-up-arrow display-4 text-primary"></i>
              <h5 class="mt-3">High Earnings</h5>
              <p>Maximize your profits with our fair and transparent earning structure.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
              <i class="bi bi-people-fill display-4 text-success"></i>
              <h5 class="mt-3">Team Growth</h5>
              <p>Expand your network with easy-to-use referral and invite tools.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 shadow-sm border-0">
            <div class="card-body">
              <i class="bi bi-shield-lock display-4 text-warning"></i>
              <h5 class="mt-3">Secure & Trusted</h5>
              <p>Your data and earnings are protected with enterprise-grade security.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="bg-light py-5">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">How It Works</h2>
      <div class="row g-4">
        <div class="col-md-3">
          <i class="bi bi-person-plus-fill display-4 text-primary"></i>
          <h5 class="mt-3">Sign Up</h5>
          <p>Create your free account in minutes.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-share-fill display-4 text-success"></i>
          <h5 class="mt-3">Invite</h5>
          <p>Share your referral link with friends & family.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-diagram-3-fill display-4 text-danger"></i>
          <h5 class="mt-3">Build</h5>
          <p>Grow your network and expand your reach.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-cash-stack display-4 text-warning"></i>
          <h5 class="mt-3">Earn</h5>
          <p>Receive commissions and rewards instantly.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-5 text-center text-white" style="background:linear-gradient(to right, #20c997, #0dcaf0);">
    <div class="container">
      <h2 class="fw-bold">Ready to Start Your MLM Journey?</h2>
      <p class="lead">Join thousands of members already earning with us!</p>
      <a href="#" class="btn btn-light btn-lg"><i class="bi bi-rocket-takeoff"></i> Join Now</a>
    </div>
  </section>
@endsection
