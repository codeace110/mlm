@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <h6>Available Packages</h6>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="row">
                        @forelse($packages as $package)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-header p-0 mx-3 mt-3 position-relative z-index-1">
                                    <a href="{{ route('packages.show', $package) }}" class="d-block">
                                        <img src="{{ $package->image ? asset($package->image) : asset('assets/img/product-placeholder.jpg') }}"
                                             class="img-fluid border-radius-lg" alt="{{ $package->name }}">
                                    </a>
                                </div>
                                <div class="card-body pt-2">
                                    <a href="{{ route('packages.show', $package) }}" class="text-decoration-none">
                                        <h5 class="font-weight-bold">{{ $package->name }}</h5>
                                    </a>
                                    <p class="text-sm text-muted mb-2">
                                        {{ Str::limit($package->description, 100) }}
                                    </p>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h4 class="font-weight-bold text-primary mb-0">
                                            ₱{{ number_format($package->price, 2) }}
                                        </h4>
                                        <a href="{{ route('packages.show', $package) }}"
                                           class="btn btn-primary btn-sm">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12">
                            <div class="text-center py-5">
                                <p class="text-muted mb-0">No packages available at the moment.</p>
                            </div>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection