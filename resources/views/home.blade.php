@extends('layouts.home')

@section('content')
  <!-- Hero Section -->
  <section class="text-white text-center d-flex align-items-center" 
           style="height:100vh; background:linear-gradient(135deg, #0A192F, #112240);">
    <div class="container">
      <h1 class="display-3 fw-bold mb-3">Transform Your Financial Destiny</h1>
      <p class="lead mb-4 opacity-75">Join thousands of Filipinos who've built sustainable wealth and achieved true financial freedom through our proven system.</p>
      <a href="#" class="btn btn-lg me-3 shadow rounded-pill fw-semibold" 
         style="background:#FFC107; color:#000;">
         <i class="bi bi-person-plus"></i> Get Started
      </a>
      <a href="#features" class="btn btn-lg shadow rounded-pill fw-semibold" 
         style="border:2px solid #00C9A7; color:#00C9A7;">
         <i class="bi bi-info-circle"></i> Learn More
      </a>
    </div>
  </section>

  <!-- Product Showcase -->
  <section id="products" class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">Our Featured Products</h2>
      <div class="row g-4">
        @foreach (['product1','product2','product3'] as $index => $product)
        <div class="col-md-4">
          <div class="card border-0 shadow-lg rounded-4 h-100">
            <img src="{{ asset('assets/'.$product.'.jpg') }}" class="card-img-top rounded-top-4" alt="Product {{ $index+1 }}">
            <div class="card-body">
              <h5 class="fw-semibold">Product {{ $index+1 }}</h5>
              <p class="text-muted">High quality product trusted by customers worldwide.</p>
              <a href="#" class="btn btn-warning rounded-pill fw-semibold">Buy Now</a>
            </div>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Earnings -->
  <section id="earnings" class="text-center text-white py-5" 
           style="background:linear-gradient(135deg, #112240, #0A192F);">
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
  <section id="feedbacks" class="py-5 bg-white">
    <div class="container">
      <h2 class="text-center fw-bold mb-5">What Our Customers Say</h2>
      <div class="row g-4">
        @foreach (['user1','user2','user3'] as $index => $user)
        <div class="col-md-4">
          <div class="card shadow-lg border-0 rounded-4 p-4 text-center h-100">
            <img src="{{ asset('assets/'.$user.'.jpg') }}" class="rounded-circle mb-3" width="70" height="70" alt="User {{ $index+1 }}">
            <p class="fst-italic">"Amazing service and high-quality products!"</p>
            <h6 class="fw-semibold">Customer {{ $index+1 }}</h6>
          </div>
        </div>
        @endforeach
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="py-5 bg-light">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">Why Choose Our MLM Platform?</h2>
      <div class="row g-4">
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-lg rounded-4">
            <div class="card-body py-4">
              <i class="bi bi-graph-up-arrow display-4 text-warning"></i>
              <h5 class="mt-3 fw-semibold">High Earnings</h5>
              <p class="text-muted">Maximize your profits with our fair and transparent earning structure.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-lg rounded-4">
            <div class="card-body py-4">
              <i class="bi bi-people-fill display-4" style="color:#00C9A7;"></i>
              <h5 class="mt-3 fw-semibold">Team Growth</h5>
              <p class="text-muted">Expand your network with easy-to-use referral and invite tools.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card h-100 border-0 shadow-lg rounded-4">
            <div class="card-body py-4">
              <i class="bi bi-shield-lock display-4 text-primary"></i>
              <h5 class="mt-3 fw-semibold">Secure & Trusted</h5>
              <p class="text-muted">Your data and earnings are protected with enterprise-grade security.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- How It Works -->
  <section class="py-5" style="background:#F8F9FA;">
    <div class="container text-center">
      <h2 class="fw-bold mb-5">How It Works</h2>
      <div class="row g-4">
        <div class="col-md-3">
          <i class="bi bi-person-plus-fill display-4 text-warning"></i>
          <h5 class="mt-3 fw-semibold">Sign Up</h5>
          <p class="text-muted">Create your free account in minutes.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-share-fill display-4 text-info"></i>
          <h5 class="mt-3 fw-semibold">Invite</h5>
          <p class="text-muted">Share your referral link with friends & family.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-diagram-3-fill display-4 text-success"></i>
          <h5 class="mt-3 fw-semibold">Build</h5>
          <p class="text-muted">Grow your network and expand your reach.</p>
        </div>
        <div class="col-md-3">
          <i class="bi bi-cash-stack display-4 text-warning"></i>
          <h5 class="mt-3 fw-semibold">Earn</h5>
          <p class="text-muted">Receive commissions and rewards instantly.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA Section -->
  <section class="py-5 text-center text-white" 
           style="background:linear-gradient(135deg, #FFC107, #FF9800);">
    <div class="container">
      <h2 class="fw-bold mb-3">Ready to Start Your MLM Journey?</h2>
      <p class="lead opacity-75">Join thousands of members already earning with us!</p>
      <a href="#" class="btn btn-dark btn-lg shadow rounded-pill fw-semibold">
        <i class="bi bi-rocket-takeoff"></i> Join Now
      </a>
    </div>
  </section>

  
@endsection
