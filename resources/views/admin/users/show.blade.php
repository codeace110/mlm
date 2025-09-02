@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Details: {{ $user->name }}</h5>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Back to Users
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 text-center mb-4">
                            <img src="{{ $user->profile_image ? asset($user->profile_image) : asset('assets/img/team-1.jpg') }}"
                                 class="avatar avatar-xl mb-3" alt="Profile">
                            <h6>{{ $user->name }}</h6>
                            <p class="text-muted mb-2">{{ $user->email }}</p>
                            <span class="badge bg-{{ $user->status === 'approved' ? 'success' : 'warning' }}">
                                {{ ucfirst($user->status) }}
                            </span>
                        </div>
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-2">Referral Code</h6>
                                        <p class="mb-0 fw-bold">{{ $user->referral_code }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-2">Phone</h6>
                                        <p class="mb-0">{{ $user->phone ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-2">Address</h6>
                                        <p class="mb-0">{{ $user->address ?? 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-2">Joined Date</h6>
                                        <p class="mb-0">{{ $user->created_at->format('M d, Y') }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-2">Sponsor</h6>
                                        <p class="mb-0">{{ $user->sponsor->name ?? 'None' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="border rounded p-3">
                                        <h6 class="text-muted mb-2">Downlines</h6>
                                        <p class="mb-0">{{ $user->downlines->count() }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Package Purchase Requests -->
    @if($packagePurchases->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Package Purchase Requests</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-secondary text-xs fw-bold ps-3">Package</th>
                                    <th class="text-secondary text-xs fw-bold">Quantity</th>
                                    <th class="text-secondary text-xs fw-bold">Amount</th>
                                    <th class="text-secondary text-xs fw-bold">Payment Method</th>
                                    <th class="text-secondary text-xs fw-bold">Status</th>
                                    <th class="text-secondary text-xs fw-bold">Date</th>
                                    <th class="text-secondary text-xs fw-bold">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($packagePurchases as $purchase)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm me-3">
                                                <img src="{{ $purchase->package->image ? asset($purchase->package->image) : asset('assets/img/product-placeholder.jpg') }}"
                                                     class="avatar-img rounded" alt="{{ $purchase->package->name }}">
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $purchase->package->name }}</h6>
                                                <small class="text-muted">{{ Str::limit($purchase->package->description, 30) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $purchase->quantity }}</td>
                                    <td>₱{{ number_format($purchase->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-info text-white">
                                            {{ ucfirst(str_replace('_', ' ', $purchase->method)) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($purchase->status === 'approved')
                                            <span class="badge rounded-pill bg-gradient-success px-3">Approved</span>
                                        @elseif($purchase->status === 'pending')
                                            <span class="badge rounded-pill bg-gradient-warning px-3 text-dark">Pending</span>
                                        @elseif($purchase->status === 'denied')
                                            <span class="badge rounded-pill bg-gradient-danger px-3">Denied</span>
                                        @endif
                                    </td>
                                    <td>{{ $purchase->created_at->format('M d, Y') }}</td>
                                    <td>
                                        @if($purchase->status === 'pending')
                                        <div class="btn-group" role="group">
                                            <form action="{{ route('admin.package-purchases.approve', $purchase) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this package purchase?')">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                            </form>
                                            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#denyModal{{ $purchase->id }}">
                                                <i class="fas fa-times me-1"></i>Deny
                                            </button>
                                        </div>
                                        @endif
                                    </td>
                                </tr>

                                <!-- Deny Modal -->
                                @if($purchase->status === 'pending')
                                <div class="modal fade" id="denyModal{{ $purchase->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Deny Package Purchase</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.package-purchases.deny', $purchase) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="admin_notes{{ $purchase->id }}" class="form-label">Reason for Denial</label>
                                                        <textarea class="form-control" id="admin_notes{{ $purchase->id }}" name="admin_notes" rows="3" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Deny Purchase</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection