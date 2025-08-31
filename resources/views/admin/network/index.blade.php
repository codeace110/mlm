
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Network Viewer</h2>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Sponsor</th>
                        <th>Level</th>
                        <th>Joined At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($network as $member)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->sponsor->name ?? 'N/A' }}</td>
                            <td>{{ $member->level ?? 'N/A' }}</td>
                            <td>{{ $member->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No network members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection