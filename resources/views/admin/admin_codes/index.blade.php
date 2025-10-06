@extends('layouts.admin')

@section('title', 'Admin Codes Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Codes</p>
                                        <h5 class="font-weight-bolder">{{ $stats['total'] }}</h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                        <i class="ni ni-single-copy-04 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Available</p>
                                        <h5 class="font-weight-bolder text-success">{{ $stats['issued'] }}</h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                        <i class="ni ni-check-bold text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Assigned</p>
                                        <h5 class="font-weight-bolder text-warning">{{ $stats['unused'] }}</h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                        <i class="ni ni-briefcase-24 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                    <div class="card">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Used</p>
                                        <h5 class="font-weight-bolder text-secondary">{{ $stats['used'] }}</h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div class="icon icon-shape bg-gradient-secondary shadow-secondary text-center rounded-circle">
                                        <i class="ni ni-key-25 text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Admin Codes Management</h5>
                            <p class="text-sm mb-0">
                                Manage and generate admin codes for distributors
                            </p>
                        </div>
                        <div class="ms-auto my-auto mt-lg-0 mt-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.admin_codes.create') }}" class="btn btn-primary btn-sm">
                                    <i class="ni ni-fat-add"></i> Generate Batch
                                </a>
                                <a href="{{ route('admin.admin_codes.batches') }}" class="btn btn-info btn-sm">
                                    <i class="ni ni-collection"></i> View Batches
                                </a>
                                <a href="{{ route('admin.admin_codes.download') }}" class="btn btn-success btn-sm">
                                    <i class="ni ni-single-copy-04"></i> Download All
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Code</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Batch</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Assigned To</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Used By</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($codes as $code)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2">
                                            <div class="my-auto">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $code->code }}</h6>
                                                @if($code->batch_name)
                                                    <small class="text-muted">{{ $code->batch_name }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($code->batch_id)
                                            <span class="badge badge-sm bg-info">{{ Str::limit($code->batch_id, 8) }}</span>
                                        @else
                                            <span class="text-muted">Legacy</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $code->status == 'issued' ? 'success' : ($code->status == 'unused' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($code->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($code->distributor)
                                            <div>
                                                <span class="font-weight-bold">{{ $code->distributor->name }}</span>
                                                <br><small class="text-muted">{{ $code->distributor->email }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Unassigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($code->usedByUser)
                                            <div>
                                                <span class="font-weight-bold">{{ $code->usedByUser->name }}</span>
                                                <br><small class="text-muted">{{ $code->used_at->format('M d, Y H:i') }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">Not Used</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.admin_codes.show', $code) }}" class="btn btn-xs btn-info" title="View Details">
                                                <i class="ni ni-single-copy-04"></i>
                                            </a>

                                            @if($code->status == 'issued')
                                            <button type="button" class="btn btn-xs btn-primary" title="Issue Code"
                                                    onclick="issueCode('{{ $code->id }}', '{{ $code->code }}')">
                                                <i class="ni ni-send"></i>
                                            </button>
                                            @elseif($code->status == 'unused')
                                            <form method="POST" action="{{ route('admin.admin_codes.revoke', $code) }}" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to revoke this code?')">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-warning" title="Revoke Code">
                                                    <i class="ni ni-ungroup"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ni ni-single-copy-04" style="font-size: 3rem;"></i>
                                            <p class="mt-2">No admin codes found.</p>
                                            <a href="{{ route('admin.admin_codes.create') }}" class="btn btn-primary">
                                                Generate Your First Batch
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer px-0 d-flex justify-content-between">
                        <div class="text-muted">
                            Showing {{ $codes->firstItem() ?? 0 }} to {{ $codes->lastItem() ?? 0 }} of {{ $codes->total() }} codes
                        </div>
                        {{ $codes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Issue Code Modal -->
<div class="modal fade" id="issueCodeModal" tabindex="-1" aria-labelledby="issueCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="issueCodeModalLabel">Issue Admin Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="issueCodeForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Issue code: <strong id="modalCodeText"></strong></p>
                    <div class="mb-3">
                        <label for="distributorSelect" class="form-label">Select Distributor</label>
                        <select class="form-select" id="distributorSelect" name="distributor_id" required>
                            <option value="">Choose a distributor...</option>
                            @foreach(\App\Models\User::where('is_admin', false)->orderBy('name')->get() as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Issue Code</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function issueCode(codeId, codeText) {
    document.getElementById('modalCodeText').textContent = codeText;
    document.getElementById('issueCodeForm').action = `/admin/admin_codes/${codeId}/issue`;
    new bootstrap.Modal(document.getElementById('issueCodeModal')).show();
}

// Auto-refresh page on successful form submission
@if(session('success'))
    setTimeout(() => {
        window.location.reload();
    }, 2000);
@endif
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection