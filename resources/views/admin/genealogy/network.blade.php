@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <h2 class="mb-4 fw-bold">Network Tree - {{ $user->name }}</h2>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Genealogy Network</h6>
                </div>
                <div class="card-body">
                    <div id="network-tree" class="text-center">
                        @include('admin.genealogy.network-tree', ['user' => $user, 'network' => $network])
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('admin.genealogy.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Genealogy
            </a>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add some basic styling for the tree
    const treeContainer = document.getElementById('network-tree');
    treeContainer.style.padding = '20px';
});
</script>
@endsection