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
    overflow-y: auto;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.binary-tree {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 1200px;
    min-height: 600px;
    position: relative;
}

.tree-level {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    margin: 40px 0;
}

.tree-level:first-child {
    margin-top: 0;
}

.tree-connection {
    position: absolute;
    width: 2px;
    background-color: #6c757d;
    z-index: 1;
}

.tree-connection.vertical {
    height: 40px;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
}

.tree-connection.horizontal {
    height: 2px;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
}

.network-node {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    text-align: center;
    min-width: 160px;
    max-width: 200px;
    position: relative;
    z-index: 2;
    border: 2px solid #fff;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.network-node:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.network-node.root {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    font-weight: bold;
    border: 3px solid #fff;
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.network-node.left-child {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.network-node.right-child {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.network-node.empty {
    background: #e9ecef;
    color: #6c757d;
    border: 2px dashed #dee2e6;
    box-shadow: none;
}

.network-node.empty:hover {
    transform: none;
    box-shadow: none;
}

.network-node .name {
    font-size: 14px;
    font-weight: bold;
    margin-bottom: 8px;
    word-break: break-word;
}

.network-node .earnings {
    font-size: 12px;
    opacity: 0.9;
    margin-bottom: 5px;
}

.network-node .level {
    font-size: 10px;
    opacity: 0.8;
    background: rgba(255, 255, 255, 0.2);
    padding: 2px 6px;
    border-radius: 8px;
    display: inline-block;
}

.network-node .side-indicator {
    position: absolute;
    top: -8px;
    left: 50%;
    transform: translateX(-50%);
    background: #fff;
    color: #495057;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: bold;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.network-node.left-child .side-indicator {
    background: #4facfe;
    color: white;
}

.network-node.right-child .side-indicator {
    background: #43e97b;
    color: white;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .network-tree-container {
        padding: 10px;
    }

    .binary-tree {
        min-width: 100%;
    }

    .network-node {
        min-width: 120px;
        padding: 10px 15px;
    }

    .network-node .name {
        font-size: 12px;
    }
}
</style>

<script>
let networkData = @json($networkTree);

document.addEventListener("DOMContentLoaded", function () {
    renderBinaryTree();
});

function renderBinaryTree() {
    const container = document.getElementById('network-tree');
    container.innerHTML = '';

    const treeDiv = document.createElement('div');
    treeDiv.className = 'binary-tree';

    // Render root level
    const rootLevel = document.createElement('div');
    rootLevel.className = 'tree-level';

    if (networkData) {
        const rootNode = createNetworkNode(networkData, true, null);
        rootLevel.appendChild(rootNode);
    } else {
        // Empty tree
        const emptyNode = createEmptyNode();
        rootLevel.appendChild(emptyNode);
    }

    treeDiv.appendChild(rootLevel);

    // Render binary tree levels
    if (networkData && networkData.children) {
        renderBinaryLevel(networkData.children, treeDiv, 1);
    }

    container.appendChild(treeDiv);
}

function renderBinaryLevel(nodes, container, level) {
    if (level > 4 || !nodes || nodes.length === 0) {
        return;
    }

    const levelDiv = document.createElement('div');
    levelDiv.className = 'tree-level';

    // Add vertical connection line from parent
    const verticalLine = document.createElement('div');
    verticalLine.className = 'tree-connection vertical';
    levelDiv.appendChild(verticalLine);

    // Create left and right positions
    const leftPosition = document.createElement('div');
    leftPosition.className = 'tree-position left';

    const rightPosition = document.createElement('div');
    rightPosition.className = 'tree-position right';

    // Find left and right children
    const leftChild = nodes.find(node => node.placement_side === 'left');
    const rightChild = nodes.find(node => node.placement_side === 'right');

    // Add horizontal connection line
    const horizontalLine = document.createElement('div');
    horizontalLine.className = 'tree-connection horizontal';
    levelDiv.appendChild(horizontalLine);

    // Add left child
    if (leftChild) {
        const leftNode = createNetworkNode(leftChild, false, 'left');
        leftPosition.appendChild(leftNode);
    } else {
        const emptyNode = createEmptyNode('left');
        leftPosition.appendChild(emptyNode);
    }

    // Add right child
    if (rightChild) {
        const rightNode = createNetworkNode(rightChild, false, 'right');
        rightPosition.appendChild(rightNode);
    } else {
        const emptyNode = createEmptyNode('right');
        rightPosition.appendChild(emptyNode);
    }

    levelDiv.appendChild(leftPosition);
    levelDiv.appendChild(rightPosition);

    container.appendChild(levelDiv);

    // Render next level for children
    const nextLevelNodes = [];
    if (leftChild && leftChild.children) {
        nextLevelNodes.push(...leftChild.children);
    }
    if (rightChild && rightChild.children) {
        nextLevelNodes.push(...rightChild.children);
    }

    if (nextLevelNodes.length > 0) {
        renderBinaryLevel(nextLevelNodes, container, level + 1);
    }
}

function createNetworkNode(node, isRoot = false, side = null) {
    const nodeDiv = document.createElement('div');
    let className = `network-node ${isRoot ? 'root' : ''}`;

    if (side === 'left') {
        className += ' left-child';
    } else if (side === 'right') {
        className += ' right-child';
    }

    nodeDiv.className = className;

    const sideIndicator = side ? `<div class="side-indicator">${side.charAt(0).toUpperCase() + side.slice(1)}</div>` : '';

    nodeDiv.innerHTML = `
        ${sideIndicator}
        <div class="name">${node.name || 'Unknown'}</div>
        <div class="earnings">₱${new Intl.NumberFormat().format(node.earnings || 0)}</div>
        <div class="level">Level ${node.level || 1}</div>
    `;

    return nodeDiv;
}

function createEmptyNode(side = null) {
    const nodeDiv = document.createElement('div');
    let className = 'network-node empty';

    if (side === 'left') {
        className += ' left-child';
    } else if (side === 'right') {
        className += ' right-child';
    }

    nodeDiv.className = className;

    const sideIndicator = side ? `<div class="side-indicator">${side.charAt(0).toUpperCase() + side.slice(1)}</div>` : '';

    nodeDiv.innerHTML = `
        ${sideIndicator}
        <div class="name">Empty Position</div>
        <div class="earnings">₱0</div>
        <div class="level">Available</div>
    `;

    return nodeDiv;
}
</script>


@endsection
