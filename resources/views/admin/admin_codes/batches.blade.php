@extends('layouts.admin')

@section('title', 'Admin Code Batches')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Admin Code Batches</h5>
                            <p class="text-sm mb-0">
                                Manage batches of admin codes
                            </p>
                        </div>
                        <div class="ms-auto my-auto mt-lg-0 mt-4">
                            <div class="d-flex gap-2">
                                <a href="{{ route('admin.admin_codes.create') }}" class="btn btn-primary btn-sm">
                                    <i class="ni ni-fat-add"></i> New Batch
                                </a>
                                <a href="{{ route('admin.admin_codes.index') }}" class="btn btn-info btn-sm">
                                    <i class="ni ni-single-copy-04"></i> All Codes
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
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Batch Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Batch ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Codes</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Available</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Assigned</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Used</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($batches as $batch)
                                @php
                                    $codes = \App\Models\AdminCode::where('batch_id', $batch->batch_id)->get();
                                    $available = $codes->where('status', 'available')->count();
                                    $unused = $codes->where('status', 'unused')->count();
                                    $used = $codes->where('status', 'used')->count();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex px-2">
                                            <div class="my-auto">
                                                <h6 class="mb-0 text-sm font-weight-bold">{{ $batch->batch_name }}</h6>
                                                <small class="text-muted">Created: {{ $codes->first()->created_at->format('M d, Y') }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-info">{{ Str::limit($batch->batch_id, 8) }}</span>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ $codes->count() }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-success">{{ $available }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-warning">{{ $unused }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-secondary">{{ $used }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.admin_codes.index', ['batch' => $batch->batch_id]) }}"
                                               class="btn btn-xs btn-info" title="View Codes">
                                                <i class="ni ni-single-copy-04"></i>
                                            </a>
                                            <a href="{{ route('admin.admin_codes.download', ['batch_id' => $batch->batch_id]) }}"
                                               class="btn btn-xs btn-success" title="Download CSV">
                                                <i class="ni ni-archive-2"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <div class="text-muted">
                                            <i class="ni ni-collection" style="font-size: 3rem;"></i>
                                            <p class="mt-2">No batches found.</p>
                                            <a href="{{ route('admin.admin_codes.create') }}" class="btn btn-primary">
                                                Create Your First Batch
                                            </a>
                                        </div>
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

<script>
@if(session('success'))
    // Show success message briefly
    setTimeout(() => {
        // Optional: Add any additional success handling
    }, 1000);
@endif
</script>
@endsection