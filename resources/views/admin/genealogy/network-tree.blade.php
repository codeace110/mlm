@if($network)
<div class="network-node mb-4">
    <div class="user-card bg-primary text-white p-3 rounded shadow-sm">
        <div class="d-flex align-items-center">
            <img src="{{ $network['user']->profile_image ? asset($network['user']->profile_image) : asset('assets/img/team-1.jpg') }}"
                 class="avatar avatar-sm me-3" alt="Profile">
            <div>
                <h6 class="mb-0">{{ $network['user']->name }}</h6>
                <small>Level {{ $network['level'] }}</small>
            </div>
        </div>
    </div>

    @if(count($network['children']) > 0)
    <div class="children-container d-flex justify-content-center mt-4">
        <div class="connector-line"></div>
        <div class="children-row d-flex flex-wrap justify-content-center gap-4">
            @foreach($network['children'] as $child)
                <div class="child-node">
                    @include('admin.genealogy.network-tree', ['user' => $user, 'network' => $child])
                </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endif

<style>
.network-node {
    position: relative;
}

.connector-line {
    position: absolute;
    top: -20px;
    left: 50%;
    width: 2px;
    height: 20px;
    background-color: #6c757d;
    transform: translateX(-50%);
}

.children-row {
    position: relative;
}

.children-row::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 0;
    right: 0;
    height: 2px;
    background-color: #6c757d;
}

.child-node {
    position: relative;
}

.child-node::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 50%;
    width: 2px;
    height: 10px;
    background-color: #6c757d;
    transform: translateX(-50%);
}

.user-card {
    min-width: 200px;
    transition: transform 0.2s;
}

.user-card:hover {
    transform: scale(1.05);
}
</style>