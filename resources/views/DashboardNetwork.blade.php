@extends('layouts.dashboard')

@section('content')

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6>My Binary Network</h6>
                    <p class="text-sm text-secondary mb-0">Top Earner → Downline Tree (3 Levels)</p>
                </div>
                <div class="card-body px-4 pt-0 pb-2">
                    <div id="network-tree" class="network-tree-container">
                        <!-- Network tree will be rendered here -->
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
</style>

<script>
let networkData = @json($networkTree);

document.addEventListener("DOMContentLoaded", function () {
    renderNetworkTree();
});

function renderNetworkTree() {
    const container = document.getElementById('network-tree');
    container.innerHTML = '';

    // Create root level
    const rootDiv = document.createElement('div');
    rootDiv.className = 'tree-level';

    const rootNode = createNetworkNode(networkData, true);
    rootDiv.appendChild(rootNode);

    container.appendChild(rootDiv);

    // Render children levels
    renderChildren(networkData, container, 1);
}

function renderChildren(node, container, level) {
    if (level > 3 || !node.children || node.children.length === 0) {
        return;
    }

    const levelDiv = document.createElement('div');
    levelDiv.className = 'tree-level';

    node.children.forEach(child => {
        const childNode = createNetworkNode(child, false);
        levelDiv.appendChild(childNode);
    });

    container.appendChild(levelDiv);

    // Render next level for each child
    node.children.forEach(child => {
        renderChildren(child, container, level + 1);
    });
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
</script>


@endsection
