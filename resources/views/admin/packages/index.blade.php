
@extends('layouts.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4 fw-bold">Packages</h2>
    <div class="mb-3">
        <a href="{{ route('admin.packages.create') }}" class="btn btn-primary">Create New Package</a>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Package Name</th>
                        <th>Description</th>
                        <th>Price</th>
                        <th>Features</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packages as $package)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($package->image)
                                    <img src="{{ asset($package->image) }}" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;" alt="Package Image">
                                @else
                                    <span class="text-muted">No image</span>
                                @endif
                            </td>
                            <td>{{ $package->name }}</td>
                            <td>{{ Str::limit($package->description, 50) }}</td>
                            <td>₱{{ number_format($package->price, 2) }}</td>
                            <td>
                                @if(is_array($package->features) && count($package->features) > 0)
                                    <ul class="list-unstyled mb-0">
                                        @foreach(array_slice($package->features, 0, 2) as $feature)
                                            <li><small>• {{ Str::limit($feature, 20) }}</small></li>
                                        @endforeach
                                        @if(count($package->features) > 2)
                                            <li><small class="text-muted">+{{ count($package->features) - 2 }} more</small></li>
                                        @endif
                                    </ul>
                                @else
                                    <span class="text-muted">No features</span>
                                @endif
                            </td>
                            <td>
                                @if($package->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.packages.edit', $package->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="bi bi-pencil"></i> Edit
                                    </a>
                                    <form action="{{ route('admin.packages.destroy', $package->id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this package?')">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">No packages found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection