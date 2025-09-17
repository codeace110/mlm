
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Genealogy Viewer</h2>
        <a href="{{ route('admin.genealogy.index') }}" class="btn btn-primary">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
        </a>
    </div>

    <!-- Search Form -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.genealogy.search') }}" class="row g-3">
                <div class="col-md-8">
                    <input type="text" name="q" class="form-control"
                           placeholder="Search by name, email, or referral code..."
                           value="{{ $query ?? '' }}">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-outline-primary me-2">
                        <i class="bi bi-search me-1"></i>Search
                    </button>
                    @if(isset($query))
                        <a href="{{ route('admin.genealogy.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i>Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Sponsor</th>
                        <th>Level</th>
                        <th>Total Left Volume</th>
                        <th>Total Right Volume</th>
                        <th>Left Consumed</th>
                        <th>Right Consumed</th>
                        <th>Effective Left</th>
                        <th>Effective Right</th>
                        <th>Level</th>
                        <th>Joined At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse(isset($users) ? $users : $genealogy as $member)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $member->profile_image ? asset($member->profile_image) : asset('assets/img/team-1.jpg') }}"
                                         class="avatar avatar-sm me-3" alt="Profile">
                                    <div>
                                        <div class="fw-bold">{{ $member->name }}</div>
                                        <small class="text-muted">{{ $member->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $member->sponsor->name ?? 'N/A' }}</td>
                            <td>{{ $member->level ?? 'N/A' }}</td>
                            <td>{{ $member->total_left_volume ?? 0 }}</td>
                            <td>{{ $member->total_right_volume ?? 0 }}</td>
                            <td>{{ $member->left_consumed ?? 0 }}</td>
                            <td>{{ $member->right_consumed ?? 0 }}</td>
                            <td>{{ $member->effective_left ?? 0 }}</td>
                            <td>{{ $member->effective_right ?? 0 }}</td>
                            <td>{{ $member->level_index ?? 1 }}</td>
                            <td>{{ $member->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.genealogy.network', $member->id) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-diagram-3 me-1"></i>View Network
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="text-center">No genealogy data found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection