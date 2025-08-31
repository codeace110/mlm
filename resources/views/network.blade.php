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
                                    <h4 id="level1Count">{{ $networkTree['children'] ? count($networkTree['children']) : 0 }}</h4>
                                    <p class="mb-0">Direct Referrals</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body text-center">
                                    <h4 id="level2Count">0</h4>
                                    <p class="mb-0">Level 2</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body text-center">
                                    <h4 id="level3Count">0</h4>
                                    <p class="mb-0">Level 3</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body text-center">
                                    <h4 id="totalCount">0</h4>
                                    <p class="mb-0">Total Network</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Tree Visualization -->
                    <div class="network-tree-container">
                        <div id="networkTree" class="network-tree">
                            <!-- Tree will be rendered here -->
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
}

.network-tree {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 800px;
}

.tree-level {
    display: flex;
    justify-content: center;
    margin: 20px 0;
    position: relative;
}

.tree-level:not(:last-child)::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 50%;
    width: 2px;
    height: 20px;
    background-color: #dee2e6;
    transform: translateX(-50%);
}

.network-node {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
    min-width: 150px;
    margin: 0 10px;
    position: relative;
}

.network-node.root {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    font-weight: bold;
}

.network-node::before {
    content: '';
    position: absolute;
    top: -10px;
    left: 50%;
    width: 0;
    height: 0;
    border-left: 10px solid transparent;
    border-right: 10px solid transparent;
    border-bottom: 10px solid rgba(102, 126, 234, 0.8);
    transform: translateX(-50%);
}

.network-node.root::before {
    display: none;
}

.network-node .name {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 5px;
}

.network-node .earnings {
    font-size: 12px;
    opacity: 0.9;
}

.network-node .level {
    font-size: 10px;
    opacity: 0.7;
    margin-top: 5px;
}

.connection-line {
    position: absolute;
    height: 20px;
    width: 2px;
    background-color: #dee2e6;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
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