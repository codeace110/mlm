@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Earnings History</h6>
                        <div class="d-flex gap-2">
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshEarnings()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Earnings Statistics -->
                    <div class="row px-4 py-3">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>₱{{ number_format($stats['total'], 2) }}</h4>
                                    <p class="mb-0">Total Earnings</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4>₱{{ number_format($stats['pending'], 2) }}</h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4>₱{{ number_format($stats['monthly'], 2) }}</h4>
                                    <p class="mb-0">This Month</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>₱{{ number_format($stats['today'], 2) }}</h4>
                                    <p class="mb-0">Today</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="px-4 mb-3">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select name="type" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    <option value="direct_referral" {{ request('type') === 'direct_referral' ? 'selected' : '' }}>Direct Referral</option>
                                    <option value="level_bonus" {{ request('type') === 'level_bonus' ? 'selected' : '' }}>Level Bonus</option>
                                    <option value="matching_bonus" {{ request('type') === 'matching_bonus' ? 'selected' : '' }}>Matching Bonus</option>
                                    <option value="leadership_bonus" {{ request('type') === 'leadership_bonus' ? 'selected' : '' }}>Leadership Bonus</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="start_date" class="form-control form-control-sm"
                                       value="{{ request('start_date') }}">
                            </div>
                            <div class="col-md-2">
                                <input type="date" name="end_date" class="form-control form-control-sm"
                                       value="{{ request('end_date') }}">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary btn-sm w-100">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Earnings Table -->
                    <div class="table-responsive px-4">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($earnings as $earning)
                                <tr>
                                    <td>
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $earning->created_at->format('M d, Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-primary">
                                            {{ ucwords(str_replace('_', ' ', $earning->type)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold text-success">
                                            ₱{{ number_format($earning->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold">
                                            {{ $earning->description }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm {{ $earning->status === 'completed' ? 'bg-success' : 'bg-warning' }}">
                                            {{ ucfirst($earning->status) }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-secondary mb-0">No earnings found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $earnings->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings by Type Chart -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6>Earnings by Type</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($earningsByType as $earningType)
                        <div class="col-md-3 mb-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h5 class="text-primary">₱{{ number_format($earningType->total, 2) }}</h5>
                                    <p class="mb-1">{{ ucwords(str_replace('_', ' ', $earningType->type)) }}</p>
                                    <small class="text-muted">{{ $earningType->count }} transactions</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshEarnings() {
    // Reload the page to refresh data
    window.location.reload();
}

// Auto-refresh earnings stats every 60 seconds
setInterval(function() {
    fetch('/ajax/earnings/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update stats cards if they're visible
                updateStatsCards(data.stats);
            }
        })
        .catch(error => console.error('Error refreshing earnings:', error));
}, 60000);

function updateStatsCards(stats) {
    // Update the statistics cards with new data
    const totalCard = document.querySelector('.card.bg-success h4');
    const pendingCard = document.querySelector('.card.bg-warning h4');
    const monthlyCard = document.querySelector('.card.bg-info h4');
    const todayCard = document.querySelector('.card.bg-primary h4');

    if (totalCard) totalCard.textContent = '₱' + new Intl.NumberFormat().format(stats.total);
    if (pendingCard) pendingCard.textContent = '₱' + new Intl.NumberFormat().format(stats.pending);
    if (monthlyCard) monthlyCard.textContent = '₱' + new Intl.NumberFormat().format(stats.monthly);
    if (todayCard) todayCard.textContent = '₱' + new Intl.NumberFormat().format(stats.today);
}
</script>
@endsection