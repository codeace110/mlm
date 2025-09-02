@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">

  {{-- Top Row: Balance + Request --}}
  <div class="row mb-4">
    {{-- Balance --}}
    <div class="col-md-4 mb-3">
      <div class="card shadow-sm border-0">
        <div class="card-body text-center p-4 bg-gradient-success text-white rounded-3">
          <div class="mb-2">
            <i class="fas fa-wallet fa-2x"></i>
          </div>
          <h5 class="fw-bold mb-1">Available Balance</h5>
          <h2 class="fw-bolder">₱{{ number_format($user->account_balance, 2) }}</h2>
          <p class="small mb-0 opacity-75">Minimum withdrawal: ₱500</p>
        </div>
      </div>
    </div>

    {{-- Request Withdrawal --}}
    <div class="col-md-8 mb-3">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-dark text-white py-3">
          <h6 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i> Request Cash-out</h6>
        </div>
        <div class="card-body">
          @if($user->account_balance < 500)
            <div class="alert alert-warning">
              <i class="fas fa-exclamation-triangle me-2"></i>
              Insufficient balance. Minimum withdrawal amount is ₱500. Your current balance: ₱{{ number_format($user->account_balance, 2) }}
            </div>
          @elseif(empty($user->phone) || empty($user->address))
            <div class="alert alert-info">
              <i class="fas fa-info-circle me-2"></i>
              Please complete your profile (phone and address) before requesting a withdrawal.
              <a href="{{ route('profile.edit') }}" class="alert-link">Update Profile</a>
            </div>
          @else
            <form method="POST" action="{{ route('withdrawals.store') }}">
              @csrf
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="amount" class="form-label">Amount to Withdraw (₱)</label>
                  <input type="number" class="form-control @error('amount') is-invalid @enderror"
                         name="amount" id="amount" placeholder="Enter amount (min ₱500)"
                         required min="500" max="{{ $user->account_balance }}" step="0.01"
                         value="{{ old('amount') }}">
                  @error('amount')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label for="method" class="form-label">Payment Method</label>
                  <select class="form-select @error('method') is-invalid @enderror"
                          name="method" id="method" required onchange="togglePaymentFields()">
                    <option value="">Select Method</option>
                    <option value="cebuana_lhuillier" {{ old('method') == 'cebuana_lhuillier' ? 'selected' : '' }}>Cebuana Lhuillier</option>
                    <option value="mlhuillier" {{ old('method') == 'mlhuillier' ? 'selected' : '' }}>M Lhuillier</option>
                    <option value="palawan_pawnshop" {{ old('method') == 'palawan_pawnshop' ? 'selected' : '' }}>Palawan Pawnshop</option>
                    <option value="gcash" {{ old('method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                    <option value="paymaya" {{ old('method') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
                  </select>
                  @error('method')
                    <div class="invalid-feedback">{{ $message }}</div>
                  @enderror
                </div>
              </div>

              <!-- Cebuana Lhuillier Fields -->
              <div id="cebuana_lhuillierFields" class="payment-fields" style="display: none;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="cl_branch" class="form-label">Branch Name</label>
                    <input type="text" class="form-control" id="cl_branch" name="account_details[branch_name]"
                           placeholder="Enter branch name" value="{{ old('account_details.branch_name') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="cl_account_number" class="form-label">Account Number</label>
                    <input type="text" class="form-control" id="cl_account_number" name="account_details[account_number]"
                           placeholder="Enter account number" value="{{ old('account_details.account_number') }}">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="cl_account_name" class="form-label">Account Holder Name</label>
                  <input type="text" class="form-control" id="cl_account_name" name="account_details[account_name]"
                         value="{{ old('account_details.account_name', $user->name) }}">
                </div>
              </div>

              <!-- M Lhuillier Fields -->
              <div id="mlhuillierFields" class="payment-fields" style="display: none;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="ml_branch" class="form-label">Branch Name</label>
                    <input type="text" class="form-control" id="ml_branch" name="account_details[branch_name]"
                           placeholder="Enter branch name" value="{{ old('account_details.branch_name') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="ml_account_number" class="form-label">Account Number</label>
                    <input type="text" class="form-control" id="ml_account_number" name="account_details[account_number]"
                           placeholder="Enter account number" value="{{ old('account_details.account_number') }}">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="ml_account_name" class="form-label">Account Holder Name</label>
                  <input type="text" class="form-control" id="ml_account_name" name="account_details[account_name]"
                         value="{{ old('account_details.account_name', $user->name) }}">
                </div>
              </div>

              <!-- Palawan Pawnshop Fields -->
              <div id="palawan_pawnshopFields" class="payment-fields" style="display: none;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="pp_branch" class="form-label">Branch Name</label>
                    <input type="text" class="form-control" id="pp_branch" name="account_details[branch_name]"
                           placeholder="Enter branch name" value="{{ old('account_details.branch_name') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="pp_account_number" class="form-label">Account Number</label>
                    <input type="text" class="form-control" id="pp_account_number" name="account_details[account_number]"
                           placeholder="Enter account number" value="{{ old('account_details.account_number') }}">
                  </div>
                </div>
                <div class="mb-3">
                  <label for="pp_account_name" class="form-label">Account Holder Name</label>
                  <input type="text" class="form-control" id="pp_account_name" name="account_details[account_name]"
                         value="{{ old('account_details.account_name', $user->name) }}">
                </div>
              </div>

              <div id="gcashFields" class="payment-fields" style="display: none;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="gcash_number" class="form-label">GCash Number</label>
                    <input type="text" class="form-control" id="gcash_number" name="account_details[mobile_number]" placeholder="09XXXXXXXXX" value="{{ old('account_details.mobile_number') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="gcash_name" class="form-label">Account Name</label>
                    <input type="text" class="form-control" id="gcash_name" name="account_details[account_name]" value="{{ old('account_details.account_name', $user->name) }}">
                  </div>
                </div>
              </div>

              <div id="paymayaFields" class="payment-fields" style="display: none;">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="paymaya_number" class="form-label">PayMaya Number</label>
                    <input type="text" class="form-control" id="paymaya_number" name="account_details[mobile_number]" placeholder="09XXXXXXXXX" value="{{ old('account_details.mobile_number') }}">
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="paymaya_name" class="form-label">Account Name</label>
                    <input type="text" class="form-control" id="paymaya_name" name="account_details[account_name]" value="{{ old('account_details.account_name', $user->name) }}">
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Balance will be deducted upon admin approval</small>
                <div>
                  <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-history me-1"></i>View All
                  </a>
                  <button type="submit" class="btn bg-gradient-success px-4">
                    <i class="fas fa-paper-plane me-2"></i> Submit Request
                  </button>
                </div>
              </div>

              <!-- Payment Method Information -->
              <div class="mt-3 p-3 bg-light rounded">
                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Processing Information</h6>
                <div class="row">
                  <div class="col-md-6">
                    <strong>Processing Time:</strong>
                    <ul class="list-unstyled small mb-0 mt-1">
                      <li><i class="fas fa-clock text-primary me-1"></i>Cebuana Lhuillier: 1-2 business days</li>
                      <li><i class="fas fa-clock text-success me-1"></i>M Lhuillier: 1-2 business days</li>
                      <li><i class="fas fa-clock text-info me-1"></i>Palawan Pawnshop: 1-2 business days</li>
                      <li><i class="fas fa-clock text-warning me-1"></i>GCash/PayMaya: Same day</li>
                    </ul>
                  </div>
                  <div class="col-md-6">
                    <strong>Fees:</strong>
                    <ul class="list-unstyled small mb-0 mt-1">
                      <li><i class="fas fa-dollar-sign text-primary me-1"></i>Cebuana Lhuillier: ₱30</li>
                      <li><i class="fas fa-dollar-sign text-success me-1"></i>M Lhuillier: ₱30</li>
                      <li><i class="fas fa-dollar-sign text-info me-1"></i>Palawan Pawnshop: ₱30</li>
                      <li><i class="fas fa-dollar-sign text-warning me-1"></i>GCash/PayMaya: ₱15</li>
                    </ul>
                  </div>
                </div>
              </div>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Withdrawal History --}}
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-info text-white py-3 d-flex align-items-center justify-content-between">
          <div>
            <i class="fas fa-history me-2"></i>
            <span>Withdrawal History</span>
          </div>
          <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-light btn-sm">
            <i class="fas fa-eye me-1"></i>View All
          </a>
        </div>

        {{-- Filters --}}
        <div class="card-body border-bottom">
          <form method="GET" class="row g-3">
            <div class="col-md-3">
              <label for="status" class="form-label small fw-bold">Status</label>
              <select name="status" class="form-select form-select-sm" id="status">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="denied" {{ request('status') == 'denied' ? 'selected' : '' }}>Denied</option>
              </select>
            </div>
            <div class="col-md-3">
              <label for="method" class="form-label small fw-bold">Payment Method</label>
              <select name="method" class="form-select form-select-sm" id="method">
                <option value="">All Methods</option>
                <option value="cebuana_lhuillier" {{ request('method') == 'cebuana_lhuillier' ? 'selected' : '' }}>Cebuana Lhuillier</option>
                <option value="mlhuillier" {{ request('method') == 'mlhuillier' ? 'selected' : '' }}>M Lhuillier</option>
                <option value="palawan_pawnshop" {{ request('method') == 'palawan_pawnshop' ? 'selected' : '' }}>Palawan Pawnshop</option>
                <option value="gcash" {{ request('method') == 'gcash' ? 'selected' : '' }}>GCash</option>
                <option value="paymaya" {{ request('method') == 'paymaya' ? 'selected' : '' }}>PayMaya</option>
              </select>
            </div>
            <div class="col-md-2">
              <label for="start_date" class="form-label small fw-bold">From Date</label>
              <input type="date" name="start_date" class="form-control form-control-sm" id="start_date" value="{{ request('start_date') }}">
            </div>
            <div class="col-md-2">
              <label for="end_date" class="form-label small fw-bold">To Date</label>
              <input type="date" name="end_date" class="form-control form-control-sm" id="end_date" value="{{ request('end_date') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
              <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm">
                  <i class="fas fa-filter me-1"></i>Filter
                </button>
                <a href="{{ route('dashboard.payout') }}" class="btn btn-outline-secondary btn-sm">
                  <i class="fas fa-times me-1"></i>Clear
                </a>
              </div>
            </div>
          </form>
        </div>
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover align-items-center mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="text-secondary text-xs fw-bold ps-3">ID</th>
                  <th class="text-secondary text-xs fw-bold">Amount</th>
                  <th class="text-secondary text-xs fw-bold">Method</th>
                  <th class="text-secondary text-xs fw-bold">Status</th>
                  <th class="text-secondary text-xs fw-bold">Date</th>
                </tr>
              </thead>
              <tbody>
                @forelse($withdrawals as $withdrawal)
                <tr>
                  <td class="ps-3">{{ $withdrawal->id }}</td>
                  <td>₱{{ number_format($withdrawal->amount, 2) }}</td>
                  <td>
                    <span class="badge bg-info text-white">
                      {{ ucfirst(str_replace('_', ' ', $withdrawal->method)) }}
                    </span>
                  </td>
                  <td>
                    @if($withdrawal->status === 'approved')
                      <span class="badge rounded-pill bg-gradient-success px-3">Approved</span>
                    @elseif($withdrawal->status === 'pending')
                      <span class="badge rounded-pill bg-gradient-warning px-3 text-dark">Pending</span>
                    @elseif($withdrawal->status === 'denied')
                      <span class="badge rounded-pill bg-gradient-danger px-3">Denied</span>
                    @endif
                  </td>
                  <td>{{ $withdrawal->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center py-4">
                    <p class="text-secondary mb-0">No withdrawal requests found.</p>
                    <small class="text-muted">Your withdrawal history will appear here.</small>
                  </td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>

          {{-- Pagination --}}
          @if($withdrawals->hasPages())
          <div class="card-footer">
            {{ $withdrawals->appends(request()->query())->links() }}
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Statistics Cards --}}
  <div class="row mt-4">
    <div class="col-md-3">
      <div class="card bg-success text-white">
        <div class="card-body text-center">
          <h5>₱{{ number_format($stats['total'], 2) }}</h5>
          <p class="mb-0">Total Withdrawn</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-warning text-white">
        <div class="card-body text-center">
          <h5>₱{{ number_format($stats['pending'], 2) }}</h5>
          <p class="mb-0">Pending</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-info text-white">
        <div class="card-body text-center">
          <h5>₱{{ number_format($stats['approved'], 2) }}</h5>
          <p class="mb-0">Approved</p>
        </div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="card bg-primary text-white">
        <div class="card-body text-center">
          <h5>{{ $stats['denied_count'] }}</h5>
          <p class="mb-0">Denied Requests</p>
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
document.getElementById('amount')?.addEventListener('input', function() {
    const amount = parseFloat(this.value);
    const maxAmount = {{ $user->account_balance }};
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
