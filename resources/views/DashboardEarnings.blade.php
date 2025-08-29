@extends('layouts.dashboard')
@section('content')

@php
  // Dummy MLM data (replace later with DB queries)
  $bonuses = [
    'referral' => 1500.00,
    'binary'   => 3200.00,
    'level'    => 900.00,
    'balance'  => 5600.00,
  ];

  $transactions = [
    ['type' => 'credit', 'title' => 'Referral Bonus', 'amount' => 500, 'date' => '27 Aug 2025, 12:30 PM'],
    ['type' => 'credit', 'title' => 'Binary Commission', 'amount' => 2000, 'date' => '27 Aug 2025, 04:30 AM'],
    ['type' => 'credit', 'title' => 'Level Bonus', 'amount' => 750, 'date' => '26 Aug 2025, 01:45 PM'],
    ['type' => 'credit', 'title' => 'Binary Commission', 'amount' => 1000, 'date' => '26 Aug 2025, 12:30 PM'],
    ['type' => 'credit', 'title' => 'Referral Bonus', 'amount' => 2500, 'date' => '26 Aug 2025, 08:30 AM'],
    ['type' => 'pending', 'title' => 'Withdrawal Request', 'amount' => 3000, 'date' => '26 Aug 2025, 05:00 AM'],
  ];

  $payouts = [
    ['date' => 'Aug 20, 2025', 'ref' => '#INV-2025-001', 'amount' => 1500],
    ['date' => 'Aug 10, 2025', 'ref' => '#INV-2025-002', 'amount' => 2000],
    ['date' => 'Jul 28, 2025', 'ref' => '#INV-2025-003', 'amount' => 1000],
    ['date' => 'Jul 15, 2025', 'ref' => '#INV-2025-004', 'amount' => 3000],
  ];
@endphp

<div class="container-fluid py-4">
  <div class="row">
    <div class="col-lg-8">
      <div class="row">
        <!-- Wallet Card -->
        <div class="col-xl-6 mb-xl-0 mb-4">
          <div class="card bg-transparent shadow-xl">
            <div class="overflow-hidden position-relative border-radius-xl" style="background-image: url('../assets/img/curved-images/curved14.jpg');">
              <span class="mask bg-gradient-dark"></span>
              <div class="card-body position-relative z-index-1 p-3">
                <i class="fas fa-wallet text-white p-2"></i>
                <h5 class="text-white mt-4 mb-5 pb-2">My Wallet</h5>
                <div class="d-flex">
                  <div class="d-flex">
                    <div class="me-4">
                      <p class="text-white text-sm opacity-8 mb-0">Available Balance</p>
                      <h6 class="text-white mb-0">₱{{ number_format($bonuses['balance'], 2) }}</h6>
                    </div>
                    <div>
                      <p class="text-white text-sm opacity-8 mb-0">Referral Earnings</p>
                      <h6 class="text-white mb-0">₱{{ number_format($bonuses['referral'], 2) }}</h6>
                    </div>
                  </div>
                  <div class="ms-auto w-20 d-flex align-items-end justify-content-end">
                    <img class="w-60 mt-2" src="../assets/img/logos/mastercard.png" alt="logo">
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bonuses Summary -->
        <div class="col-xl-6">
          <div class="row">
            <div class="col-md-6">
              <div class="card">
                <div class="card-header mx-4 p-3 text-center">
                  <div class="icon icon-shape icon-lg bg-success shadow text-center border-radius-lg">
                    <i class="fas fa-sitemap opacity-10"></i>
                  </div>
                </div>
                <div class="card-body pt-0 p-3 text-center">
                  <h6 class="text-center mb-0">Binary Commissions</h6>
                  <span class="text-xs">Team Earnings</span>
                  <hr class="horizontal dark my-3">
                  <h5 class="mb-0">₱{{ number_format($bonuses['binary'], 2) }}</h5>
                </div>
              </div>
            </div>
            <div class="col-md-6 mt-md-0 mt-4">
              <div class="card">
                <div class="card-header mx-4 p-3 text-center">
                  <div class="icon icon-shape icon-lg bg-warning shadow text-center border-radius-lg">
                    <i class="fas fa-level-up-alt opacity-10"></i>
                  </div>
                </div>
                <div class="card-body pt-0 p-3 text-center">
                  <h6 class="text-center mb-0">Level Bonuses</h6>
                  <span class="text-xs">Downline Earnings</span>
                  <hr class="horizontal dark my-3">
                  <h5 class="mb-0">₱{{ number_format($bonuses['level'], 2) }}</h5>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Payout History -->
        <div class="col-md-12 mb-lg-0 mb-4">
          <div class="card mt-4">
            <div class="card-header pb-0 p-3">
              <div class="row">
                <div class="col-6 d-flex align-items-center">
                  <h6 class="mb-0">Payout History</h6>
                </div>
                <div class="col-6 text-end">
                  <a class="btn bg-gradient-dark mb-0" href="javascript:;"><i class="fas fa-plus"></i>&nbsp;&nbsp;Request Withdrawal</a>
                </div>
              </div>
            </div>
            <div class="card-body p-3">
              <ul class="list-group">
                @foreach($payouts as $payout)
                <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark font-weight-bold text-sm">{{ $payout['date'] }}</h6>
                    <span class="text-xs">{{ $payout['ref'] }}</span>
                  </div>
                  <div class="d-flex align-items-center text-sm">
                    ₱{{ number_format($payout['amount'], 2) }}
                    <button class="btn btn-link text-dark text-sm mb-0 px-0 ms-4"><i class="fas fa-file-invoice text-lg me-1"></i> Receipt</button>
                  </div>
                </li>
                @endforeach
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Transactions -->
    <div class="col-lg-4">
      <div class="card h-100">
        <div class="card-header pb-0 p-3">
          <div class="row">
            <div class="col-6 d-flex align-items-center">
              <h6 class="mb-0">Recent Transactions</h6>
            </div>
            <div class="col-6 text-end">
              <button class="btn btn-outline-primary btn-sm mb-0">View All</button>
            </div>
          </div>
        </div>
        <div class="card-body p-3 pb-0">
          <ul class="list-group">
            @foreach($transactions as $tx)
              <li class="list-group-item border-0 d-flex justify-content-between ps-0 mb-2 border-radius-lg">
                <div class="d-flex align-items-center">
                  <button class="btn btn-icon-only btn-rounded 
                    @if($tx['type'] === 'credit') btn-outline-success 
                    @elseif($tx['type'] === 'pending') btn-outline-dark 
                    @else btn-outline-danger @endif 
                    mb-0 me-3 btn-sm d-flex align-items-center justify-content-center">
                    @if($tx['type'] === 'credit')
                      <i class="fas fa-arrow-up"></i>
                    @elseif($tx['type'] === 'pending')
                      <i class="fas fa-exclamation"></i>
                    @else
                      <i class="fas fa-arrow-down"></i>
                    @endif
                  </button>
                  <div class="d-flex flex-column">
                    <h6 class="mb-1 text-dark text-sm">{{ $tx['title'] }}</h6>
                    <span class="text-xs">{{ $tx['date'] }}</span>
                  </div>
                </div>
                <div class="d-flex align-items-center 
                  @if($tx['type'] === 'credit') text-success 
                  @elseif($tx['type'] === 'pending') text-dark 
                  @else text-danger @endif 
                  text-gradient text-sm font-weight-bold">
                  @if($tx['type'] === 'pending')
                    Pending
                  @else
                    + ₱{{ number_format($tx['amount'], 2) }}
                  @endif
                </div>
              </li>
            @endforeach
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

@endsection
