
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Withdrawals</h2>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Requested At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($withdrawals as $withdrawal)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $withdrawal->user->name ?? 'N/A' }}</td>
                            <td>{{ number_format($withdrawal->amount, 2) }}</td>
                            <td>
                                @if($withdrawal->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($withdrawal->status == 'approved')
                                    <span class="badge bg-success">Approved</span>
                                @elseif($withdrawal->status == 'denied')
                                    <span class="badge bg-danger">Denied</span>
                                @endif
                            </td>
                            <td>{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                @if($withdrawal->status == 'pending')
                                    <form action="{{ route('admin.withdrawals.approve', $withdrawal->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.withdrawals.deny', $withdrawal->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-danger">Deny</button>
                                    </form>
                                @else
                                    <span class="text-muted">No actions</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No withdrawals found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection