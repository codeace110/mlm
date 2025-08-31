@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Withdrawal History</h6>
                        <a href="{{ route('withdrawals.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>Request Withdrawal
                        </a>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <!-- Withdrawal Statistics -->
                    <div class="row px-4 py-3">
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4>₱{{ number_format($stats['total'], 2) }}</h4>
                                    <p class="mb-0">Total Withdrawn</p>
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
                                    <h4>₱{{ number_format($stats['approved'], 2) }}</h4>
                                    <p class="mb-0">Approved</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4>₱{{ number_format($stats['available_balance'], 2) }}</h4>
                                    <p class="mb-0">Available Balance</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="px-4 mb-3">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="denied" {{ request('status') === 'denied' ? 'selected' : '' }}>Denied</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select name="method" class="form-select form-select-sm">
                                    <option value="">All Methods</option>
                                    <option value="bank_transfer" {{ request('method') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="paypal" {{ request('method') === 'paypal' ? 'selected' : '' }}>PayPal</option>
                                    <option value="gcash" {{ request('method') === 'gcash' ? 'selected' : '' }}>GCash</option>
                                    <option value="paymaya" {{ request('method') === 'paymaya' ? 'selected' : '' }}>PayMaya</option>
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

                    <!-- Withdrawals Table -->
                    <div class="table-responsive px-4">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Method</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($withdrawals as $withdrawal)
                                <tr>
                                    <td>
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $withdrawal->created_at->format('M d, Y H:i') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold text-danger">
                                            ₱{{ number_format($withdrawal->amount, 2) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-info">
                                            {{ ucwords(str_replace('_', ' ', $withdrawal->method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm
                                            @if($withdrawal->status === 'approved') bg-success
                                            @elseif($withdrawal->status === 'pending') bg-warning
                                            @else bg-danger @endif">
                                            {{ ucfirst($withdrawal->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-link text-primary btn-sm p-0" onclick="showWithdrawalDetails({{ $withdrawal->id }})">
                                            <i class="fas fa-eye me-1"></i>View
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <p class="text-secondary mb-0">No withdrawal requests found.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $withdrawals->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Withdrawal Details Modal -->
<div class="modal fade" id="withdrawalDetailsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Withdrawal Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="withdrawalDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function showWithdrawalDetails(withdrawalId) {
    // Find the withdrawal data from the table
    const withdrawalRow = document.querySelector(`button[onclick*="${withdrawalId}"]`).closest('tr');
    const cells = withdrawalRow.querySelectorAll('td');

    const date = cells[0].textContent.trim();
    const amount = cells[1].textContent.trim();
    const method = cells[2].textContent.trim();
    const status = cells[3].textContent.trim();

    let detailsHtml = `
        <div class="row">
            <div class="col-6"><strong>Date:</strong></div>
            <div class="col-6">${date}</div>
        </div>
        <div class="row mt-2">
            <div class="col-6"><strong>Amount:</strong></div>
            <div class="col-6">${amount}</div>
        </div>
        <div class="row mt-2">
            <div class="col-6"><strong>Method:</strong></div>
            <div class="col-6">${method}</div>
        </div>
        <div class="row mt-2">
            <div class="col-6"><strong>Status:</strong></div>
            <div class="col-6">${status}</div>
        </div>
    `;

    document.getElementById('withdrawalDetailsContent').innerHTML = detailsHtml;
    new bootstrap.Modal(document.getElementById('withdrawalDetailsModal')).show();
}

// Auto-refresh withdrawal stats every 60 seconds
setInterval(function() {
    fetch('/ajax/withdrawals/stats')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateWithdrawalStats(data.stats);
            }
        })
        .catch(error => console.error('Error refreshing withdrawals:', error));
}, 60000);

function updateWithdrawalStats(stats) {
    const totalCard = document.querySelector('.card.bg-success h4');
    const pendingCard = document.querySelector('.card.bg-warning h4');
    const approvedCard = document.querySelector('.card.bg-info h4');
    const balanceCard = document.querySelector('.card.bg-primary h4');

    if (totalCard) totalCard.textContent = '₱' + new Intl.NumberFormat().format(stats.total);
    if (pendingCard) pendingCard.textContent = '₱' + new Intl.NumberFormat().format(stats.pending);
    if (approvedCard) approvedCard.textContent = '₱' + new Intl.NumberFormat().format(stats.approved);
    if (balanceCard) balanceCard.textContent = '₱' + new Intl.NumberFormat().format(stats.available_balance);
}
</script>
@endsection