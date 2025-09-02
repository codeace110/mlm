@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex align-items-center">
                        <div>
                            <h6 class="mb-0">Purchase Package</h6>
                            <p class="text-sm text-muted mb-0">Submit your purchase request for admin approval</p>
                        </div>
                        <div class="ms-auto">
                            <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Back to Package
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Package Summary -->
                    <div class="border rounded p-3 mb-4 bg-light">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <img src="{{ $package->image ? asset($package->image) : asset('assets/img/product-placeholder.jpg') }}"
                                     class="img-fluid rounded" alt="{{ $package->name }}" style="max-height: 60px;">
                            </div>
                            <div class="col-md-6">
                                <h6 class="mb-1">{{ $package->name }}</h6>
                                <p class="text-sm text-muted mb-0">{{ Str::limit($package->description, 100) }}</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="mb-2">
                                    <h5 class="text-primary mb-0">₱{{ number_format($package->price, 2) }}</h5>
                                    <small class="text-muted">per unit × {{ $quantity }} qty</small>
                                </div>
                                <h6 class="text-success mb-0">Total: ₱{{ number_format($totalAmount, 2) }}</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Simple Purchase Form -->
                    <form method="POST" action="{{ route('packages.purchase', $package) }}">
                        @csrf
                        <input type="hidden" name="quantity" value="{{ $quantity }}">

                        <!-- Payment Method Selection -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Payment Method</label>
                            <select name="method" class="form-select" required>
                                <option value="">Select Payment Method</option>
                                <option value="cebuana_lhuillier">Cebuana Lhuillier</option>
                                <option value="mlhuillier">M Lhuillier</option>
                                <option value="palawan_pawnshop">Palawan Pawnshop</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Shipping Information -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="shipping_name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="shipping_name" name="shipping_name"
                                       value="{{ old('shipping_name', auth()->user()->shipping_name ?: auth()->user()->name) }}" required>
                                @error('shipping_name')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="shipping_phone" class="form-label">Contact Number</label>
                                <input type="tel" class="form-control" id="shipping_phone" name="shipping_phone"
                                       value="{{ old('shipping_phone', auth()->user()->phone) }}" required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">Complete Address</label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3"
                                      placeholder="Street address, barangay, city, province, postal code" required>{{ old('shipping_address', auth()->user()->address) }}</textarea>
                            @error('shipping_address')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="shipping_city" class="form-label">City</label>
                                <input type="text" class="form-control" id="shipping_city" name="shipping_city"
                                       value="{{ old('shipping_city', auth()->user()->city) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_province" class="form-label">Province</label>
                                <input type="text" class="form-control" id="shipping_province" name="shipping_province"
                                       value="{{ old('shipping_province', auth()->user()->province) }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="shipping_postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control" id="shipping_postal_code" name="shipping_postal_code"
                                       value="{{ old('shipping_postal_code', auth()->user()->postal_code) }}">
                            </div>
                        </div>

                        <!-- Additional Notes -->
                        <div class="mb-3">
                            <label for="notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3"
                                      placeholder="Any special instructions or notes">{{ old('notes') }}</textarea>
                        </div>

                        <!-- Terms Agreement -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the Terms and Conditions
                                </label>
                                @error('terms')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- Information Alert -->
                        <div class="alert alert-info">
                            <h6 class="alert-heading mb-2"><i class="fas fa-info-circle me-2"></i>Important Information</h6>
                            <ul class="list-unstyled small mb-0">
                                <li><i class="fas fa-clock text-primary me-1"></i>Your request will be reviewed by an admin</li>
                                <li><i class="fas fa-check-circle text-success me-1"></i>Balance will be deducted upon approval</li>
                                <li><i class="fas fa-user-check text-info me-1"></i>Complete your profile for faster processing</li>
                            </ul>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Admin approval required before processing</small>
                            <div>
                                <a href="{{ route('packages.show', $package) }}" class="btn btn-outline-secondary me-2">
                                    <i class="fas fa-times me-1"></i>Cancel
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Purchase Request
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Balance Summary -->
            <div class="card shadow mb-4">
                <div class="card-header">
                    <h6 class="mb-0">Account Balance</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-2">
                        <i class="fas fa-wallet fa-2x text-primary"></i>
                    </div>
                    <h4 class="text-primary mb-1">₱{{ number_format(auth()->user()->account_balance, 2) }}</h4>
                    <p class="text-muted small mb-0">Available Balance</p>
                    @if(auth()->user()->account_balance < $totalAmount)
                        <div class="alert alert-warning mt-2 small">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Insufficient balance for this purchase
                        </div>
                    @endif
                </div>
            </div>

            <!-- Important Notes -->
            <div class="card shadow">
                <div class="card-header">
                    <h6 class="mb-0">Purchase Process</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-check-circle text-success me-2"></i>
                        <small>Complete your profile (phone & address) before purchasing</small>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-clock text-info me-2"></i>
                        <small>Wait for admin approval</small>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-money-bill-wave text-warning me-2"></i>
                        <small>Balance deducted upon approval</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection