@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>My Binary Network Tree</h6>
                        <div class="d-flex gap-2">
                            <select id="levelSelect" class="form-select form-select-sm" style="width: auto;">
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                                <option value="5">Level 5</option>
                                <option value="10" selected>Full Tree (Scalable)</option>
                            </select>
                            <button class="btn btn-outline-primary btn-sm" onclick="refreshNetwork()">
                                <i class="fas fa-sync-alt me-1"></i>Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Color Legend -->
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h6 class="card-title mb-2">Node Color Legend</h6>
                                    <div class="row text-center">
                                        <div class="col-md-2">
                                            <div style="width: 20px; height: 20px; background: #2196F3; border-radius: 50%; display: inline-block;"></div>
                                            <div class="small mt-1">You (Root)</div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="width: 20px; height: 20px; background: #FFD700; border-radius: 50%; display: inline-block;"></div>
                                            <div class="small mt-1">Direct (No Carryover)</div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="width: 20px; height: 20px; background: #FF6B35; border-radius: 50%; display: inline-block;"></div>
                                            <div class="small mt-1">Direct (With Carryover)</div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="width: 20px; height: 20px; background: #4CAF50; border-radius: 50%; display: inline-block;"></div>
                                            <div class="small mt-1">Spillover (No Carryover)</div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="width: 20px; height: 20px; background: #8B4513; border-radius: 50%; display: inline-block;"></div>
                                            <div class="small mt-1">Spillover (With Carryover)</div>
                                        </div>
                                        <div class="col-md-2">
                                            <div style="width: 20px; height: 20px; background: #f0f0f0; border: 2px solid #ccc; border-radius: 50%; display: inline-block;"></div>
                                            <div class="small mt-1">Empty Slot</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Network Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h4 id="level1Count">{{ $networkTree['children'] ? count($networkTree['children']) : 0 }}</h4>
                                    <p class="mb-0">Direct Binary</p>
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
                                    <p class="mb-0">Total Binary Network</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Scalable Binary Tree Visualization with Avatars -->
                    <div class="text-center">
                        <canvas id="binary-tree-canvas" width="4000" height="2000" style="border: 1px solid #ddd; max-width: 100%; height: auto; background: #f8f9fa; cursor: grab; transition: transform 0.1s ease;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
let networkData = {!! json_encode($networkTree) !!};
let currentLevel = 10;
let scale = 1.0;
let isDragging = false;
let lastX = 0;
let lastY = 0;
let translateX = 0;
let translateY = 0;

document.addEventListener('DOMContentLoaded', function() {
    updateNetworkStats();
    drawBinaryTree();

    const canvas = document.getElementById('binary-tree-canvas');

    // Zoom with wheel
    canvas.addEventListener('wheel', function(e) {
        e.preventDefault();
        const zoomFactor = e.deltaY < 0 ? 1.1 : 0.9;
        scale = Math.min(Math.max(scale * zoomFactor, 0.3), 4);
        drawBinaryTree();
    });

    // Pan with drag
    canvas.addEventListener('mousedown', function(e) {
        isDragging = true;
        lastX = e.clientX - translateX;
        lastY = e.clientY - translateY;
        canvas.style.cursor = 'grabbing';
    });

    document.addEventListener('mousemove', function(e) {
        if (isDragging) {
            translateX = e.clientX - lastX;
            translateY = e.clientY - lastY;
            drawBinaryTree();
        }
    });

    document.addEventListener('mouseup', function() {
        isDragging = false;
        canvas.style.cursor = 'grab';
    });

    // Level selector change event
    document.getElementById('levelSelect').addEventListener('change', function() {
        currentLevel = parseInt(this.value);
        // Dynamically resize canvas for scalability
        const canvas = document.getElementById('binary-tree-canvas');
        const baseWidth = 4000;
        const baseHeight = 2000;
        const extraWidth = (currentLevel - 3) * 800;
        const extraHeight = (currentLevel - 3) * 200;
        canvas.width = baseWidth + extraWidth;
        canvas.height = baseHeight + extraHeight;
        drawBinaryTree();
    });
});

