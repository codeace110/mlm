@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>My Network Tree</h6>
                        <div class="d-flex gap-2">
                            <select id="levelSelect" class="form-select form-select-sm" style="width: auto;">
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3" selected>Level 3</option>
                                <option value="4">Level 4</option>
                                <option value="5">Level 5</option>
                            </select>
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshNetwork()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Network Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 id="level1Count">{{ isset($networkStats['level1']) ? $networkStats['level1'] : 0 }}</h4>
                                    <p class="mb-0">Direct Referrals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 id="level2Count">{{ isset($networkStats['level2']) ? $networkStats['level2'] : 0 }}</h4>
                                    <p class="mb-0">Level 2</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 id="level3Count">{{ isset($networkStats['level3']) ? $networkStats['level3'] : 0 }}</h4>
                                    <p class="mb-0">Level 3</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 id="totalCount">{{ isset($networkStats['total']) ? $networkStats['total'] : 0 }}</h4>
                                    <p class="mb-0">Total Network</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Tree Visualization -->
                    <div class="network-tree-container">
                        <div id="networkTree" class="network-tree">
                            @if($networkTree && isset($networkTree['name']))
                                @include('partials.network-node', ['node' => $networkTree, 'level' => 0])
                            @else
                                <div class="text-center p-5">
                                    <p class="text-muted">No network data available</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.network-tree-container {
    overflow-x: auto;
    padding: 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    min-height: 600px;
}

.network-tree {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 900px;
    padding: 20px;
}

.tree-level {
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 40px 0;
    position: relative;
    width: 100%;
}

.tree-level:not(:last-child)::after {
    content: '';
    position: absolute;
    bottom: -40px;
    left: 50%;
    width: 2px;
    height: 40px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
    transform: translateX(-50%);
    border-radius: 1px;
}

.network-node {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px 25px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    text-align: center;
    min-width: 180px;
    margin: 0 15px;
    position: relative;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.network-node:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
    border-color: rgba(255, 255, 255, 0.3);
}

.network-node.root {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    font-weight: bold;
    min-width: 200px;
    padding: 25px 30px;
    box-shadow: 0 10px 30px rgba(240, 147, 251, 0.4);
}

.network-node.root:hover {
    box-shadow: 0 15px 40px rgba(240, 147, 251, 0.5);
}

.network-node::before {
    content: '';
    position: absolute;
    top: -12px;
    left: 50%;
    width: 0;
    height: 0;
    border-left: 12px solid transparent;
    border-right: 12px solid transparent;
    border-bottom: 12px solid #667eea;
    transform: translateX(-50%);
    border-radius: 2px;
}

.network-node.root::before {
    display: none;
}

