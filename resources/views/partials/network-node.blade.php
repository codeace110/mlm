@php
    $hasLeft = isset($node['children'][0]) && $node['children'][0];
    $hasRight = isset($node['children'][1]) && $node['children'][1];
    $isRoot = $level === 0;
    $nodeEarnings = $node['earnings'] ?? 0;
    $nodeLevel = $node['level'] ?? 1;
    $leftVolume = $node['left_volume'] ?? 0;
    $rightVolume = $node['right_volume'] ?? 0;
    $totalVolume = $leftVolume + $rightVolume;
@endphp

<style>
.node-header {
    margin-bottom: 8px;
}

.profile-image {
    border: 2px solid rgba(255, 255, 255, 0.3);
    object-fit: cover;
}

.child-nodes {
    display: flex;
    justify-content: space-between;
    width: 100%;
    gap: 40px;
}

.left-child, .right-child {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: flex-start;
}

.total-volume {
    background: rgba(255, 255, 255, 0.1);
    padding: 1px 6px;
    border-radius: 8px;
    font-size: 9px !important;
    margin-top: 3px;
}
</style>

<div class="tree-level">
    <!-- Current Node -->
    <div class="network-node {{ $isRoot ? 'root' : '' }} {{ $nodeEarnings > 0 ? 'active' : '' }}">
        <div class="node-header">
            @if(isset($node['profile_image']) && $node['profile_image'])
                <img src="{{ $node['profile_image'] }}" alt="{{ $node['name'] }}" class="profile-image" style="width: 30px; height: 30px; border-radius: 50%; margin-bottom: 5px;">
            @endif
        </div>
        <div class="name" title="{{ $node['name'] }}">{{ Str::limit($node['name'], 15) }}</div>
        <div class="earnings" title="Total Earnings: ₱{{ number_format($nodeEarnings) }}">
            ₱{{ number_format($nodeEarnings) }}
        </div>
        <div class="level">Level {{ $nodeLevel }}</div>

        @if($totalVolume > 0)
            <div class="volume-info">
                <small title="Left Volume: {{ $leftVolume }}">L: {{ $leftVolume }}</small>
                <small title="Right Volume: {{ $rightVolume }}">R: {{ $rightVolume }}</small>
            </div>
        @endif

        @if(isset($node['total_left_volume']) && isset($node['total_right_volume']))
            <div class="total-volume" style="font-size: 10px; opacity: 0.6; margin-top: 3px;">
                Total: {{ $node['total_left_volume'] + $node['total_right_volume'] }}
            </div>
        @endif
    </div>

    @if($hasLeft || $hasRight)
        <!-- Connection Lines -->
        <div class="connection-lines">
            @if($hasLeft)
                <div class="connection-line left-line"></div>
            @endif
            @if($hasRight)
                <div class="connection-line right-line"></div>
            @endif
            <div class="connection-line down-line"></div>
        </div>

        <!-- Child Nodes Container -->
        <div class="child-nodes">
            <div class="left-child">
                @if($hasLeft)
                    @include('partials.network-node', ['node' => $node['children'][0], 'level' => $level + 1])
                @endif
            </div>
            <div class="right-child">
                @if($hasRight)
                    @include('partials.network-node', ['node' => $node['children'][1], 'level' => $level + 1])
                @endif
            </div>
        </div>
    @endif
</div>