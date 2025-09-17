@extends('layouts.dashboard')
@section('content')
<div class="container-fluid py-4">


  <!-- My Referrals Table -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6>People who signed up using my referral code</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Placement</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                  <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Joined</th>
                  <th class="text-secondary opacity-7"></th>
                </tr>
              </thead>
              <tbody>
                @forelse($referrals as $ref)
                <tr>
                  <td>
                    <div class="d-flex px-2 py-1">
                      <div>
                        <img src="{{ asset('assets/img/team-1.jpg') }}" class="avatar avatar-sm me-3" alt="user">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $ref->name }}</h6>
                        <p class="text-xs text-secondary mb-0">{{ $ref->email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ ucfirst($ref->placement_side ?? 'left') }}</p>
                    <p class="text-xs text-secondary mb-0">Downlines: {{ $ref->total_downlines }}</p>
                  </td>
                  <td class="align-middle text-center text-sm">
                    @if($ref->status === 'approved')
                      <span class="badge badge-sm bg-gradient-success">Active</span>
                    @else
                      <span class="badge badge-sm bg-gradient-warning">{{ ucfirst($ref->status) }}</span>
                    @endif
                  </td>
                  <td class="align-middle text-center">
                    <span class="text-secondary text-xs font-weight-bold">{{ $ref->created_at->format('d/m/Y') }}</span>
                  </td>
                  <td class="align-middle">
                    <a href="#" class="text-secondary font-weight-bold text-xs">
                      View
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">No referrals yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Referral Earnings / Binary Progress -->
  <div class="row">
    <div class="col-12">
      <div class="card mb-4">
        <div class="card-header pb-0">
          <h6>Referral Activation & Earnings</h6>
        </div>
        <div class="card-body px-0 pt-0 pb-2">
          <div class="table-responsive p-0">
            <table class="table align-items-center justify-content-center mb-0">
              <thead>
                <tr>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Referral</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Package</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Commission</th>
                  <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center opacity-7 ps-2">Network Volume</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                @forelse($referralEarnings as $earn)
                <tr>
                  <td>
                    <div class="d-flex px-2">
                      <div class="my-auto">
                        <h6 class="mb-0 text-sm">{{ $earn->user->name }}</h6>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-sm font-weight-bold mb-0">{{ ucfirst($earn->type) }}</p>
                  </td>
                  <td>
                    <span class="text-xs font-weight-bold">₱{{ number_format($earn->amount, 2) }}</span>
                  </td>
                  <td class="align-middle text-center">
                    <div class="d-flex align-items-center justify-content-center">
                      <span class="me-2 text-xs font-weight-bold">{{ $earn->progress }}%</span>
                      <div class="progress w-75">
                        <div class="progress-bar bg-gradient-info" role="progressbar"
                             style="width: {{ $earn->progress }}%;"></div>
                      </div>
                    </div>
                  </td>
                  <td class="align-middle">
                    <a href="#" class="btn btn-link text-secondary mb-0">
                      <i class="fa fa-ellipsis-v text-xs"></i>
                    </a>
                  </td>
                </tr>
                @empty
                <tr>
                  <td colspan="5" class="text-center text-muted">No referral earnings yet.</td>
                </tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

</div>
@endsection