.network-node .name {
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 8px;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.network-node .earnings {
    font-size: 14px;
    opacity: 0.95;
    margin-bottom: 5px;
    font-weight: 500;
}

.network-node .level {
    font-size: 12px;
    opacity: 0.8;
    margin-top: 8px;
    background: rgba(255, 255, 255, 0.1);
    padding: 2px 8px;
    border-radius: 10px;
    display: inline-block;
}

.network-node .volume-info {
    margin-top: 8px;
    font-size: 11px;
    opacity: 0.75;
}

.network-node .volume-info small {
    display: block;
    margin: 2px 0;
}

.connection-line {
    position: absolute;
    height: 40px;
    width: 3px;
    background: linear-gradient(to bottom, #667eea, #764ba2);
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border-radius: 2px;
}

.connection-lines {
    position: relative;
    height: 40px;
    width: 100%;
}

.connection-line.left-line,
.connection-line.right-line {
    height: 3px !important;
    width: 60% !important;
    top: 50% !important;
    transform: none !important;
}

.connection-line.left-line {
    left: 0;
}

.connection-line.right-line {
    right: 0;
}

/* Add some visual enhancements */
.network-node::after {
    content: '';
    position: absolute;
    bottom: -5px;
    left: 50%;
    width: 10px;
    height: 10px;
    background: inherit;
    transform: translateX(-50%) rotate(45deg);
    border-radius: 2px;
    opacity: 0.3;
}

.network-node.root::after {
    display: none;
}

/* Status indicators */
.network-node.active {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
    50% {
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.6);
    }
    100% {
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }
}

/* Responsive design */
@media (max-width: 768px) {
    .network-node {
        min-width: 140px;
        padding: 15px 20px;
        margin: 0 10px;
    }

    .network-node.root {
        min-width: 160px;
        padding: 20px 25px;
    }

    .tree-level {
        margin: 30px 0;
    }
}
</style>

<script>
let networkData = @json($networkTree);
let currentLevel = 3;

document.addEventListener('DOMContentLoaded', function() {
    renderNetworkTree();
    updateNetworkStats();

    // Level selector change event
    document.getElementById('levelSelect').addEventListener('change', function() {
        currentLevel = parseInt(this.value);
        renderNetworkTree();
    });
});

function renderNetworkTree() {
    const container = document.getElementById('networkTree');
    container.innerHTML = '';

    // Render tree level by level
    for (let level = 0; level <= currentLevel; level++) {
        const levelNodes = getNodesAtLevel(networkData, level);
        if (levelNodes.length > 0) {
            const levelDiv = document.createElement('div');
            levelDiv.className = 'tree-level';

            levelNodes.forEach(node => {
                const nodeDiv = createNetworkNode(node, level === 0);
                levelDiv.appendChild(nodeDiv);
            });

            container.appendChild(levelDiv);
        }
    }
}

function getNodesAtLevel(node, targetLevel, currentLevel = 0) {
    if (currentLevel === targetLevel) {
        return [node];
    }

    if (currentLevel > targetLevel || !node.children) {
        return [];
    }

    let nodes = [];
    node.children.forEach(child => {
        nodes = nodes.concat(getNodesAtLevel(child, targetLevel, currentLevel + 1));
    });

    return nodes;
}

function createNetworkNode(node, isRoot = false) {
    const nodeDiv = document.createElement('div');
    nodeDiv.className = `network-node ${isRoot ? 'root' : ''}`;

    nodeDiv.innerHTML = `
        <div class="name">${node.name}</div>
        <div class="earnings">₱${new Intl.NumberFormat().format(node.earnings || 0)}</div>
        <div class="level">Level ${node.level}</div>
    `;

    return nodeDiv;
}

function updateNetworkStats() {
    // Get stats from the server-side data if available
    @if(isset($networkStats))
        document.getElementById('level1Count').textContent = '{{ $networkStats["level1"] ?? 0 }}';
        document.getElementById('level2Count').textContent = '{{ $networkStats["level2"] ?? 0 }}';
        document.getElementById('level3Count').textContent = '{{ $networkStats["level3"] ?? 0 }}';
        document.getElementById('totalCount').textContent = '{{ $networkStats["total"] ?? 0 }}';
    @else
        // Fallback to client-side calculation
        const level1Count = networkData.children ? networkData.children.length : 0;
        let level2Count = 0;
        let level3Count = 0;

        if (networkData.children) {
            networkData.children.forEach(child => {
                if (child.children) {
                    level2Count += child.children.length;
                    child.children.forEach(grandchild => {
                        if (grandchild.children) {
                            level3Count += grandchild.children.length;
                        }
                    });
                }
            });
        }

        document.getElementById('level1Count').textContent = level1Count;
        document.getElementById('level2Count').textContent = level2Count;
        document.getElementById('level3Count').textContent = level3Count;
        document.getElementById('totalCount').textContent = level1Count + level2Count + level3Count;
    @endif
}

function refreshNetwork() {
    // Reload the page to refresh network data
    window.location.reload();
}

// Auto-refresh network stats every 5 minutes
setInterval(function() {
    // You could add AJAX call here to update network data
    console.log('Network auto-refresh would happen here');
}, 300000);
</script>
@endsection