function updateNetworkStats() {
    const level1Count = networkData.children ? networkData.children.length : 0;
    let level2Count = 0;
    let level3Count = 0;
    // Calculate up to level 3 for stats, even if tree is deeper
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

function drawBinaryTree() {
    const canvas = document.getElementById('binary-tree-canvas');
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const centerX = width / 2;

    ctx.clearRect(0, 0, width, height);

    ctx.save();
    ctx.translate(translateX, translateY);
    ctx.scale(scale, scale);

    const nodeRadius = 25;
    const verticalSpacing = 120;
    const maxDepth = currentLevel;
    const sideMargin = 100;   // keep nodes away from the center wall

    ctx.font = '12px Arial';
    ctx.textAlign = 'center';

    // Draw center divider
    ctx.beginPath();
    ctx.moveTo(centerX, 0);
    ctx.lineTo(centerX, height);
    ctx.strokeStyle = '#ccc';
    ctx.lineWidth = 2;
    ctx.stroke();

    // Root node in the middle
    drawNode(networkData, centerX, 80, 0, maxDepth, ctx, nodeRadius, verticalSpacing, width, sideMargin, centerX);

    ctx.restore();
}
function drawNode(node, x, y, depth, maxDepth, ctx, nodeRadius, vSpacing, canvasWidth, margin, centerX) {
    if (depth > maxDepth) return;

    const hOffset = (canvasWidth / Math.pow(2, depth + 2));

    // --- Draw node ---
    if (node) {
        ctx.beginPath();
        ctx.arc(x, y, nodeRadius, 0, 2 * Math.PI);

        // Color based on depth and carryover status
        if (depth === 0) {
            ctx.fillStyle = '#2196F3'; // Blue for current user
        } else if (depth === 1) {
            // Direct referrals: check for carryover
            if ((node.carryover_left && node.carryover_left > 0) || (node.carryover_right && node.carryover_right > 0)) {
                ctx.fillStyle = '#FF6B35'; // Orange-red for direct with carryover
            } else {
                ctx.fillStyle = '#FFD700'; // Yellow for direct without carryover
            }
        } else {
            // Spillover nodes: check for carryover
            if ((node.carryover_left && node.carryover_left > 0) || (node.carryover_right && node.carryover_right > 0)) {
                ctx.fillStyle = '#8B4513'; // Brown for spillover with carryover
            } else {
                ctx.fillStyle = '#4CAF50'; // Green for spillover without carryover
            }
        }
        ctx.fill();
        ctx.strokeStyle = '#333';
        ctx.stroke();

        ctx.fillStyle = '#fff';
        ctx.font = 'bold 12px Arial';
        ctx.fillText(node.name.charAt(0).toUpperCase(), x, y + 4);

        ctx.fillStyle = '#333';
        ctx.font = '10px Arial';
        ctx.fillText(node.name, x, y + nodeRadius + 14);

        ctx.fillStyle = '#666';
        ctx.font = '9px Arial';
        ctx.fillText('L:' + node.left_volume + ' R:' + node.right_volume, x, y + nodeRadius + 28);

        // Display carryover information
        if (node.carryover_left || node.carryover_right) {
            ctx.fillStyle = '#999';
            ctx.font = '8px Arial';
            ctx.fillText('Carry: L:' + (node.carryover_left || 0) + ' R:' + (node.carryover_right || 0), x, y + nodeRadius + 40);
        }
    } else {
        // Empty placeholder node
        ctx.beginPath();
        ctx.arc(x, y, nodeRadius, 0, 2 * Math.PI);
        ctx.fillStyle = '#f0f0f0'; // Light gray background
        ctx.fill();
        ctx.strokeStyle = '#ccc'; // Gray border
        ctx.lineWidth = 2;
        ctx.stroke();

        // Draw "Empty" text
        ctx.fillStyle = '#999'; // Gray text
        ctx.font = 'bold 10px Arial';
        ctx.fillText('Empty', x, y + 4);

        return; // Don't draw children for empty nodes
    }

    // --- LEFT child ---
    let leftX = x - hOffset;
    let leftY = y + vSpacing;

    // Apply center margin restriction ONLY for direct referrals (depth == 0)
    if (depth === 0 && leftX + nodeRadius > centerX - margin) {
        leftX = centerX - margin - nodeRadius;
    }

    // Always draw left position, even if empty
    if (depth < maxDepth) {
        // Draw connecting line
        ctx.beginPath();
        ctx.moveTo(x, y + nodeRadius);
        ctx.lineTo(leftX, leftY - nodeRadius);
        ctx.strokeStyle = '#999';
        ctx.stroke();

        // Draw left child (or empty placeholder)
        const leftChild = (node.children && node.children[0]) ? node.children[0] : null;
        drawNode(leftChild, leftX, leftY, depth + 1, maxDepth,
                 ctx, nodeRadius, vSpacing, canvasWidth, margin, centerX);
    }

    // --- RIGHT child ---
    let rightX = x + hOffset;
    let rightY = y + vSpacing;

    // Apply center margin restriction ONLY for direct referrals (depth == 0)
    if (depth === 0 && rightX - nodeRadius < centerX + margin) {
        rightX = centerX + margin + nodeRadius;
    }

    // Always draw right position, even if empty
    if (depth < maxDepth) {
        // Draw connecting line
        ctx.beginPath();
        ctx.moveTo(x, y + nodeRadius);
        ctx.lineTo(rightX, rightY - nodeRadius);
        ctx.strokeStyle = '#999';
        ctx.stroke();

        // Draw right child (or empty placeholder)
        const rightChild = (node.children && node.children[1]) ? node.children[1] : null;
        drawNode(rightChild, rightX, rightY, depth + 1, maxDepth,
                 ctx, nodeRadius, vSpacing, canvasWidth, margin, centerX);
    }
}


function refreshNetwork() {
    window.location.reload();
}
</script>
