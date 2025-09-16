@extends('layouts.admin')

@section('title', 'Admin Codes')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
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
                            <div class="ms-auto my-auto">
                                <form method="POST" action="{{ route('admin.admin_codes.generate') }}" class="d-inline">
                                    @csrf
                                    <div class="input-group input-group-outline" style="width: 200px;">
                                        <label class="form-label">Generate Count</label>
                                        <input type="number" class="form-control" name="count" min="1" max="1000" value="50" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm mt-2">Generate Codes</button>
                                </form>
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
                                                <h6 class="mb-0 text-sm">{{ $code->code }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-{{ $code->status == 'issued' ? 'success' : ($code->status == 'unused' ? 'warning' : 'secondary') }}">
                                            {{ ucfirst($code->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $code->distributor ? $code->distributor->name : 'Unassigned' }}
                                    </td>
                                    <td>
                                        {{ $code->usedByUser ? $code->usedByUser->name : 'Not Used' }}
                                    </td>
                                    <td class="text-center">
                                        @if($code->status == 'issued')
                                        <form method="POST" action="{{ route('admin.admin_codes.assign', $code) }}" class="d-inline">
                                            @csrf
                                            <select name="distributor_id" class="form-select form-select-sm" required>
                                                <option value="">Assign to Distributor</option>
                                                @foreach(\App\Models\User::where('is_admin', false)->get() as $user)
                                                <option value="{{ $user->id }}" {{ $code->distributor_id == $user->id ? 'selected' : '' }}>
                                                    {{ $user->name }} ({{ $user->email }})
                                                </option>
                                                @endforeach
                                            </select>
                                            <button type="submit" class="btn btn-xs btn-primary mt-1">Assign</button>
                                        </form>
                                        @endif
                                        <a href="{{ route('admin.admin_codes.show', $code) }}" class="btn btn-xs btn-info">View</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No admin codes found.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer px-0">
                        {{ $codes->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection