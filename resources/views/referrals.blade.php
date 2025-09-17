@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>My Network & Referrals</h6>
                        <a href="{{ route('dashboard.network') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-sitemap me-1"></i>View Binary Network Tree
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Network Statistics -->
                    <div class="row px-4 py-3">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $networkStats['level1'] }}</h3>
                                    <p class="mb-0">Direct Referrals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $networkStats['level2'] }}</h3>
                                    <p class="mb-0">Level 2</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $networkStats['level3'] }}</h3>
                                    <p class="mb-0">Level 3</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h3>{{ $networkStats['total'] }}</h3>
                                    <p class="mb-0">Total Network</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Referrals -->
                    <div class="px-4">
                        <h6 class="mb-3">Recent Referrals</h6>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Joined</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Earnings</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentReferrals as $referral)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $referral->name }}</h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $referral->email }}</p>
                                        </td>
                                        <td>
                                            <span class="badge badge-sm {{ $referral->status === 'approved' ? 'bg-success' : 'bg-warning' }}">
                                                {{ ucfirst($referral->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="text-secondary text-xs font-weight-bold">{{ $referral->created_at->format('M d, Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs font-weight-bold">₱{{ number_format($referral->earnings->sum('amount'), 2) }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            <p class="text-secondary mb-0">No referrals yet. Share your referral link to get started!</p>
                                        </td>
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

    <!-- Referral Link Section -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Your Referral Link</h6>
                </div>
                <div class="card-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="referralLink" value="{{ url('/register?ref=' . Auth::user()->referral_code) }}" readonly>
                        <button class="btn btn-outline-primary" type="button" onclick="copyReferralLink()">
                            <i class="fas fa-copy me-1"></i>Copy Link
                        </button>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Share this link with potential members to earn referral bonuses
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralLink() {
    const referralLink = document.getElementById('referralLink');
    referralLink.select();
    document.execCommand('copy');

    // Show success message
    const button = event.target.closest('button');
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
    button.classList.remove('btn-outline-primary');
    button.classList.add('btn-success');

    setTimeout(() => {
        button.innerHTML = originalText;
        button.classList.remove('btn-success');
        button.classList.add('btn-outline-primary');
    }, 2000);
}
</script>
@endsection