@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">My Referral Codes</h6>
                        <div class="stats">
                            <span class="text-success text-sm font-weight-bold">Total: {{ $totalCodes }}</span>
                            <span class="text-warning text-sm font-weight-bold mx-2">Assigned: {{ $assignedCodes }}</span>
                            <span class="text-info text-sm font-weight-bold">Used: {{ $usedCodes }}</span>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-2">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Code</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Used By</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Generated</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($referralCodes as $code)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div>
                                                <h6 class="mb-0 text-sm font-weight-bold ms-3">{{ $code->code }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $code->status == 'available' ? 'success' : ($code->status == 'assigned' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($code->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $code->usedBy ? $code->usedBy->name . ' (' . $code->usedBy->email . ')' : 'Not Used' }}
                                    </td>
                                    <td>
                                        {{ $code->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="text-center">
                                        <a href="#" class="btn btn-link btn-sm text-dark p-0" data-bs-toggle="modal" data-bs-target="#codeModal{{ $code->id }}">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>

                                <!-- Modal for each code -->
                                <div class="modal fade" id="codeModal{{ $code->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Code Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p><strong>Code:</strong> {{ $code->code }}</p>
                                                <p><strong>Status:</strong> {{ ucfirst($code->status) }}</p>
                                                @if($code->usedBy)
                                                <p><strong>Used By:</strong> {{ $code->usedBy->name }}</p>
                                                <p><strong>Email:</strong> {{ $code->usedBy->email }}</p>
                                                @endif
                                                <p><strong>Generated:</strong> {{ $code->created_at->format('Y-m-d H:i') }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4">No referral codes assigned to you yet.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer px-3">
                        {{ $referralCodes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Back to Referrals -->
<div class="row mt-4">
    <div class="col-12">
        <a href="{{ route('referrals.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Referrals
        </a>
    </div>
</div>
@endsection