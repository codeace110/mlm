@extends('layouts.dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>My Binary Network Tree</h6>
                        <div class="d-flex gap-2 flex-wrap">
                            <select id="levelSelect" class="form-select form-select-sm" style="width: auto;">
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                                <option value="5">Level 5</option>
                                <option value="10" selected>Full Tree (Scalable)</option>
                            </select>
                            <div class="btn-group" role="group">
                                <button class="btn btn-outline-secondary btn-sm" id="zoomIn" title="Zoom In">
                                    <i class="fas fa-plus"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="zoomOut" title="Zoom Out">
                                    <i class="fas fa-minus"></i>
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" id="resetView" title="Reset View">
                                    <i class="fas fa-home"></i>
                                </button>
                            </div>
                            <button class="btn btn-outline-success btn-sm" id="exportTree" title="Export as PNG">
                                <i class="fas fa-download me-1"></i>Export
                            </button>
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
                        <canvas id="binary-tree-canvas" width="1200" height="800" style="border: 1px solid #ddd; max-width: 100%; height: auto; background: #f8f9fa; cursor: grab; transition: transform 0.1s ease;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Details Modal -->
    <div class="modal fade" id="userDetailsModal" tabindex="-1" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="userDetailsModalLabel">User Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div id="userAvatar" class="mb-3">
                                <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 2rem;">
                                    <span id="userInitial"></span>
                                </div>
                            </div>
                            <h5 id="userName"></h5>
                            <p class="text-muted mb-1" id="userEmail"></p>
                            <span class="badge" id="userStatus">Active</span>
                        </div>
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Left Volume</h6>
                                            <h4 class="text-primary" id="leftVolume">0</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Right Volume</h6>
                                            <h4 class="text-success" id="rightVolume">0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Carryover Left</h6>
                                            <h4 class="text-warning" id="carryoverLeft">0</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <h6 class="card-title">Carryover Right</h6>
                                            <h4 class="text-info" id="carryoverRight">0</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <p><strong>Joined:</strong> <span id="joinDate"></span></p>
                                <p><strong>Last Active:</strong> <span id="lastActive"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

<script>
let networkData = {!! json_encode($networkTree) !!};
console.log('Network Tree Data:', networkData);
let currentLevel = 10;
let scale = 1.0;
let isDragging = false;
let lastX = 0;
let lastY = 0;
let translateX = 0;
let translateY = 0;
let isTransitioning = false;
let clickedNode = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing network view');
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

    // Touch support for mobile
    let touchStartX = 0;
    let touchStartY = 0;
    canvas.addEventListener('touchstart', function(e) {
        if (e.touches.length === 1) {
            const touch = e.touches[0];
            touchStartX = touch.clientX - translateX;
            touchStartY = touch.clientY - translateY;
            isDragging = true;
            canvas.style.cursor = 'grabbing';
        }
    });

    canvas.addEventListener('touchmove', function(e) {
        if (e.touches.length === 1 && isDragging) {
            e.preventDefault();
            const touch = e.touches[0];
            translateX = touch.clientX - touchStartX;
            translateY = touch.clientY - touchStartY;
            drawBinaryTree();
        }
    });

    canvas.addEventListener('touchend', function() {
        isDragging = false;
        canvas.style.cursor = 'grab';
    });

    // Zoom buttons
    document.getElementById('zoomIn').addEventListener('click', function() {
        scale = Math.min(scale * 1.2, 4);
        drawBinaryTree();
    });

    document.getElementById('zoomOut').addEventListener('click', function() {
        scale = Math.max(scale * 0.8, 0.3);
        drawBinaryTree();
    });

    document.getElementById('resetView').addEventListener('click', function() {
        scale = 1.0;
        translateX = 0;
        translateY = 0;
        drawBinaryTree();
    });

    // Export functionality
    document.getElementById('exportTree').addEventListener('click', function() {
        const canvas = document.getElementById('binary-tree-canvas');
        const link = document.createElement('a');
        link.download = 'network-tree-' + new Date().toISOString().split('T')[0] + '.png';
        link.href = canvas.toDataURL();
        link.click();
    });

    // Level selector change event with smooth transition
    document.getElementById('levelSelect').addEventListener('change', function() {
        if (isTransitioning) return;

        isTransitioning = true;
        const newLevel = parseInt(this.value);

        // Smooth transition effect
        canvas.style.opacity = '0.7';
        setTimeout(() => {
            currentLevel = newLevel;
            // Dynamically resize canvas for scalability
            const baseWidth = 1200;
            const baseHeight = 800;
            const extraWidth = (currentLevel - 3) * 300;
            const extraHeight = (currentLevel - 3) * 150;
            canvas.width = Math.min(baseWidth + extraWidth, 2000);
            canvas.height = Math.min(baseHeight + extraHeight, 1200);

            // Reset view for new level
            scale = 1.0;
            translateX = 0;
            translateY = 0;

            drawBinaryTree();
            canvas.style.opacity = '1';
            isTransitioning = false;
        }, 300);
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
    console.log('Drawing binary tree');
    const canvas = document.getElementById('binary-tree-canvas');
    if (!canvas) {
        console.error('Canvas not found');
        return;
    }
    const ctx = canvas.getContext('2d');
    const width = canvas.width;
    const height = canvas.height;
    const centerX = width / 2;

    console.log('Canvas dimensions:', width, height);
    ctx.clearRect(0, 0, width, height);

    // Reset clickable nodes
    window.clickableNodes = [];

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

    // Add click event listener to canvas
    canvas.onclick = function(e) {
        if (isDragging) return; // Don't handle clicks during drag

        const rect = canvas.getBoundingClientRect();
        const clickX = (e.clientX - rect.left - translateX) / scale;
        const clickY = (e.clientY - rect.top - translateY) / scale;

        // Check if click is on a node
        for (const nodeData of window.clickableNodes) {
            const distance = Math.sqrt(
                Math.pow(clickX - nodeData.x, 2) + Math.pow(clickY - nodeData.y, 2)
            );

            if (distance <= nodeData.radius && nodeData.node) {
                showUserDetails(nodeData.node);
                break;
            }
        }
    };
}
function drawNode(node, x, y, depth, maxDepth, ctx, nodeRadius, vSpacing, canvasWidth, margin, centerX) {
    if (depth > maxDepth) return;

    const hOffset = Math.max(80, canvasWidth / Math.pow(2, depth + 2));

    // Store node data for click detection
    const nodeData = {
        node: node,
        x: x,
        y: y,
        radius: nodeRadius,
        depth: depth
    };

    // Add to clickable nodes array
    if (!window.clickableNodes) window.clickableNodes = [];
    window.clickableNodes.push(nodeData);

    // --- Draw node ---
    if (node) {
        ctx.beginPath();
        ctx.arc(x, y, nodeRadius, 0, 2 * Math.PI);

        // Color based on depth and carryover status
        if (depth === 0) {
            ctx.fillStyle = '#2196F3'; // Blue for current user
        } else if (depth === 1) {
            // Direct referrals: check for carryover
            if ((node.left_spillover && node.left_spillover > 0) || (node.right_spillover && node.right_spillover > 0)) {
                ctx.fillStyle = '#FF6B35'; // Orange-red for direct with carryover
            } else {
                ctx.fillStyle = '#FFD700'; // Yellow for direct without carryover
            }
        } else {
            // Spillover nodes: check for carryover
            if ((node.left_spillover && node.left_spillover > 0) || (node.right_spillover && node.right_spillover > 0)) {
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
        if (node.left_spillover || node.right_spillover) {
            ctx.fillStyle = '#999';
            ctx.font = '8px Arial';
            ctx.fillText('Carry: L:' + (node.left_spillover || 0) + ' R:' + (node.right_spillover || 0), x, y + nodeRadius + 40);
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


function showUserDetails(node) {
    // Populate modal with user data
    document.getElementById('userInitial').textContent = node.name.charAt(0).toUpperCase();
    document.getElementById('userName').textContent = node.name;
    document.getElementById('userEmail').textContent = node.email || 'N/A';
    document.getElementById('leftVolume').textContent = node.left_volume || 0;
    document.getElementById('rightVolume').textContent = node.right_volume || 0;
    document.getElementById('carryoverLeft').textContent = node.left_spillover || 0;
    document.getElementById('carryoverRight').textContent = node.right_spillover || 0;
    document.getElementById('joinDate').textContent = node.created_at ? new Date(node.created_at).toLocaleDateString() : 'N/A';
    document.getElementById('lastActive').textContent = node.updated_at ? new Date(node.updated_at).toLocaleDateString() : 'N/A';

    // Set status badge
    const statusBadge = document.getElementById('userStatus');
    statusBadge.textContent = 'Active';
    statusBadge.className = 'badge bg-success';

    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('userDetailsModal'));
    modal.show();
}

function refreshNetwork() {
    window.location.reload();
}

// Live data updates
function startLiveUpdates() {
    // Update network stats every 30 seconds
    setInterval(function() {
        updateNetworkStatsFromServer();
    }, 30000);

    // Update balance stats every 60 seconds (if balance elements exist)
    setInterval(function() {
        updateBalanceStatsFromServer();
    }, 60000);
}

function updateNetworkStatsFromServer() {
    $.ajax({
        url: '{{ route("ajax.dashboard.network_stats") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Update the displayed statistics
                document.getElementById('level1Count').textContent = response.stats.level1;
                document.getElementById('level2Count').textContent = response.stats.level2;
                document.getElementById('level3Count').textContent = response.stats.level3;
                document.getElementById('totalCount').textContent = response.stats.total;

                // Show subtle update indicator
                showUpdateIndicator('Network stats updated');
            }
        },
        error: function() {
            // Silently fail for live updates
        }
    });
}

function updateBalanceStatsFromServer() {
    $.ajax({
        url: '{{ route("ajax.dashboard.balance_stats") }}',
        method: 'GET',
        success: function(response) {
            if (response.success) {
                // Update balance display if elements exist (for main dashboard integration)
                const balanceElement = document.getElementById('current-balance');
                const earningsElement = document.getElementById('total-earnings');
                const pendingElement = document.getElementById('pending-earnings');

                if (balanceElement) balanceElement.textContent = '₱' + response.balance;
                if (earningsElement) earningsElement.textContent = '₱' + response.totalEarnings;
                if (pendingElement) pendingElement.textContent = '₱' + response.pendingEarnings;

                // Show subtle update indicator
                showUpdateIndicator('Balance updated');
            }
        },
        error: function() {
            // Silently fail for live updates
        }
    });
}

function showUpdateIndicator(message) {
    // Create a subtle notification
    const indicator = document.createElement('div');
    indicator.className = 'alert alert-info position-fixed';
    indicator.style.cssText = 'top: 20px; right: 20px; z-index: 9999; font-size: 12px; padding: 8px 12px; opacity: 0; transition: opacity 0.3s;';
    indicator.textContent = message;

    document.body.appendChild(indicator);

    // Fade in
    setTimeout(() => indicator.style.opacity = '1', 100);

    // Fade out and remove
    setTimeout(() => {
        indicator.style.opacity = '0';
        setTimeout(() => {
            if (indicator.parentNode) {
                indicator.parentNode.removeChild(indicator);
            }
        }, 300);
    }, 2000);
}

// Start live updates
startLiveUpdates();
</script>
