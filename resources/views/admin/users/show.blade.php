@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">User Details: {{ $user->name }}</h5>
                    <div>
                        <form method="POST" action="{{ route('admin.users.generate-referral-code', $user) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="bi bi-plus-circle me-1"></i>Generate Referral Code
                            </button>
                        </form>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-arrow-left me-1"></i>Back to Users
                        </a>
                    </div>
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

                    <!-- Referral Codes Section -->
                    <div class="mt-4">
                        <h6>Referral Codes</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Code</th>
                                        <th>Status</th>
                                        <th>Used By</th>
                                        <th>Expires At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($user->referralCodes as $code)
                                    <tr>
                                        <td>{{ $code->code }}</td>
                                        <td><span class="badge bg-{{ $code->status === 'available' ? 'success' : 'secondary' }}">{{ ucfirst($code->status) }}</span></td>
                                        <td>{{ $code->usedBy->name ?? 'N/A' }}</td>
                                        <td>{{ $code->expires_at ? $code->expires_at->format('M d, Y') : 'N/A' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No referral codes generated</td>
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

</div>
@endsection