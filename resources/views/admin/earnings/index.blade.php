
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Earnings Report</h2>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($earnings as $earning)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $earning->user->name ?? 'N/A' }}</td>
                            <td>{{ number_format($earning->amount, 2) }}</td>
                            <td>{{ $earning->type }}</td>
                            <td>{{ $earning->created_at->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No earnings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection