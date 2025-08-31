@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <img src="{{ $package->image ? asset($package->image) : asset('assets/img/product-placeholder.jpg') }}"
                                 class="img-fluid rounded" alt="{{ $package->name }}">
                        </div>
                        <div class="col-md-7">
                            <h2 class="font-weight-bold">{{ $package->name }}</h2>
                            <h3 class="text-primary font-weight-bold mb-3">
                                ₱{{ number_format($package->price, 2) }}
                            </h3>
                            <p class="text-muted mb-4">{{ $package->description }}</p>

                            @if($package->features && is_array($package->features))
                            <h5>Package Features:</h5>
                            <ul class="list-unstyled">
                                @foreach($package->features as $feature)
                                <li><i class="fas fa-check text-success me-2"></i>{{ $feature }}</li>
                                @endforeach
                            </ul>
                            @endif

                            <!-- Purchase Form -->
                            <div class="mt-4">
                                <h5>Purchase Package</h5>
                                <form action="{{ route('packages.purchase', $package) }}" method="POST">
                                    @csrf
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="quantity" class="form-label">Quantity</label>
                                            <select name="quantity" id="quantity" class="form-select" onchange="updateTotal()">
                                                @for($i = 1; $i <= 10; $i++)
                                                <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Unit Price</label>
                                            <p class="mb-0 font-weight-bold">₱{{ number_format($package->price, 2) }}</p>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Total</label>
                                            <p id="totalPrice" class="mb-0 font-weight-bold text-primary">
                                                ₱{{ number_format($package->price, 2) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <p class="text-sm text-muted">
                                            Your Balance: ₱{{ number_format(Auth::user()->account_balance, 2) }}
                                        </p>
                                    </div>

                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-shopping-cart me-2"></i>Purchase Now
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Related Packages -->
            @if($relatedPackages->count() > 0)
            <div class="card shadow">
                <div class="card-header">
                    <h6>Related Packages</h6>
                </div>
                <div class="card-body">
                    @foreach($relatedPackages as $related)
                    <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <img src="{{ $related->image ? asset($related->image) : asset('assets/img/product-placeholder.jpg') }}"
                             class="avatar avatar-sm me-3" alt="{{ $related->name }}">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">{{ Str::limit($related->name, 25) }}</h6>
                            <p class="text-primary mb-0 font-weight-bold">₱{{ number_format($related->price, 2) }}</p>
                        </div>
                        <a href="{{ route('packages.show', $related) }}" class="btn btn-outline-primary btn-sm">
                            View
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Package Benefits -->
            <div class="card shadow mt-4">
                <div class="card-header">
                    <h6>Why Choose This Package?</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-shipping-fast text-success me-3"></i>
                        <div>
                            <h6 class="mb-0">Fast Delivery</h6>
                            <small class="text-muted">Delivered within 3-5 business days</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-shield-alt text-primary me-3"></i>
                        <div>
                            <h6 class="mb-0">Quality Guarantee</h6>
                            <small class="text-muted">100% authentic products</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-undo-alt text-info me-3"></i>
                        <div>
                            <h6 class="mb-0">Easy Returns</h6>
                            <small class="text-muted">30-day return policy</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-headset text-warning me-3"></i>
                        <div>
                            <h6 class="mb-0">24/7 Support</h6>
                            <small class="text-muted">Customer support available</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function updateTotal() {
    const quantity = document.getElementById('quantity').value;
    const unitPrice = {{ $package->price }};
    const total = quantity * unitPrice;

    document.getElementById('totalPrice').textContent = '₱' + new Intl.NumberFormat().format(total);
}

// Initialize total on page load
document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
});
</script>
@endsection