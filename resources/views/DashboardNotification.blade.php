@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>Notifications</h6>
                    <p class="text-sm text-secondary mb-0">Track your network activity</p>
                </div>
                <div class="card-body px-4 pt-0 pb-2">
                    <ul class="list-group">
                        {{-- Hardcoded Notifications --}}
                        <li class="list-group-item d-flex align-items-center justify-content-between bg-light">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm me-3">
                                    <i class="fas fa-user-plus text-primary"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small">🏆 You successfully invited John Doe!</p>
                                    <span class="text-xs text-muted">5 minutes ago</span>
                                </div>
                            </div>
                            <span class="badge bg-primary">New</span>
                        </li>

                        <li class="list-group-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm me-3">
                                    <i class="fas fa-coins text-success"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small">💰 You received a bonus of ₱500 from your left downline!</p>
                                    <span class="text-xs text-muted">30 minutes ago</span>
                                </div>
                            </div>
                        </li>

                        <li class="list-group-item d-flex align-items-center justify-content-between bg-light">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm me-3">
                                    <i class="fas fa-hand-holding-usd text-warning"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small">💵 Your payout of ₱2,000 has been processed!</p>
                                    <span class="text-xs text-muted">1 hour ago</span>
                                </div>
                            </div>
                            <span class="badge bg-primary">New</span>
                        </li>

                        <li class="list-group-item d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <div class="icon icon-sm me-3">
                                    <i class="fas fa-gift text-danger"></i>
                                </div>
                                <div>
                                    <p class="mb-0 small">🎁 You received a gift from the system!</p>
                                    <span class="text-xs text-muted">2 hours ago</span>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Pagination Placeholder -->
                    <div class="mt-3">
                        <nav>
                            <ul class="pagination justify-content-center mb-0">
                                <li class="page-item disabled">
                                    <a class="page-link" href="#">Previous</a>
                                </li>
                                <li class="page-item active">
                                    <a class="page-link" href="#">1</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">2</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="#">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
