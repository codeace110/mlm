@extends('layouts.dashboard')
@php
    use Illuminate\Support\Str;
@endphp
@section('content')
    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-lg-6 col-12">
          <div class="row">
            <div class="col-lg-6 col-md-6 col-12">
              <div class="card">
                <span class="mask bg-primary opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                  <div class="row">
                    <div class="col-8 text-start">
                      <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                        <i class="ni ni-circle-08 text-dark text-gradient text-lg opacity-10" aria-hidden="true"></i>
                      </div>
                      <h5 class="text-white font-weight-bolder mb-0 mt-3">
                        {{ $downlinesCount }}
                      </h5>
                      <span class="text-white text-sm">My Downlines</span>
                    </div>
                    <div class="col-4">
                      <div class="dropdown text-end mb-6">
                        <a href="javascript:;" class="cursor-pointer" id="dropdownUsers1" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Options for downlines">
                          <i class="fa fa-ellipsis-h text-white" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownUsers1">
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Action</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Another action</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Something else here</a></li>
                        </ul>
                      </div>
                      <p class="text-white text-sm text-end font-weight-bolder mt-auto mb-0">{{ $downlineGrowthPercent ?? '+0%' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12 mt-4 mt-md-0">
              <div class="card">
                <span class="mask bg-success opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                  <div class="row">
                    <div class="col-8 text-start">
                      <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                        <i class="ni ni-money-coins text-dark text-gradient text-lg opacity-10" aria-hidden="true"></i>
                      </div>
                      <h5 class="text-white font-weight-bolder mb-0 mt-3">
                        ₱{{ number_format($accountBalance, 2) }}
                      </h5>
                      <span class="text-white text-sm">Account Balance</span>
                    </div>
                    <div class="col-4">
                      <div class="dropstart text-end mb-6">
                        <a href="javascript:;" class="cursor-pointer" id="dropdownUsers2" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Options for account balance">
                          <i class="fa fa-ellipsis-h text-white" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownUsers2">
                          <li><a class="dropdown-item border-radius-md" href="{{ route('dashboard.payout') }}">Request Withdrawal</a></li>
                          <li><a class="dropdown-item border-radius-md" href="{{ route('earnings.index') }}">View Earnings</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Balance History</a></li>
                        </ul>
                      </div>
                      <p class="text-white text-sm text-end font-weight-bolder mt-auto mb-0">
                        <span id="balance-change">{{ $balanceGrowthPercent ?? '+0%' }}</span>
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-4">
            <div class="col-lg-6 col-md-6 col-12">
              <div class="card">
                <span class="mask bg-dark opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                  <div class="row">
                    <div class="col-8 text-start">
                      <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                        <i class="ni ni-cart text-dark text-gradient text-lg opacity-10" aria-hidden="true"></i>
                      </div>
                      <h5 class="text-white font-weight-bolder mb-0 mt-3">
                        ₱{{ number_format($totalWithdrawals, 2) }}
                      </h5>
                      <span class="text-white text-sm">Total Withdrawals</span>
                    </div>
                    <div class="col-4">
                      <div class="dropdown text-end mb-6">
                        <a href="javascript:;" class="cursor-pointer" id="dropdownUsers3" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Options for total withdrawals">
                          <i class="fa fa-ellipsis-h text-white" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownUsers3">
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Action</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Another action</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Something else here</a></li>
                        </ul>
                      </div>
                      <p class="text-white text-sm text-end font-weight-bolder mt-auto mb-0">{{ $withdrawalGrowthPercent ?? '+0%' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-6 col-md-6 col-12 mt-4 mt-md-0">
              <div class="card">
                <span class="mask bg-dark opacity-10 border-radius-lg"></span>
                <div class="card-body p-3 position-relative">
                  <div class="row">
                    <div class="col-8 text-start">
                      <div class="icon icon-shape bg-white shadow text-center border-radius-2xl">
                        <i class="ni ni-like-2 text-dark text-gradient text-lg opacity-10" aria-hidden="true"></i>
                      </div>
                      <h5 class="text-white font-weight-bolder mb-0 mt-3">
                        ₱{{ number_format($pendingEarnings, 2) }}
                      </h5>
                      <span class="text-white text-sm">Pending Earnings</span>
                    </div>
                    <div class="col-4">
                      <div class="dropstart text-end mb-6">
                        <a href="javascript:;" class="cursor-pointer" id="dropdownUsers4" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Options for pending earnings">
                          <i class="fa fa-ellipsis-h text-white" aria-hidden="true"></i>
                        </a>
                        <ul class="dropdown-menu px-2 py-3" aria-labelledby="dropdownUsers4">
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Action</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Another action</a></li>
                          <li><a class="dropdown-item border-radius-md" href="javascript:;">Something else here</a></li>
                        </ul>
                      </div>
                      <p class="text-white text-sm text-end font-weight-bolder mt-auto mb-0">{{ $pendingEarningsGrowthPercent ?? '+0%' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-6 col-12 mt-4 mt-lg-0">
          <div class="card shadow h-100">
            <div class="card-header pb-0 p-3">
              <h6 class="mb-0">Reviews</h6>
            </div>
            <div class="card-body pb-0 p-3">
              <ul class="list-group">
                @php
                  $positivePercent = 80; // This should come from database/config
                  $neutralPercent = 17;
                  $negativePercent = 3;
                @endphp
                <li class="list-group-item border-0 d-flex align-items-center px-0 mb-0">
                  <div class="w-100">
                    <div class="d-flex mb-2">
                      <span class="me-2 text-sm font-weight-bold text-dark">Positive Reviews</span>
                      <span class="ms-auto text-sm font-weight-bold">{{ $positivePercent }}%</span>
                    </div>
                    <div>
                      <div class="progress progress-md">
                        <div class="progress-bar bg-primary w-{{ $positivePercent }}" role="progressbar" aria-valuenow="{{ $positivePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex align-items-center px-0 mb-2">
                  <div class="w-100">
                    <div class="d-flex mb-2">
                      <span class="me-2 text-sm font-weight-bold text-dark">Neutral Reviews</span>
                      <span class="ms-auto text-sm font-weight-bold">{{ $neutralPercent }}%</span>
                    </div>
                    <div>
                      <div class="progress progress-md">
                        <div class="progress-bar bg-primary w-{{ $neutralPercent }}" role="progressbar" aria-valuenow="{{ $neutralPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  </div>
                </li>
                <li class="list-group-item border-0 d-flex align-items-center px-0 mb-2">
                  <div class="w-100">
                    <div class="d-flex mb-2">
                      <span class="me-2 text-sm font-weight-bold text-dark">Negative Reviews</span>
                      <span class="ms-auto text-sm font-weight-bold">{{ $negativePercent }}%</span>
                    </div>
                    <div>
                      <div class="progress progress-md">
                        <div class="progress-bar bg-primary w-{{ $negativePercent }}" role="progressbar" aria-valuenow="{{ $negativePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                      </div>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <div class="card-footer pt-0 p-3 d-flex align-items-center">
              <div class="w-60">
                <p class="text-sm">
                  {{ $salesText ?? 'Sales data will be displayed here' }}
                </p>
              </div>
              <div class="w-40 text-end">
                <button class="btn btn-dark mb-0 text-end" disabled>View all reviews</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row my-4">
        <div class="col-lg-8 col-md-12 mb-4">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6>Earnings Overview</h6>
              <p class="text-sm">
                <i class="fa fa-arrow-up text-success" aria-hidden="true"></i>
                <span class="font-weight-bold">₱{{ number_format($totalEarnings, 2) }}</span> total earnings
              </p>
            </div>
            <div class="card-body p-3">
              <!-- Earnings Breakdown Chart -->
              <div class="mb-4">
                <h6 class="text-sm font-weight-bold mb-3">Earnings Breakdown</h6>
                <div class="chart-container" style="position: relative; height: 200px;">
                  <canvas id="earnings-breakdown-chart"></canvas>
                </div>
              </div>

              <!-- Earnings by Type List -->
              <div class="mb-4">
                @forelse($earningsByType as $earning)
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-xs font-weight-bold text-dark">
                    {{ ucwords(str_replace('_', ' ', $earning->type)) }}
                  </span>
                  <span class="text-xs font-weight-bold text-success">
                    ₱{{ number_format($earning->total, 2) }}
                  </span>
                </div>
                @empty
                <p class="text-xs text-muted mb-0">No earnings yet</p>
                @endforelse
              </div>

              <!-- Timeline with max height and scrolling -->
              <div class="timeline timeline-one-side" style="max-height: 400px; overflow-y: auto;">
                @forelse($recentReferrals as $referral)
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="ni ni-single-02 text-success text-gradient"></i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">New Referral: {{ $referral->name }}</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">{{ $referral->created_at->format('d M H:i') }}</p>
                  </div>
                </div>
                @empty
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="ni ni-single-02 text-muted"></i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">No recent referrals</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">Share your referral link to get started</p>
                  </div>
                </div>
                @endforelse

                @forelse($recentEarnings as $earning)
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="ni ni-money-coins text-info text-gradient"></i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">₱{{ number_format($earning->amount, 2) }} - {{ ucwords(str_replace('_', ' ', $earning->type)) }}</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">{{ $earning->created_at->format('d M H:i') }}</p>
                  </div>
                </div>
                @empty
                <div class="timeline-block mb-3">
                  <span class="timeline-step">
                    <i class="ni ni-money-coins text-muted"></i>
                  </span>
                  <div class="timeline-content">
                    <h6 class="text-dark text-sm font-weight-bold mb-0">No recent earnings</h6>
                    <p class="text-secondary font-weight-bold text-xs mt-1 mb-0">Earnings will appear here</p>
                  </div>
                </div>
                @endforelse
              </div>
            </div>
          </div>
        </div>

        <!-- Additional column to fill the row -->
        <div class="col-lg-4 col-md-6 mb-4">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6>Quick Actions</h6>
            </div>
            <div class="card-body p-3">
              <div class="d-grid gap-3">
                <a href="{{ route('referrals.index') }}" class="btn btn-outline-primary">
                  <i class="fas fa-users me-2"></i>View Network
                </a>
                <a href="{{ route('earnings.index') }}" class="btn btn-outline-success">
                  <i class="fas fa-chart-line me-2"></i>View Earnings
                </a>
                <a href="{{ route('dashboard.payout') }}" class="btn btn-outline-warning">
                  <i class="fas fa-money-bill-wave me-2"></i>Request Payout
                </a>
                <a href="{{ route('profile.edit') }}" class="btn btn-outline-info">
                  <i class="fas fa-user-edit me-2"></i>Edit Profile
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-7 mb-lg-0 mb-4">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6 class="mb-0">Account Overview</h6>
            </div>
            <div class="card-body p-3">
              <div class="row">
                <div class="col-lg-6">
                  <div class="d-flex align-items-center mb-4">
                    <img src="{{ Auth::user()->profile_image ? asset(Auth::user()->profile_image) : asset('assets/img/team-1.jpg') }}"
                         class="avatar avatar-xl me-3" alt="Profile">
                    <div>
                      <p class="mb-1 text-sm text-muted">Welcome back,</p>
                      <h5 class="font-weight-bolder mb-1">{{ Auth::user()->name }}</h5>
                      <p class="mb-0 text-sm">Referral Code: <strong class="text-primary">{{ Auth::user()->referral_code }}</strong></p>
                    </div>
                  </div>

                  <!-- Account Status -->
                  <div class="mb-4">
                    <h6 class="text-sm font-weight-bold mb-3">Account Status</h6>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="text-xs">Level</span>
                      <span class="badge bg-gradient-primary">{{ Auth::user()->level ?? 1 }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="text-xs">Status</span>
                      <span class="badge bg-gradient-success">{{ Auth::user()->status ?? 'Active' }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                      <span class="text-xs">Member Since</span>
                      <span class="text-xs">{{ Auth::user()->created_at->format('M Y') }}</span>
                    </div>
                  </div>

                  <!-- Quick Actions -->
                  <div class="d-grid gap-2">
                    <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-user-edit me-1"></i>Edit Profile
                    </a>
                    <a href="{{ route('referrals.index') }}" class="btn btn-outline-success btn-sm">
                      <i class="fas fa-users me-1"></i>View Network
                    </a>
                  </div>
                </div>

                <div class="col-lg-5 ms-auto">
                  <div class="row h-100">
                    <!-- Account Balance Mini Chart -->
                    <div class="col-12 mb-3">
                      <div class="card bg-gradient-primary border-radius-lg h-100">
                        <div class="card-body p-3">
                          <div class="chart-container" style="position: relative; height: 120px;">
                            <canvas id="account-balance-mini-chart"></canvas>
                          </div>
                          <h6 class="text-white font-weight-bold mb-1">Balance Trend</h6>
                          <p class="text-white text-sm mb-0">Last 7 days performance</p>
                        </div>
                      </div>
                    </div>

                    <!-- Progress Section -->
                    <div class="col-12">
                      <div class="card bg-gradient-success border-radius-lg h-100">
                        <div class="card-body p-3 text-center">
                          <i class="fas fa-rocket text-white mb-2" style="font-size: 2rem;"></i>
                          <h6 class="text-white font-weight-bold mb-2">Your Journey</h6>
                          <p class="text-white text-sm mb-3">Keep growing your network!</p>
                          <div class="progress mb-2" style="height: 6px;">
                            <div class="progress-bar bg-white" role="progressbar" style="width: {{ min(100, ($downlinesCount / 10) * 100) }}%"></div>
                          </div>
                          <small class="text-white-50">{{ $downlinesCount }}/10 to next level</small>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="card h-100">
            <div class="card-header pb-0">
              <h6 class="mb-0">Network Growth</h6>
            </div>
            <div class="card-body p-3">
              <!-- Network Stats -->
              <div class="row text-center mb-4">
                <div class="col-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-users text-primary mb-2" style="font-size: 1.5rem;"></i>
                    <h4 class="font-weight-bold mb-0">{{ $downlinesCount }}</h4>
                    <small class="text-muted">Direct</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-sitemap text-success mb-2" style="font-size: 1.5rem;"></i>
                    <h4 class="font-weight-bold mb-0">{{ $networkStats['total'] ?? 0 }}</h4>
                    <small class="text-muted">Total</small>
                  </div>
                </div>
                <div class="col-4">
                  <div class="d-flex flex-column align-items-center">
                    <i class="fas fa-chart-line text-warning mb-2" style="font-size: 1.5rem;"></i>
                    <h4 class="font-weight-bold mb-0">{{ $networkStats['level1'] + $networkStats['level2'] + $networkStats['level3'] }}%</h4>
                    <small class="text-muted">Growth</small>
                  </div>
                </div>
              </div>

              <!-- Motivational Content -->
              <div class="text-center">
                <h6 class="font-weight-bold mb-3">Build Your Network Empire</h6>
                <p class="text-sm text-muted mb-4">Every new member you bring strengthens your earning potential. Share your referral link and watch your network grow!</p>

                <!-- Share Referral Link -->
                <div class="input-group mb-3">
                  <input type="text" class="form-control form-control-sm" value="{{ url('/register?ref=' . Auth::user()->referral_code) }}" readonly id="referralLink">
                  <button class="btn btn-primary btn-sm" type="button" onclick="copyReferralLink()">
                    <i class="fas fa-copy"></i>
                  </button>
                </div>

                <div class="d-grid gap-2">
                  <a href="{{ route('referrals.index') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Add New Member
                  </a>
                  <a href="javascript:;" class="btn btn-outline-primary btn-sm" onclick="shareReferral()">
                    <i class="fas fa-share me-1"></i>Share Link
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mt-4">
        <div class="col-lg-6 mb-lg-0 mb-4">
          <div class="card h-100">
            <div class="card-header pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0">Network Performance Analytics</h6>
                  <p class="text-sm mb-0">
                    <i class="fas fa-chart-line text-success me-1"></i>
                    <span class="font-weight-bold">{{ $networkStats['level1'] + $networkStats['level2'] + $networkStats['level3'] }}%</span> network growth rate
                  </p>
                </div>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-filter me-1"></i>Filter
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterNetworkChart('7d')">Last 7 days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterNetworkChart('30d')">Last 30 days</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterNetworkChart('90d')">Last 90 days</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="card-body p-3">
              <!-- Network Performance Chart -->
              <div class="chart-container" style="position: relative; height: 250px;">
                <canvas id="network-performance-chart"></canvas>
              </div>

              <!-- Network Level Breakdown -->
              <div class="row mt-4">
                <div class="col-6">
                  <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-primary me-3" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                    <div>
                      <p class="text-xs mb-0 font-weight-bold">Direct Level</p>
                      <h6 class="mb-0">{{ $networkStats['level1'] }} members</h6>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" style="width: {{ min(100, ($networkStats['level1'] / max(1, $networkStats['total'])) * 100) }}%"></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-info me-3" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                    <div>
                      <p class="text-xs mb-0 font-weight-bold">Level 2</p>
                      <h6 class="mb-0">{{ $networkStats['level2'] }} members</h6>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-info" style="width: {{ min(100, ($networkStats['level2'] / max(1, $networkStats['total'])) * 100) }}%"></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-warning me-3" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                    <div>
                      <p class="text-xs mb-0 font-weight-bold">Level 3</p>
                      <h6 class="mb-0">{{ $networkStats['level3'] }} members</h6>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" style="width: {{ min(100, ($networkStats['level3'] / max(1, $networkStats['total'])) * 100) }}%"></div>
                  </div>
                </div>
                <div class="col-6">
                  <div class="d-flex align-items-center mb-3">
                    <div class="badge bg-success me-3" style="width: 12px; height: 12px; border-radius: 50%;"></div>
                    <div>
                      <p class="text-xs mb-0 font-weight-bold">Total Network</p>
                      <h6 class="mb-0">{{ $networkStats['total'] }} members</h6>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" style="width: 100%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card h-100">
            <div class="card-header pb-0">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="mb-0">Sales & Revenue Analytics</h6>
                  <p class="text-sm mb-0">
                    <i class="fas fa-arrow-up text-success me-1"></i>
                    <span class="font-weight-bold">{{ $salesGrowthPercent ?? '+12%' }}</span> growth this month
                  </p>
                </div>
                <div class="dropdown">
                  <button class="btn btn-sm btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-calendar me-1"></i>Period
                  </button>
                  <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterSalesChart('weekly')">Weekly</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterSalesChart('monthly')">Monthly</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterSalesChart('yearly')">Yearly</a></li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="card-body p-3">
              <!-- Sales Chart -->
              <div class="chart-container" style="position: relative; height: 250px;">
                <canvas id="sales-analytics-chart"></canvas>
              </div>

              <!-- Sales Metrics -->
              <div class="row mt-4 text-center">
                <div class="col-4">
                  <div class="border-right">
                    <h4 class="font-weight-bold text-primary mb-1">{{ $totalEarnings ? '₱' . number_format($totalEarnings, 0) : '₱0' }}</h4>
                    <p class="text-xs text-muted mb-0">Total Revenue</p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border-right">
                    <h4 class="font-weight-bold text-success mb-1">{{ $accountBalance ? '₱' . number_format($accountBalance, 0) : '₱0' }}</h4>
                    <p class="text-xs text-muted mb-0">Available Balance</p>
                  </div>
                </div>
                <div class="col-4">
                  <div class="border-right">
                    <h4 class="font-weight-bold text-warning mb-1">{{ $pendingEarnings ? '₱' . number_format($pendingEarnings, 0) : '₱0' }}</h4>
                    <p class="text-xs text-muted mb-0">Pending Earnings</p>
                  </div>
                </div>
              </div>

              <!-- Performance Indicators -->
              <div class="mt-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="text-sm">Monthly Target</span>
                  <span class="text-sm font-weight-bold">{{ $salesGrowthPercent ?? '75%' }}</span>
                </div>
                <div class="progress" style="height: 6px;">
                  <div class="progress-bar bg-gradient-success" style="width: {{ $salesGrowthPercent ?? '75%' }}"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <footer class="footer pt-3  ">
        <div class="container-fluid">
          <div class="row align-items-center justify-content-lg-between">
            <div class="col-lg-6 mb-lg-0 mb-4">
              <div class="copyright text-center text-sm text-muted text-lg-start">
                © <script>
                  document.write(new Date().getFullYear())
                </script>,
                {{ config('app.name', 'MLM Platform') }} - All rights reserved.
              </div>
            </div>
            <div class="col-lg-6">
              <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                <li class="nav-item">
                  <a href="{{ route('dashboard') }}" class="nav-link text-muted">Dashboard</a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('referrals.index') }}" class="nav-link text-muted">Network</a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('earnings.index') }}" class="nav-link text-muted">Earnings</a>
                </li>
                <li class="nav-item">
                  <a href="{{ route('dashboard.payout') }}" class="nav-link pe-0 text-muted">Withdrawals</a>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </main>
  <div class="fixed-plugin">
    <a class="fixed-plugin-button text-dark position-fixed px-3 py-2">
      <i class="fa fa-cog py-2"> </i>
    </a>
    <div class="card shadow-lg ">
      <div class="card-header pb-0 pt-3 ">
        <div class="float-start">
          <h5 class="mt-3 mb-0">Soft UI Configurator</h5>
          <p>See our dashboard options.</p>
        </div>
        <div class="float-end mt-4">
          <button class="btn btn-link text-dark p-0 fixed-plugin-close-button">
            <i class="fa fa-close"></i>
          </button>
        </div>
        <!-- End Toggle Button -->
      </div>
      <hr class="horizontal dark my-1">
      <div class="card-body pt-sm-3 pt-0">
        <!-- Sidebar Backgrounds -->
        <div>
          <h6 class="mb-0">Sidebar Colors</h6>
        </div>
        <a href="javascript:void(0)" class="switch-trigger background-color">
          <div class="badge-colors my-2 text-start">
            <span class="badge filter bg-primary active" data-color="primary" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-dark" data-color="dark" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-info" data-color="info" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-success" data-color="success" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-warning" data-color="warning" onclick="sidebarColor(this)"></span>
            <span class="badge filter bg-gradient-danger" data-color="danger" onclick="sidebarColor(this)"></span>
          </div>
        </a>
        <!-- Sidenav Type -->
        <div class="mt-3">
          <h6 class="mb-0">Sidenav Type</h6>
          <p class="text-sm">Choose between 2 different sidenav types.</p>
        </div>
        <div class="d-flex">
          <button class="btn btn-primary w-100 px-3 mb-2 active" data-class="bg-transparent" onclick="sidebarType(this)">Transparent</button>
          <button class="btn btn-primary w-100 px-3 mb-2 ms-2" data-class="bg-white" onclick="sidebarType(this)">White</button>
        </div>
        <p class="text-sm d-xl-none d-block mt-2">You can change the sidenav type just on desktop view.</p>
        <!-- Navbar Fixed -->
        <div class="mt-3">
          <h6 class="mb-0">Navbar Fixed</h6>
        </div>
        <div class="form-check form-switch ps-0">
          <input class="form-check-input mt-1 ms-auto" type="checkbox" id="navbarFixed" onclick="navbarFixed(this)">
        </div>
        <hr class="horizontal dark my-sm-4">
        <a class="btn bg-gradient-dark w-100" href="https://www.creative-tim.com/product/soft-ui-dashboard">Free Download</a>
        <a class="btn btn-outline-dark w-100" href="https://www.creative-tim.com/learning-lab/bootstrap/license/soft-ui-dashboard">View documentation</a>
        <div class="w-100 text-center">
          <a class="github-button" href="https://github.com/creativetimofficial/soft-ui-dashboard" data-icon="octicon-star" data-size="large" data-show-count="true" aria-label="Star creativetimofficial/soft-ui-dashboard on GitHub">Star</a>
          <h6 class="mt-3">Thank you for sharing!</h6>
          <a href="https://twitter.com/intent/tweet?text=Check%20Soft%20UI%20Dashboard%20made%20by%20%40CreativeTim%20%23webdesign%20%23dashboard%20%23bootstrap5&amp;url=https%3A%2F%2Fwww.creative-tim.com%2Fproduct%2Fsoft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-twitter me-1" aria-hidden="true"></i> Tweet
          </a>
          <a href="https://www.facebook.com/sharer/sharer.php?u=https://www.creative-tim.com/product/soft-ui-dashboard" class="btn btn-dark mb-0 me-2" target="_blank">
            <i class="fab fa-facebook-square me-1" aria-hidden="true"></i> Share
          </a>
        </div>
      </div>
    </div>
  </div>

  <script>
      // Copy referral link to clipboard
      function copyReferralLink() {
          const referralLink = document.getElementById('referralLink');
          referralLink.select();
          referralLink.setSelectionRange(0, 99999); // For mobile devices
          navigator.clipboard.writeText(referralLink.value).then(function() {
              // Show success message
              const btn = event.target.closest('button');
              const originalHtml = btn.innerHTML;
              btn.innerHTML = '<i class="fas fa-check"></i>';
              btn.classList.add('btn-success');
              btn.classList.remove('btn-primary');
              setTimeout(() => {
                  btn.innerHTML = originalHtml;
                  btn.classList.remove('btn-success');
                  btn.classList.add('btn-primary');
              }, 2000);
          }).catch(function(err) {
              console.error('Failed to copy: ', err);
          });
      }

      // Share referral link
      function shareReferral() {
          const referralLink = document.getElementById('referralLink').value;
          const shareText = `Join me in this amazing MLM opportunity! Use my referral code: ${referralLink}`;

          if (navigator.share) {
              navigator.share({
                  title: 'Join Our MLM Network',
                  text: shareText,
                  url: referralLink
              });
          } else {
              // Fallback for browsers that don't support Web Share API
              navigator.clipboard.writeText(shareText).then(function() {
                  alert('Referral link copied to clipboard! Share it with your friends.');
              });
          }
      }

      // AJAX functionality for live updates
      function updateDashboardStats() {
          // Update earnings stats
          fetch('/ajax/earnings/stats')
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Update total earnings
                      const totalEarningsElement = document.querySelector('.card:contains("Total Earnings") h5');
                      if (totalEarningsElement) {
                          totalEarningsElement.textContent = '₱' + new Intl.NumberFormat().format(data.stats.total);
                      }

                      // Update pending earnings
                      const pendingEarningsElement = document.querySelector('.card:contains("Pending Earnings") h5');
                      if (pendingEarningsElement) {
                          pendingEarningsElement.textContent = '₱' + new Intl.NumberFormat().format(data.stats.pending);
                      }
                  }
              })
              .catch(error => console.error('Error updating earnings stats:', error));

          // Update withdrawal stats
          fetch('/ajax/withdrawals/stats')
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      // Update total withdrawals
                      const totalWithdrawalsElement = document.querySelector('.card:contains("Total Withdrawals") h5');
                      if (totalWithdrawalsElement) {
                          totalWithdrawalsElement.textContent = '₱' + new Intl.NumberFormat().format(data.stats.total);
                      }

                      // Update account balance
                      const balanceElement = document.querySelector('.card:contains("Account Balance") h5');
                      if (balanceElement) {
                          balanceElement.textContent = '₱' + new Intl.NumberFormat().format(data.stats.available_balance);
                      }
                  }
              })
              .catch(error => console.error('Error updating withdrawal stats:', error));
      }

      // Update stats every 30 seconds
      setInterval(updateDashboardStats, 30000);

      // Initial load
      document.addEventListener('DOMContentLoaded', function() {
          updateDashboardStats();
      });
  </script>

  <!-- Custom Chart Scripts for MLM Dashboard -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
      loadChartData();
  });

  function loadChartData() {
      // Fetch earnings and network data
      fetch('/ajax/dashboard/charts')
          .then(response => response.json())
          .then(data => {
              if (data.success) {
                  renderEarningsChart(data.earnings);
                  renderNetworkChart(data.network);
              }
          })
          .catch(error => console.error('Error loading chart data:', error));

      // Initialize new analytics charts
      renderNetworkPerformanceChart();
      renderSalesAnalyticsChart(data.earnings);
      renderEarningsBreakdownChart();
      renderAccountBalanceMiniChart();
  }

  function renderEarningsChart(earningsData) {
      const ctx = document.getElementById("chart-bars");
      if (!ctx) return;

      // Destroy existing chart if it exists
      if (window.earningsChart) {
          window.earningsChart.destroy();
      }

      window.earningsChart = new Chart(ctx, {
          type: "bar",
          data: {
              labels: earningsData.labels,
              datasets: [{
                  label: "Monthly Earnings",
                  tension: 0.4,
                  borderWidth: 0,
                  borderRadius: 4,
                  borderSkipped: false,
                  backgroundColor: "#fff",
                  data: earningsData.data,
                  maxBarThickness: 6
              }],
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: false,
                  }
              },
              interaction: {
                  intersect: false,
                  mode: 'index',
              },
              scales: {
                  y: {
                      grid: {
                          drawBorder: false,
                          display: false,
                          drawOnChartArea: false,
                          drawTicks: false,
                      },
                      ticks: {
                          suggestedMin: 0,
                          suggestedMax: Math.max(...earningsData.data) * 1.2 || 500,
                          beginAtZero: true,
                          padding: 15,
                          font: {
                              size: 14,
                              family: "Inter",
                              style: 'normal',
                              lineHeight: 2
                          },
                          color: "#fff"
                      },
                  },
                  x: {
                      grid: {
                          drawBorder: false,
                          display: false,
                          drawOnChartArea: false,
                          drawTicks: false
                      },
                      ticks: {
                          display: true,
                          color: "#fff",
                          padding: 10,
                          font: {
                              size: 12,
                              family: "Inter",
                              style: 'normal'
                          }
                      },
                  },
              },
          },
      });
  }

  function renderNetworkChart(networkData) {
      const ctx = document.getElementById("chart-line");
      if (!ctx) return;

      // Destroy existing chart if it exists
      if (window.networkChart) {
          window.networkChart.destroy();
      }

      var gradientStroke1 = ctx.createLinearGradient(0, 230, 0, 50);
      gradientStroke1.addColorStop(1, 'rgba(203,12,159,0.2)');
      gradientStroke1.addColorStop(0.2, 'rgba(72,72,176,0.0)');
      gradientStroke1.addColorStop(0, 'rgba(203,12,159,0)');

      window.networkChart = new Chart(ctx, {
          type: "line",
          data: {
              labels: networkData.labels,
              datasets: [{
                  label: "Network Growth",
                  tension: 0.4,
                  borderWidth: 0,
                  pointRadius: 0,
                  borderColor: "#cb0c9f",
                  borderWidth: 3,
                  backgroundColor: gradientStroke1,
                  fill: true,
                  data: networkData.data,
                  maxBarThickness: 6
              }],
          },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: false,
                  }
              },
              interaction: {
                  intersect: false,
                  mode: 'index',
              },
              scales: {
                  y: {
                      grid: {
                          drawBorder: false,
                          display: true,
                          drawOnChartArea: true,
                          drawTicks: false,
                          borderDash: [5, 5]
                      },
                      ticks: {
                          display: true,
                          padding: 10,
                          color: '#b2b9bf',
                          font: {
                              size: 11,
                              family: "Inter",
                              style: 'normal',
                              lineHeight: 2
                          },
                      }
                  },
                  x: {
                      grid: {
                          drawBorder: false,
                          display: false,
                          drawOnChartArea: false,
                          drawTicks: false,
                          borderDash: [5, 5]
                      },
                      ticks: {
                          display: true,
                          color: '#b2b9bf',
                          padding: 20,
                          font: {
                              size: 11,
                              family: "Inter",
                              style: 'normal',
                              lineHeight: 2
                          },
                      }
                  },
              },
          },
      });
  }

  // New Network Performance Chart
  function renderNetworkPerformanceChart() {
      const ctx = document.getElementById("network-performance-chart");
      if (!ctx) return;

      // Destroy existing chart if it exists
      if (window.networkPerformanceChart) {
          window.networkPerformanceChart.destroy();
      }

      // Real data from PHP variables
      const networkData = {
          labels: ['Level 1', 'Level 2', 'Level 3'],
          datasets: [
              {
                  label: 'Network Members',
                  data: [{{ $networkStats['level1'] }}, {{ $networkStats['level2'] }}, {{ $networkStats['level3'] }}],
                  backgroundColor: ['#5e72e4', '#11cdef', '#fb6340'],
                  borderWidth: 1
              }
          ]
      };

      window.networkPerformanceChart = new Chart(ctx, {
          type: 'bar',
          data: networkData,
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: true,
                      position: 'top',
                      labels: {
                          usePointStyle: true,
                          padding: 20,
                          font: {
                              size: 12
                          }
                      }
                  },
                  tooltip: {
                      mode: 'index',
                      intersect: false,
                      backgroundColor: 'rgba(0,0,0,0.8)',
                      titleColor: '#fff',
                      bodyColor: '#fff',
                      borderColor: 'rgba(255,255,255,0.1)',
                      borderWidth: 1
                  }
              },
              interaction: {
                  mode: 'nearest',
                  axis: 'x',
                  intersect: false
              },
              scales: {
                  x: {
                      display: true,
                      grid: {
                          display: false
                      },
                      ticks: {
                          color: '#8898aa'
                      }
                  },
                  y: {
                      display: true,
                      grid: {
                          color: 'rgba(0,0,0,0.05)',
                          drawBorder: false
                      },
                      ticks: {
                          color: '#8898aa',
                          callback: function(value) {
                              return value + ' members';
                          }
                      }
                  }
              },
              elements: {
                  point: {
                      radius: 4,
                      hoverRadius: 6
                  }
              }
          }
      });
  }

  // New Sales Analytics Chart
  function renderSalesAnalyticsChart(earningsData = null) {
      const ctx = document.getElementById("sales-analytics-chart");
      if (!ctx) return;

      // Destroy existing chart if it exists
      if (window.salesAnalyticsChart) {
          window.salesAnalyticsChart.destroy();
      }

      // Use real data if available, otherwise sample data
      let labels, revenueData, growthData;
      if (earningsData && earningsData.labels && earningsData.data) {
          labels = earningsData.labels;
          revenueData = earningsData.data;
          // Calculate growth percentage
          growthData = revenueData.map((val, index) => {
              if (index === 0) return 0;
              const prev = revenueData[index - 1];
              return prev > 0 ? ((val - prev) / prev * 100) : 0;
          });
      } else {
          // Sample data fallback
          labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'];
          revenueData = [12000, 19000, 15000, 25000, 22000, 30000, 28000];
          growthData = [5, 12, 8, 15, 10, 18, 14];
      }

      const salesData = {
          labels: labels,
          datasets: [
              {
                  label: 'Revenue',
                  data: revenueData,
                  borderColor: '#2dce89',
                  backgroundColor: 'rgba(45, 206, 137, 0.1)',
                  borderWidth: 3,
                  fill: true,
                  tension: 0.4,
                  yAxisID: 'y'
              },
              {
                  label: 'Growth %',
                  data: growthData,
                  borderColor: '#f5365c',
                  backgroundColor: 'rgba(245, 54, 92, 0.1)',
                  borderWidth: 2,
                  borderDash: [5, 5],
                  fill: false,
                  tension: 0.4,
                  yAxisID: 'y1'
              }
          ]
      };

      window.salesAnalyticsChart = new Chart(ctx, {
          type: 'line',
          data: salesData,
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: true,
                      position: 'top',
                      labels: {
                          usePointStyle: true,
                          padding: 20,
                          font: {
                              size: 12
                          }
                      }
                  },
                  tooltip: {
                      mode: 'index',
                      intersect: false,
                      callbacks: {
                          label: function(context) {
                              let label = context.dataset.label || '';
                              if (label) {
                                  label += ': ';
                              }
                              if (context.datasetIndex === 0) {
                                  label += '₱' + context.parsed.y.toLocaleString();
                              } else {
                                  label += context.parsed.y + '%';
                              }
                              return label;
                          }
                      }
                  }
              },
              interaction: {
                  mode: 'nearest',
                  axis: 'x',
                  intersect: false
              },
              scales: {
                  x: {
                      display: true,
                      grid: {
                          display: false
                      },
                      ticks: {
                          color: '#8898aa'
                      }
                  },
                  y: {
                      type: 'linear',
                      display: true,
                      position: 'left',
                      grid: {
                          color: 'rgba(0,0,0,0.05)',
                          drawBorder: false
                      },
                      ticks: {
                          color: '#8898aa',
                          callback: function(value) {
                              return '₱' + (value / 1000) + 'k';
                          }
                      }
                  },
                  y1: {
                      type: 'linear',
                      display: true,
                      position: 'right',
                      grid: {
                          drawOnChartArea: false,
                      },
                      ticks: {
                          color: '#fb6340',
                          callback: function(value) {
                              return value + '%';
                          }
                      }
                  }
              },
              elements: {
                  point: {
                      radius: 4,
                      hoverRadius: 6
                  }
              }
          }
      });
  }

  // Earnings Breakdown Chart
  function renderEarningsBreakdownChart() {
      const ctx = document.getElementById("earnings-breakdown-chart");
      if (!ctx) return;

      // Destroy existing chart if it exists
      if (window.earningsBreakdownChart) {
          window.earningsBreakdownChart.destroy();
      }

      // Real data from PHP variables
      const earningsData = {
          labels: @json($earningsByType->pluck('type')->toArray()),
          datasets: [{
              data: @json($earningsByType->pluck('total')->toArray()),
              backgroundColor: [
                  '#5e72e4', // Primary blue
                  '#2dce89', // Success green
                  '#11cdef', // Info blue
                  '#fb6340', // Warning orange
                  '#f5365c'  // Danger red
              ],
              borderWidth: 0,
              hoverBorderWidth: 2,
              hoverBorderColor: '#fff'
          }]
      };

      window.earningsBreakdownChart = new Chart(ctx, {
          type: 'doughnut',
          data: earningsData,
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: true,
                      position: 'bottom',
                      labels: {
                          padding: 15,
                          usePointStyle: true,
                          font: {
                              size: 11
                          }
                      }
                  },
                  tooltip: {
                      callbacks: {
                          label: function(context) {
                              const label = context.label || '';
                              const value = context.parsed;
                              const total = context.dataset.data.reduce((a, b) => a + b, 0);
                              const percentage = ((value / total) * 100).toFixed(1);
                              return label + ': ₱' + value.toLocaleString() + ' (' + percentage + '%)';
                          }
                      }
                  }
              },
              cutout: '60%'
          }
      });
  }

  // Account Balance Mini Chart
  function renderAccountBalanceMiniChart() {
      const ctx = document.getElementById("account-balance-mini-chart");
      if (!ctx) return;

      // Destroy existing chart if it exists
      if (window.accountBalanceMiniChart) {
          window.accountBalanceMiniChart.destroy();
      }

      // Sample data - in real app, this would come from AJAX
      const balanceData = {
          labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
          datasets: [{
              label: 'Balance',
              data: [12500, 13200, 12800, 14100, 13800, 15200, 15800],
              borderColor: '#ffffff',
              backgroundColor: 'rgba(255, 255, 255, 0.2)',
              borderWidth: 2,
              fill: true,
              tension: 0.4,
              pointRadius: 0,
              pointHoverRadius: 4,
              pointBackgroundColor: '#ffffff',
              pointBorderColor: '#ffffff'
          }]
      };

      window.accountBalanceMiniChart = new Chart(ctx, {
          type: 'line',
          data: balanceData,
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: {
                  legend: {
                      display: false
                  },
                  tooltip: {
                      enabled: false
                  }
              },
              scales: {
                  x: {
                      display: false,
                      grid: {
                          display: false
                      }
                  },
                  y: {
                      display: false,
                      grid: {
                          display: false
                      }
                  }
              },
              elements: {
                  point: {
                      hoverRadius: 6
                  }
              },
              interaction: {
                  intersect: false,
                  mode: 'nearest'
              }
          }
      });
  }

  // Filter functions for charts
  function filterNetworkChart(period) {
      // In real app, this would fetch new data based on period
      console.log('Filtering network chart for period:', period);
      // renderNetworkPerformanceChart(); // Re-render with new data
  }

  function filterSalesChart(period) {
      // In real app, this would fetch new data based on period
      console.log('Filtering sales chart for period:', period);
      // renderSalesAnalyticsChart(); // Re-render with new data
  }

  // Auto-refresh charts every 5 minutes
  setInterval(function() {
      loadChartData();
  }, 300000);
  </script>

@endsection