@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Request Withdrawal</h6>
                        <a href="{{ route('withdrawals.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back to Withdrawals
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Balance Info -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle me-2"></i>
                            <div>
                                <strong>Available Balance:</strong> ₱{{ number_format(Auth::user()->account_balance, 2) }}
                                <br>
                                <small class="text-muted">Minimum withdrawal amount: ₱500.00</small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('withdrawals.store') }}" method="POST">
                        @csrf

                        <!-- Amount -->
                        <div class="mb-3">
                            <label for="amount" class="form-label">Withdrawal Amount (₱)</label>
                            <input type="number" class="form-control @error('amount') is-invalid @enderror"
                                   id="amount" name="amount" min="500"
                                   max="{{ Auth::user()->account_balance }}"
                                   step="0.01" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Enter amount between ₱500 and ₱{{ number_format(Auth::user()->account_balance, 2) }}</div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-3">
                            <label for="method" class="form-label">Payment Method</label>
                            <select class="form-select @error('method') is-invalid @enderror"
                                    id="method" name="method" required onchange="togglePaymentFields()">
                                <option value="">Select Payment Method</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="paypal">PayPal</option>
                                <option value="gcash">GCash</option>
                                <option value="paymaya">PayMaya</option>
                            </select>
                            @error('method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Bank Transfer Fields -->
                        <div id="bankFields" class="payment-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bank_name" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="account_details[bank_name]">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_details[account_number]">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="account_name" class="form-label">Account Holder Name</label>
                                <input type="text" class="form-control" id="account_name" name="account_details[account_name]"
                                       value="{{ Auth::user()->name }}">
                            </div>
                        </div>

                        <!-- PayPal Fields -->
                        <div id="paypalFields" class="payment-fields" style="display: none;">
                            <div class="mb-3">
                                <label for="paypal_email" class="form-label">PayPal Email</label>
                                <input type="email" class="form-control" id="paypal_email" name="account_details[email]"
                                       value="{{ Auth::user()->email }}">
                            </div>
                        </div>

                        <!-- GCash Fields -->
                        <div id="gcashFields" class="payment-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_number" class="form-label">GCash Number</label>
                                    <input type="text" class="form-control" id="gcash_number" name="account_details[mobile_number]"
                                           placeholder="09XXXXXXXXX">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="gcash_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="gcash_name" name="account_details[account_name]"
                                           value="{{ Auth::user()->name }}">
                                </div>
                            </div>
                        </div>

                        <!-- PayMaya Fields -->
                        <div id="paymayaFields" class="payment-fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="paymaya_number" class="form-label">PayMaya Number</label>
                                    <input type="text" class="form-control" id="paymaya_number" name="account_details[mobile_number]"
                                           placeholder="09XXXXXXXXX">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="paymaya_name" class="form-label">Account Name</label>
                                    <input type="text" class="form-control" id="paymaya_name" name="account_details[account_name]"
                                           value="{{ Auth::user()->name }}">
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" target="_blank">Terms and Conditions</a> and
                                    <a href="#" target="_blank">Withdrawal Policy</a>
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-1"></i>Submit Withdrawal Request
                            </button>
                            <a href="{{ route('withdrawals.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Withdrawal Info -->
            <div class="card mt-4">
                <div class="card-header">
                    <h6>Withdrawal Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Processing Time</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-clock text-primary me-2"></i>Bank Transfer: 1-3 business days</li>
                                <li><i class="fas fa-clock text-success me-2"></i>E-wallets: 1-2 business days</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Fees</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-dollar-sign text-warning me-2"></i>Bank Transfer: ₱50</li>
                                <li><i class="fas fa-dollar-sign text-success me-2"></i>E-wallets: ₱25</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePaymentFields() {
    const method = document.getElementById('method').value;
    const allFields = document.querySelectorAll('.payment-fields');

    // Hide all payment fields
    allFields.forEach(field => field.style.display = 'none');

    // Show selected payment fields
    if (method) {
        document.getElementById(method + 'Fields').style.display = 'block';
    }
}

// Validate amount on input
document.getElementById('amount').addEventListener('input', function() {
    const amount = parseFloat(this.value);
    const maxAmount = {{ Auth::user()->account_balance }};
    const minAmount = 500;

    if (amount < minAmount) {
        this.setCustomValidity('Amount must be at least ₱500');
    } else if (amount > maxAmount) {
        this.setCustomValidity('Amount cannot exceed your available balance');
    } else {
        this.setCustomValidity('');
    }
});
</script>
@endsection