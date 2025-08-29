@extends('layouts.dashboard')
@section('content')
<div class="container-fluid py-4">

  @php
    // Dummy referrals
    $referrals = [
      (object)[
        'id' => 1,
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'profile_photo_url' => asset('assets/img/team-1.jpg'),
        'placement' => 'left',
        'leg' => 'A',
        'status' => 'active',
        'created_at' => now()->subDays(5),
      ],
      (object)[
        'id' => 2,
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'profile_photo_url' => asset('assets/img/team-2.jpg'),
        'placement' => 'right',
        'leg' => 'B',
        'status' => 'inactive',
        'created_at' => now()->subDays(10),
      ],
    ];

    // Dummy earnings
    $earnings = [
      (object)[
        'id' => 1,
        'referral' => (object)['name' => 'John Doe'],
        'package_name' => 'Starter Pack',
        'commission' => 50,
        'progress' => 60,
      ],
      (object)[
        'id' => 2,
        'referral' => (object)['name' => 'Jane Smith'],
        'package_name' => 'Pro Pack',
        'commission' => 120,
        'progress' => 80,
      ],
    ];
  @endphp

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
                        <img src="{{ $ref->profile_photo_url }}" class="avatar avatar-sm me-3" alt="user">
                      </div>
                      <div class="d-flex flex-column justify-content-center">
                        <h6 class="mb-0 text-sm">{{ $ref->name }}</h6>
                        <p class="text-xs text-secondary mb-0">{{ $ref->email }}</p>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-xs font-weight-bold mb-0">{{ ucfirst($ref->placement) }}</p>
                    <p class="text-xs text-secondary mb-0">Leg: {{ ucfirst($ref->leg) }}</p>
                  </td>
                  <td class="align-middle text-center text-sm">
                    @if($ref->status === 'active')
                      <span class="badge badge-sm bg-gradient-success">Active</span>
                    @else
                      <span class="badge badge-sm bg-gradient-secondary">Inactive</span>
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
                @forelse($earnings as $earn)
                <tr>
                  <td>
                    <div class="d-flex px-2">
                      <div class="my-auto">
                        <h6 class="mb-0 text-sm">{{ $earn->referral->name }}</h6>
                      </div>
                    </div>
                  </td>
                  <td>
                    <p class="text-sm font-weight-bold mb-0">{{ $earn->package_name }}</p>
                  </td>
                  <td>
                    <span class="text-xs font-weight-bold">₱{{ number_format($earn->commission, 2) }}</span>
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
