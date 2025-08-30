@extends('layouts.admin')

@section('title', 'Admin Dashboard')

@section('content')
<div class="container-fluid py-4">

    {{-- Dashboard Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 fw-bold">Admin Dashboard</h1>
        <span class="text-muted">Welcome, {{ Auth::user()->name }}</span>
    </div>

    {{-- Quick Stats --}}
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center">
                    <h5 class="fw-bold">Users</h5>
                    <h2>{{ $userCount ?? 0 }}</h2>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Manage</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center">
                    <h5 class="fw-bold">Packages</h5>
                    <h2>{{ $packageCount ?? 0 }}</h2>
                    <a href="{{ route('admin.packages.index') }}" class="btn btn-sm btn-outline-primary">View</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center">
                    <h5 class="fw-bold">Earnings</h5>
                    <h2>${{ number_format($earningsTotal ?? 0, 2) }}</h2>
                    <a href="{{ route('admin.earnings.index') }}" class="btn btn-sm btn-outline-primary">Reports</a>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body text-center">
                    <h5 class="fw-bold">Withdrawals</h5>
                    <h2>{{ $pendingWithdrawals ?? 0 }}</h2>
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm btn-outline-primary">Review</a>
                </div>
            </div>
        </div>
    </div>

    {{-- Management Sections --}}
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header fw-bold">User Management</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.users.index') }}" class="list-group-item">All Users</a>
                    <a href="{{ route('admin.users.pending') }}" class="list-group-item">Approve/Deny Registrations</a>
                    <a href="{{ route('admin.genealogy.index') }}" class="list-group-item">View Genealogy (Tree)</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header fw-bold">Package Management</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.packages.index') }}" class="list-group-item">Manage Packages</a>
                    <a href="{{ route('admin.bonus_rules.index') }}" class="list-group-item">Bonus Rules</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-header fw-bold">Earnings Reports</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.earnings.index') }}" class="list-group-item">All Earnings</a>
                    <a href="{{ route('admin.earnings.byUser') }}" class="list-group-item">By User</a>
                    <a href="{{ route('admin.earnings.byType') }}" class="list-group-item">By Type</a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-4 mt-4">
                <div class="card-header fw-bold">Withdrawal Management</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('admin.withdrawals.index') }}" class="list-group-item">Pending Requests</a>
                    <a href="{{ route('admin.withdrawals.history') }}" class="list-group-item">History</a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
