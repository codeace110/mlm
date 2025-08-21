@extends('layouts.home')

@section('content')
  <!-- Hero Section -->
  <section class="text-white text-center d-flex align-items-center" 
           style="height:100vh; background:linear-gradient(135deg, #FF9B00, #FFC900);">
    <div class="container">
      <h1 class="display-3 fw-bold mb-3 text-shadow">Grow Your Network. Multiply Your Earnings.</h1>
      <p class="lead mb-4 opacity-75">Join the future of MLM with our smart platform. Build your team, earn rewards, and achieve financial freedom.</p>
      <a href="#" class="btn btn-lg me-3 shadow rounded-pill" 
         style="background:#FFE100; color:#000; font-weight:600;">
         <i class="bi bi-person-plus"></i> Get Started
      </a>
      <a href="#features" class="btn btn-lg shadow rounded-pill" 
         style="border:2px solid #fff; color:#fff; font-weight:600;">
         <i class="bi bi-info-circle"></i> Learn More
      </a>
    </div>
  </section>

  <!-- Product Showcase -->
  <section id="products" class="py-5 bg-white">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">Our Featured Products</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-lg rounded-4">
            <img src="{{ asset('assets/product1.jpg') }}" class="card-img-top rounded-top-4" alt="Product 1">
            <div class="card-body">
              <h5 class="fw-semibold">Product One</h5>
              <p class="text-muted">High quality product that boosts your lifestyle.</p>
              <a href="#" class="btn btn-warning rounded-pill fw-semibold">Buy Now</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-lg rounded-4">
            <img src="{{ asset('assets/product2.jpg') }}" class="card-img-top rounded-top-4" alt="Product 2">
            <div class="card-body">
              <h5 class="fw-semibold">Product Two</h5>
              <p class="text-muted">Trusted by thousands of customers worldwide.</p>
              <a href="#" class="btn btn-warning rounded-pill fw-semibold">Buy Now</a>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-lg rounded-4">
            <img src="{{ asset('assets/product3.jpg') }}" class="card-img-top rounded-top-4" alt="Product 3">
            <div class="card-body">
              <h5 class="fw-semibold">Product Three</h5>
              <p class="text-muted">Affordable and reliable for everyday use.</p>
              <a href="#" class="btn btn-warning rounded-pill fw-semibold">Buy Now</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Earnings -->
  <section id="earnings" class="text-center text-white py-5" 
           style="background:linear-gradient(135deg, #222, #444);">
    <div class="container">
      <h2 class="fw-bold mb-5">Our Achievements</h2>
      <div class="row">
        <div class="col-md-4">
          <i class="bi bi-cash-stack display-4 text-warning"></i>
          <h3 class="fw-bold">₱1M+</h3>
          <p class="opacity-75">Total Sales</p>
        </div>
        <div class="col-md-4">
          <i class="bi bi-emoji-smile display-4 text-success"></i>
          <h3 class="fw-bold">500+</h3>
          <p class="opacity-75">Happy Customers</p>
        </div>
        <div class="col-md-4">
          <i class="bi bi-box-seam display-4 text-info"></i>
          <h3 class="fw-bold">50+</h3>
          <p class="opacity-75">Products Delivered</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Feedbacks -->
  <section id="feedbacks" class="py-5 bg-light">
    <div class="container">
      <h2 class="text-center fw-bold mb-5">What Our Customers Say</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card shadow-lg border-0 rounded-4 p-4 text-center">
            <img src="{{ asset('assets/user1.jpg') }}" class="rounded-circle mb-3" width="70" height="70" alt="User 1">
            <p class="fst-italic">"Amazing service and high-quality products!"</p>
            <h6 class="fw-semibold">Juan D.</h6>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-lg border-0 rounded-4 p-4 text-center">
            <img src="{{ asset('assets/user2.jpg') }}" class="rounded-circle mb-3" width="70" height="70" alt="User 2">
            <p class="fst-italic">"Fast delivery and excellent customer support."</p>
            <h6 class="fw-semibold">Maria S.</h6>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-lg border-0 rounded-4 p-4 text-center">
            <img src="{{ asset('assets/user3.jpg') }}" class="rounded-circle mb-3" width="70" height="70" alt="User 3">
            <p class="fst-italic">"I keep coming back because they never disappoint."</p>
            <h6 class="fw-semibold">Carlo R.</h6>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-5 bg-white">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">Why Choose Our MLM Platform?</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-lg rounded-4">
            <div class="card-body py-4">
              <i class="bi bi-graph-up-arrow display-4" style="color:#FF9B00;"></i>
              <h5 class="mt-3 fw-semibold">High Earnings</h5>
              <p class="text-muted">Maximize your profits with our fair and transparent earning structure.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-lg rounded-4">
            <div class="card-body py-4">
              <i class="bi bi-people-fill display-4" style="color:#FFC900;"></i>
              <h5 class="mt-3 fw-semibold">Team Growth</h5>
              <p class="text-muted">Expand your network with easy-to-use referral and invite tools.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-lg rounded-4">
            <div class="card-body py-4">
              <i class="bi bi-shield-lock display-4" style="color:#EBE389;"></i>
              <h5 class="mt-3 fw-semibold">Secure & Trusted</h5>
              <p class="text-muted">Your data and earnings are protected with enterprise-grade security.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="py-5" style="background:#FFFBEA;">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">How It Works</h2>
      <div class="row g-4">
        <div class="col-md-3">
          <i class="bi bi-person-plus-fill display-4" style="color:#FF9B00;"></i>
          <h5 class="mt-3 fw-semibold">Sign Up</h5>
          <p class="text-muted">Create your free account in minutes.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-share-fill display-4" style="color:#FFE100;"></i>
          <h5 class="mt-3 fw-semibold">Invite</h5>
          <p class="text-muted">Share your referral link with friends & family.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-diagram-3-fill display-4" style="color:#FFC900;"></i>
          <h5 class="mt-3 fw-semibold">Build</h5>
          <p class="text-muted">Grow your network and expand your reach.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-cash-stack display-4" style="color:#EBE389;"></i>
          <h5 class="mt-3 fw-semibold">Earn</h5>
          <p class="text-muted">Receive commissions and rewards instantly.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-5 text-center text-dark" 
           style="background:linear-gradient(135deg, #FFE100, #FF9B00);">
    <div class="container">
      <h2 class="fw-bold mb-3">Ready to Start Your MLM Journey?</h2>
      <p class="lead opacity-75">Join thousands of members already earning with us!</p>
      <a href="#" class="btn btn-dark btn-lg shadow rounded-pill fw-semibold">
        <i class="bi bi-rocket-takeoff"></i> Join Now
      </a>
    </div>
  </section>
@endsection
