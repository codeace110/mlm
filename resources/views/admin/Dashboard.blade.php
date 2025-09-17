
@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <h2 class="mb-4 fw-bold">Admin Dashboard</h2>
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                        <i class="bi bi-people fs-3"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Total Users</h6>
                        <h4 class="mb-0 fw-bold">{{ $totalUsers ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning text-white rounded-circle p-3 me-3">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                    <div>
                        <h6 class="card-title mb-1">Pending Withdrawals</h6>
                        <h4 class="mb-0 fw-bold">{{ $pendingWithdrawals ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white fw-bold">
                    Quick Links
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-primary me-2 mb-2">
                        <i class="bi bi-person-lines-fill me-1"></i> Manage Users
                    </a>
                    <a href="{{ route('admin.bonus_rules.index') }}" class="btn btn-info me-2 mb-2">
                        <i class="bi bi-trophy me-1"></i> Bonus Rules
                    </a>
                    <a href="{{ route('admin.earnings.index') }}" class="btn btn-primary me-2 mb-2">
                        <i class="bi bi-graph-up me-1"></i> Earnings
                    </a>
                    <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-warning me-2 mb-2">
                        <i class="bi bi-cash-stack me-1"></i> Withdrawals
                    </a>
                    <a href="{{ route('admin.genealogy.index') }}" class="btn btn-secondary me-2 mb-2">
                        <i class="bi bi-diagram-3 me-1"></i> Genealogy
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection