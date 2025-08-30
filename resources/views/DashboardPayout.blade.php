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
          @php $balance = 7500.50; @endphp
          <h5 class="fw-bold mb-1">Available Balance</h5>
          <h2 class="fw-bolder">₱{{ number_format($balance, 2) }}</h2>
          <p class="small mb-0 opacity-75">Withdraw anytime above ₱100</p>
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
          <form method="POST" action="#">
            @csrf
            <div class="mb-3">
              <label for="amount" class="form-label">Amount to Withdraw (₱)</label>
              <input type="number" class="form-control" name="amount" id="amount" placeholder="Enter amount (min ₱100)" required min="100">
            </div>
            <div class="d-flex justify-content-end">
              <button type="submit" class="btn bg-gradient-success px-4">
                <i class="fas fa-paper-plane me-2"></i> Submit Request
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  {{-- Withdrawal History --}}
  <div class="row">
    <div class="col-12">
      <div class="card shadow-sm border-0">
        <div class="card-header bg-gradient-info text-white py-3 d-flex align-items-center">
          <i class="fas fa-history me-2"></i>
          <h6 class="mb-0">Withdrawal History</h6>
        </div>
        <div class="card-body p-0">
          @php
            $withdrawals = [
              ['id' => 1, 'amount' => 1500, 'status' => 'approved', 'created_at' => '2025-08-01'],
              ['id' => 2, 'amount' => 2000, 'status' => 'pending', 'created_at' => '2025-08-10'],
              ['id' => 3, 'amount' => 1000, 'status' => 'rejected', 'created_at' => '2025-08-15'],
            ];
          @endphp

          <div class="table-responsive">
            <table class="table table-hover align-items-center mb-0">
              <thead class="bg-light">
                <tr>
                  <th class="text-secondary text-xs fw-bold ps-3">ID</th>
                  <th class="text-secondary text-xs fw-bold">Amount</th>
                  <th class="text-secondary text-xs fw-bold">Status</th>
                  <th class="text-secondary text-xs fw-bold">Date</th>
                </tr>
              </thead>
              <tbody>
                @foreach($withdrawals as $withdrawal)
                <tr>
                  <td class="ps-3">{{ $withdrawal['id'] }}</td>
                  <td>₱{{ number_format($withdrawal['amount'], 2) }}</td>
                  <td>
                    @if($withdrawal['status'] === 'approved')
                      <span class="badge rounded-pill bg-gradient-success px-3">Approved</span>
                    @elseif($withdrawal['status'] === 'pending')
                      <span class="badge rounded-pill bg-gradient-warning px-3 text-dark">Pending</span>
                    @else
                      <span class="badge rounded-pill bg-gradient-danger px-3">Rejected</span>
                    @endif
                  </td>
                  <td>{{ \Carbon\Carbon::parse($withdrawal['created_at'])->format('M d, Y') }}</td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>
  </div>

</div>
@endsection
