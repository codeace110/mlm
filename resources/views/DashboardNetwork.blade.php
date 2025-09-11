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

// function drawBinaryTree() {
//     const canvas = document.getElementById('binary-tree-canvas');
//     const ctx = canvas.getContext('2d');
//     const width = canvas.width;
//     const height = canvas.height;
//     const centerX = width / 2;

//     ctx.clearRect(0, 0, width, height);

//     // Draw central vertical line as a thick wall to separate left and right
//     ctx.save();
//     ctx.beginPath();
//     ctx.moveTo(centerX, 0);
//     ctx.lineTo(centerX, height);
//     ctx.strokeStyle = '#ccc';
//     ctx.lineWidth = 3;
//     ctx.stroke();
//     ctx.restore();

//     // Apply transform for zoom and pan
//     ctx.save();
//     ctx.translate(translateX, translateY);
//     ctx.scale(scale, scale);

//     const nodeRadius = 40;
//     const baseHorizontalOffset = 120; // Shorter horizontal distance
//     const verticalSpacing = 60; // Shorter vertical distance
//     const maxDepth = currentLevel;

//     ctx.font = '12px Arial';
//     ctx.textAlign = 'center';

//     // Draw tree starting from root
//     drawNode(networkData, centerX, 80, 0, maxDepth, ctx, nodeRadius, baseHorizontalOffset, verticalSpacing);

//     ctx.restore();
// }

// function drawNode(node, x, y, depth, maxDepth, ctx, nodeRadius, baseHOffset, vSpacing) {
//     if (!node || depth > maxDepth) return;

//     // Minimal fanning to keep close - pow(1.2, depth) for slight separation, no overlap
//     const hOffset = baseHOffset * Math.pow(1.2, depth);

//     // Draw avatar circle
//     ctx.beginPath();
//     ctx.arc(x, y, nodeRadius, 0, 2 * Math.PI);

//     if (node.profile_image) {
//         ctx.fillStyle = '#e0e0e0';
//         ctx.fill();
//         ctx.fillStyle = '#333';
//         ctx.fillText(node.name.charAt(0).toUpperCase(), x, y + 5);
//     } else {
//         const balance = Math.abs(parseFloat(node.left_volume) - parseFloat(node.right_volume));
//         ctx.fillStyle = balance < 100 ? '#4CAF50' : '#f44336';
//         ctx.fill();
//         ctx.fillStyle = '#fff';
//         ctx.font = 'bold 16px Arial';
//         ctx.fillText(node.name.charAt(0).toUpperCase(), x, y + 6);
//     }

//     ctx.strokeStyle = '#ddd';
//     ctx.lineWidth = 2;
//     ctx.stroke();

//     // Draw name below avatar
//     ctx.fillStyle = '#333';
//     ctx.font = 'bold 12px Arial';
//     ctx.fillText(node.name, x, y + nodeRadius + 20);

//     // Draw volumes
//     ctx.fillStyle = '#666';
//     ctx.font = '10px Arial';
//     ctx.fillText('L: ' + node.left_volume + ' R: ' + node.right_volume, x, y + nodeRadius + 40);

//     // Draw left wing (child[0] is left) - stays strictly left of center
//     if (node.children && node.children[0] && depth < maxDepth) {
//         const leftX = x - hOffset;
//         const leftY = y + vSpacing;
//         // Short line to left child
//         ctx.beginPath();
//         ctx.moveTo(x - 10, y + nodeRadius); // Start slightly left from center
//         ctx.lineTo(leftX + 10, leftY - nodeRadius); // End slightly right on child
//         ctx.strokeStyle = '#999';
//         ctx.lineWidth = 2;
//         ctx.stroke();
//         // Label "Left" on line
//         const midXLeft = (x - 10 + leftX + 10) / 2;
//         const midYLeft = (y + nodeRadius + leftY - nodeRadius) / 2;
//         ctx.fillStyle = '#999';
//         ctx.font = 'italic 10px Arial';
//         ctx.fillText('Left', midXLeft - 15, midYLeft);
//         drawNode(node.children[0], leftX, leftY, depth + 1, maxDepth, ctx, nodeRadius, baseHOffset, vSpacing);
//     }

//     // Draw right wing (child[1] is right) - stays strictly right of center
//     if (node.children && node.children[1] && depth < maxDepth) {
//         const rightX = x + hOffset;
//         const rightY = y + vSpacing;
//         // Short line to right child
//         ctx.beginPath();
//         ctx.moveTo(x + 10, y + nodeRadius); // Start slightly right from center
//         ctx.lineTo(rightX - 10, rightY - nodeRadius); // End slightly left on child
//         ctx.strokeStyle = '#999';
//         ctx.lineWidth = 2;
//         ctx.stroke();
//         // Label "Right" on line
//         const midXRight = (x + 10 + rightX - 10) / 2;
//         const midYRight = (y + nodeRadius + rightY - nodeRadius) / 2;
//         ctx.fillStyle = '#999';
//         ctx.font = 'italic 10px Arial';
//         ctx.fillText('Right', midXRight - 20, midYRight);
//         drawNode(node.children[1], rightX, rightY, depth + 1, maxDepth, ctx, nodeRadius, baseHOffset, vSpacing);
//     }
// }

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

        // Color based on depth: 0 (user) - blue, 1 (direct) - yellow, >1 (spillover) - green
        if (depth === 0) {
            ctx.fillStyle = '#2196F3'; // Blue for current user
        } else if (depth === 1) {
            ctx.fillStyle = '#FFD700'; // Yellow for direct referrals
        } else {
            ctx.fillStyle = '#4CAF50'; // Green for spillover
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
    } else {
        // placeholder slot
        ctx.beginPath();
        ctx.arc(x, y, nodeRadius, 0, 2 * Math.PI);
        ctx.fillStyle = '#ccc';
        ctx.fill();
        ctx.strokeStyle = '#999';
        ctx.stroke();
        return;
    }

    // --- LEFT child ---
    let leftX = x - hOffset;
    let leftY = y + vSpacing;

    // Apply center margin restriction ONLY for direct referrals (depth == 0)
    if (depth === 0 && leftX + nodeRadius > centerX - margin) {
        leftX = centerX - margin - nodeRadius;
    }

    if (node.children && node.children[0]) {
        ctx.beginPath();
        ctx.moveTo(x, y + nodeRadius);
        ctx.lineTo(leftX, leftY - nodeRadius);
        ctx.strokeStyle = '#999';
        ctx.stroke();
        drawNode(node.children[0], leftX, leftY, depth + 1, maxDepth,
                 ctx, nodeRadius, vSpacing, canvasWidth, margin, centerX);
    }

    // --- RIGHT child ---
    let rightX = x + hOffset;
    let rightY = y + vSpacing;

    // Apply center margin restriction ONLY for direct referrals (depth == 0)
    if (depth === 0 && rightX - nodeRadius < centerX + margin) {
        rightX = centerX + margin + nodeRadius;
    }

    if (node.children && node.children[1]) {
        ctx.beginPath();
        ctx.moveTo(x, y + nodeRadius);
        ctx.lineTo(rightX, rightY - nodeRadius);
        ctx.strokeStyle = '#999';
        ctx.stroke();
        drawNode(node.children[1], rightX, rightY, depth + 1, maxDepth,
                 ctx, nodeRadius, vSpacing, canvasWidth, margin, centerX);
    }
}


function refreshNetwork() {
    window.location.reload();
}
</script>
@endsection
