@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <h2 class="mb-4 fw-bold">Network Tree - {{ $user->name }}</h2>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">Genealogy Network</h6>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0" id="navigationBreadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('admin.genealogy.index') }}">Genealogy</a></li>
                                <li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-outline-secondary btn-sm" id="zoomIn" title="Zoom In">
                            <i class="fas fa-plus"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="zoomOut" title="Zoom Out">
                            <i class="fas fa-minus"></i>
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" id="resetZoom" title="Reset Zoom">
                            <i class="fas fa-home"></i>
                        </button>
                        <button class="btn btn-outline-primary btn-sm" id="fitToScreen" title="Fit to Screen">
                            <i class="fas fa-expand"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Color Legend -->
                    <div class="mb-3">
                        <h6 class="mb-2">Node Color Legend</h6>
                        <div class="row text-center">
                            <div class="col-md-2">
                                <div style="width: 20px; height: 20px; background: #2196F3; border-radius: 50%; display: inline-block;"></div>
                                <div class="small mt-1">Root User</div>
                            </div>
                            <div class="col-md-2">
                                <div style="width: 20px; height: 20px; background: #FFD700; border-radius: 50%; display: inline-block;"></div>
                                <div class="small mt-1">Direct (Fully Consumed)</div>
                            </div>
                            <div class="col-md-2">
                                <div style="width: 20px; height: 20px; background: #FF6B35; border-radius: 50%; display: inline-block;"></div>
                                <div class="small mt-1">Direct (Has Unconsumed)</div>
                            </div>
                            <div class="col-md-2">
                                <div style="width: 20px; height: 20px; background: #4CAF50; border-radius: 50%; display: inline-block;"></div>
                                <div class="small mt-1">Spillover (Fully Consumed)</div>
                            </div>
                            <div class="col-md-2">
                                <div style="width: 20px; height: 20px; background: #8B4513; border-radius: 50%; display: inline-block;"></div>
                                <div class="small mt-1">Spillover (Has Unconsumed)</div>
                            </div>
                        </div>
                    </div>

                    <div id="network-tree" class="text-center position-relative">
                        <svg width="100%" height="800" style="max-width: 1200px; height: auto;"></svg>
                        <script>
                            var treeData = {!! json_encode($treeData) !!};
                        </script>

                        <!-- Tooltip -->
                        <div id="node-tooltip" class="position-absolute bg-white border rounded shadow p-2 d-none" style="z-index: 1000; pointer-events: none; max-width: 250px;">
                            <div class="d-flex align-items-center mb-2">
                                <img id="tooltip-avatar" class="rounded-circle me-2" style="width: 30px; height: 30px;" alt="Avatar">
                                <div>
                                    <strong id="tooltip-name"></strong><br>
                                    <small class="text-muted" id="tooltip-email"></small>
                                </div>
                            </div>
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted d-block">Left Volume</small>
                                    <strong id="tooltip-left-volume"></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted d-block">Right Volume</small>
                                    <strong id="tooltip-right-volume"></strong>
                                </div>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">Double-click to navigate to this user's network</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('admin.genealogy.index') }}" class="btn btn-secondary me-2">
                        <i class="bi bi-arrow-left me-2"></i>Back to Genealogy
                    </a>
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-outline-primary me-2">
                        <i class="bi bi-person me-2"></i>View User Profile
                    </a>
                    <button class="btn btn-outline-info" onclick="toggleGenealogyTable()">
                        <i class="bi bi-table me-2"></i>Toggle Genealogy Table
                    </button>
                </div>
                <div class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Double-click nodes to navigate • Right-click for options • Hover for details
                </div>
            </div>
        </div>
    </div>

    <!-- Integrated Genealogy Table -->
    <div class="row mt-3" id="genealogyTableSection" style="display: none;">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="bi bi-table me-2"></i>Network Genealogy Table</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-items-center mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-secondary text-xs fw-bold ps-3">#</th>
                                    <th class="text-secondary text-xs fw-bold">User</th>
                                    <th class="text-secondary text-xs fw-bold">Level</th>
                                    <th class="text-secondary text-xs fw-bold">Left Volume</th>
                                    <th class="text-secondary text-xs fw-bold">Right Volume</th>
                                    <th class="text-secondary text-xs fw-bold">Effective Left</th>
                                    <th class="text-secondary text-xs fw-bold">Effective Right</th>
                                    <th class="text-secondary text-xs fw-bold">Joined</th>
                                    <th class="text-secondary text-xs fw-bold">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="networkGenealogyTableBody">
                                <!-- Table will be populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://d3js.org/d3.v7.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const svg = d3.select("#network-tree svg");
    const width = +svg.attr("width") || 1200;
    const height = +svg.attr("height");
    const g = svg.append("g").attr("transform", "translate(40,0)");

    // Zoom and pan functionality
    let zoom = d3.zoom()
        .scaleExtent([0.1, 4])
        .on("zoom", function(event) {
            g.attr("transform", event.transform);
        });

    svg.call(zoom);

    // Zoom control buttons
    document.getElementById('zoomIn').addEventListener('click', function() {
        svg.transition().call(zoom.scaleBy, 1.2);
    });

    document.getElementById('zoomOut').addEventListener('click', function() {
        svg.transition().call(zoom.scaleBy, 0.8);
    });

    document.getElementById('resetZoom').addEventListener('click', function() {
        svg.transition().call(zoom.transform, d3.zoomIdentity.translate(40, 0).scale(1));
    });

    document.getElementById('fitToScreen').addEventListener('click', function() {
        const bounds = g.node().getBBox();
        const fullWidth = bounds.width;
        const fullHeight = bounds.height;
        const midX = bounds.x + fullWidth / 2;
        const midY = bounds.y + fullHeight / 2;

        const scale = 0.9 / Math.max(fullWidth / width, fullHeight / height);
        const translate = [width / 2 - scale * midX, height / 2 - scale * midY];

        svg.transition().call(
            zoom.transform,
            d3.zoomIdentity.translate(translate[0], translate[1]).scale(scale)
        );
    });

    // Define clipPath for circular avatars
    const defs = svg.append("defs");
    const clipPath = defs.append("clipPath")
        .attr("id", "circle-clip")
        .append("circle")
        .attr("r", 10)
        .attr("cx", 0)
        .attr("cy", 0);

    const tree = d3.tree().size([height - 100, width - 80]).separation((a, b) => a.parent === b.parent ? 1.5 : 2.5);
    const root = d3.hierarchy(treeData);
    root.x0 = height / 2;
    root.y0 = 0;

    function update(source) {
        const treeDataLayout = tree(root);

        root.eachBefore(d => {
            d.x = d.x < 0 ? height + d.x : d.x;
            d.y = d.depth * 120;
        });

        const nodes = g.selectAll("g.node")
            .data(root.descendants(), d => d.data.id || d.data.name);

        const nodeEnter = nodes.enter().append("g")
            .attr("class", "node")
            .attr("transform", d => `translate(${source.y0},${source.x0})`)
            .on("click", clicked)
            .on("dblclick", navigateToUser)
            .on("contextmenu", showContextMenu)
            .on("mouseover", showTooltip)
            .on("mousemove", moveTooltip)
            .on("mouseout", hideTooltip)
            .style("cursor", "pointer");

        // Background circle for color based on depth and carryover status
        nodeEnter.append("circle")
            .attr("r", 10)
            .attr("class", "node-bg")
            .attr("fill", d => {
                // Root node (depth 0)
                if (d.depth === 0) {
                    return "#2196F3"; // Blue for current user
                }
                // Direct referrals (depth 1)
                else if (d.depth === 1) {
                    const hasUnconsumed = (d.data.left_consumed < d.data.total_left_volume) ||
                                         (d.data.right_consumed < d.data.total_right_volume);
                    return hasUnconsumed ? "#FF6B35" : "#FFD700"; // Orange-red with unconsumed, Yellow without
                }
                // Spillover nodes (depth > 1)
                else {
                    const hasUnconsumed = (d.data.left_consumed < d.data.total_left_volume) ||
                                         (d.data.right_consumed < d.data.total_right_volume);
                    return hasUnconsumed ? "#8B4513" : "#4CAF50"; // Brown with unconsumed, Green without
                }
            })
            .attr("stroke", "#fff")
            .attr("stroke-width", 2);

        // Avatar image clipped to circle
        const image = nodeEnter.append("image")
            .attr("class", "node-avatar")
            .attr("width", 20)
            .attr("height", 20)
            .attr("x", -10)
            .attr("y", -10)
            .attr("clip-path", "url(#circle-clip)")
            .attr("href", d => d.data.profile_image ? `images/profiles/${d.data.profile_image}` : null)
            .style("opacity", d => d.data.profile_image ? 1 : 0);

        nodeEnter.append("text")
            .attr("dy", 25)
            .attr("x", 0)
            .attr("text-anchor", "middle")
            .attr("font-size", "10px")
            .text(d => d.data.name);
        
        nodeEnter.append("text")
            .attr("dy", 40)
            .attr("x", 0)
            .attr("text-anchor", "middle")
            .attr("font-size", "8px")
            .attr("fill", "#666")
            .text(d => `(L:${d.data.effective_left}, R:${d.data.effective_right})`);

        nodeEnter.append("text")
            .attr("dy", 50)
            .attr("x", 0)
            .attr("text-anchor", "middle")
            .attr("font-size", "7px")
            .attr("fill", "#999")
            .text(d => (d.data.left_consumed < d.data.total_left_volume) || (d.data.right_consumed < d.data.total_right_volume) ?
                `Unconsumed: L:${d.data.effective_left}, R:${d.data.effective_right}` : '');

        const nodeUpdate = nodes.merge(nodeEnter);
        nodeUpdate.transition()
            .duration(750)
            .attr("transform", d => `translate(${d.y},${d.x})`);

        // Update background circle with new color logic
        nodeUpdate.select(".node-bg")
            .attr("r", 10)
            .attr("fill", d => {
                // Root node (depth 0)
                if (d.depth === 0) {
                    return "#2196F3"; // Blue for current user
                }
                // Direct referrals (depth 1)
                else if (d.depth === 1) {
                    const hasUnconsumed = (d.data.left_consumed < d.data.total_left_volume) ||
                                         (d.data.right_consumed < d.data.total_right_volume);
                    return hasUnconsumed ? "#FF6B35" : "#FFD700"; // Orange-red with unconsumed, Yellow without
                }
                // Spillover nodes (depth > 1)
                else {
                    const hasUnconsumed = (d.data.left_consumed < d.data.total_left_volume) ||
                                         (d.data.right_consumed < d.data.total_right_volume);
                    return hasUnconsumed ? "#8B4513" : "#4CAF50"; // Brown with unconsumed, Green without
                }
            })
            .attr("stroke", "#fff")
            .attr("stroke-width", 2);

        // Update avatar image
        nodeUpdate.select(".node-avatar")
            .attr("href", d => d.data.profile_image ? `images/profiles/${d.data.profile_image}` : null)
            .style("opacity", d => d.data.profile_image ? 1 : 0);

        // Update unconsumed text
        nodeUpdate.selectAll("text:nth-child(4)")
            .text(d => (d.data.left_consumed < d.data.total_left_volume) || (d.data.right_consumed < d.data.total_right_volume) ?
                `Unconsumed: L:${d.data.effective_left}, R:${d.data.effective_right}` : '');

        nodes.exit().transition()
            .duration(750)
            .attr("transform", d => `translate(${source.y},${source.x})`)
            .remove();

        const links = g.selectAll("path.link")
            .data(treeDataLayout.links(), d => d.target.data.id || d.target.data.name);

        const linkEnter = links.enter().insert("path", "g")
            .attr("class", "link")
            .attr("d", d => straightLine({x: source.x0, y: source.y0}, {x: source.x0, y: source.y0}));

        const linkUpdate = links.merge(linkEnter);
        linkUpdate.transition()
            .duration(750)
            .attr("d", d => straightLine(d.source, d.target));

        links.exit().transition()
            .duration(750)
            .attr("d", d => straightLine({x: source.x, y: source.y}, {x: source.x, y: source.y}))
            .remove();

        root.eachBefore(d => {
            d.x0 = d.x;
            d.y0 = d.y;
        });
    }

    function straightLine(s, d) {
        return `M ${s.y} ${s.x} L ${d.y} ${d.x}`; // straight line for classic look
    }

    function clicked(event, d) {
        if (d.children) {
            d._children = d.children;
            d.children = null;
        } else if (d._children) {
            d.children = d._children;
            d._children = null;
        }
        update(d);
    }

    function navigateToUser(event, d) {
        event.stopPropagation();
        if (d.data.id) {
            window.location.href = `{{ route('admin.genealogy.network', '') }}/${d.data.id}`;
        }
    }

    function showContextMenu(event, d) {
        event.preventDefault();

        // Create context menu
        const menu = document.createElement('div');
        menu.className = 'position-absolute bg-white border rounded shadow p-2';
        menu.style.cssText = `left: ${event.pageX}px; top: ${event.pageY}px; z-index: 1000; min-width: 150px;`;
        menu.innerHTML = `
            <div class="context-menu-item p-2 hover-bg-light cursor-pointer" onclick="navigateToUserFromContext(${d.data.id})">
                <i class="fas fa-external-link-alt me-2"></i>View Network
            </div>
            <div class="context-menu-item p-2 hover-bg-light cursor-pointer" onclick="closeContextMenu()">
                <i class="fas fa-times me-2"></i>Close
            </div>
        `;

        document.body.appendChild(menu);

        // Close menu when clicking elsewhere
        const closeMenu = (e) => {
            if (!menu.contains(e.target)) {
                menu.remove();
                document.removeEventListener('click', closeMenu);
            }
        };

        setTimeout(() => document.addEventListener('click', closeMenu), 100);
    }

    function navigateToUserFromContext(userId) {
        window.location.href = `{{ route('admin.genealogy.network', '') }}/${userId}`;
    }

    function closeContextMenu() {
        // Menu closes automatically
    }

    function showTooltip(event, d) {
        const tooltip = document.getElementById('node-tooltip');
        const rect = tooltip.getBoundingClientRect();

        // Update tooltip content
        document.getElementById('tooltip-name').textContent = d.data.name;
        document.getElementById('tooltip-email').textContent = d.data.email || 'N/A';
        document.getElementById('tooltip-left-volume').textContent = d.data.effective_left || 0;
        document.getElementById('tooltip-right-volume').textContent = d.data.effective_right || 0;

        // Update avatar
        const avatar = document.getElementById('tooltip-avatar');
        if (d.data.profile_image) {
            avatar.src = `images/profiles/${d.data.profile_image}`;
            avatar.style.display = 'block';
        } else {
            avatar.style.display = 'none';
        }

        tooltip.classList.remove('d-none');
    }

    function moveTooltip(event, d) {
        const tooltip = document.getElementById('node-tooltip');
        const rect = tooltip.getBoundingClientRect();
        const svgRect = document.querySelector('#network-tree svg').getBoundingClientRect();

        let left = event.pageX + 10;
        let top = event.pageY - 10;

        // Keep tooltip within viewport
        if (left + rect.width > window.innerWidth) {
            left = event.pageX - rect.width - 10;
        }

        if (top + rect.height > window.innerHeight) {
            top = event.pageY - rect.height - 10;
        }

        tooltip.style.left = left + 'px';
        tooltip.style.top = top + 'px';
    }

    function hideTooltip(event, d) {
        document.getElementById('node-tooltip').classList.add('d-none');
    }

    function toggleGenealogyTable() {
        const tableSection = document.getElementById('genealogyTableSection');
        const isVisible = tableSection.style.display !== 'none';

        if (isVisible) {
            tableSection.style.display = 'none';
        } else {
            tableSection.style.display = 'block';
            populateGenealogyTable();
        }
    }

    function populateGenealogyTable() {
        const tbody = document.getElementById('networkGenealogyTableBody');
        tbody.innerHTML = '';

        // Flatten the tree data for table display
        const flattenedData = [];
        root.eachBefore(d => {
            flattenedData.push({
                id: d.data.id,
                name: d.data.name,
                email: d.data.email,
                level: d.depth + 1,
                total_left_volume: d.data.total_left_volume || 0,
                total_right_volume: d.data.total_right_volume || 0,
                effective_left: d.data.effective_left || 0,
                effective_right: d.data.effective_right || 0,
                created_at: d.data.created_at || 'N/A',
                profile_image: d.data.profile_image
            });
        });

        // Sort by level and name
        flattenedData.sort((a, b) => {
            if (a.level !== b.level) return a.level - b.level;
            return a.name.localeCompare(b.name);
        });

        flattenedData.forEach((user, index) => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="ps-3">${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        <img src="${user.profile_image ? 'images/profiles/' + user.profile_image : '{{ asset('assets/img/team-1.jpg') }}'}"
                             class="avatar avatar-sm me-3" alt="Profile">
                        <div>
                            <div class="fw-bold">${user.name}</div>
                            <small class="text-muted">${user.email}</small>
                        </div>
                    </div>
                </td>
                <td>${user.level}</td>
                <td>${user.total_left_volume}</td>
                <td>${user.total_right_volume}</td>
                <td>${user.effective_left}</td>
                <td>${user.effective_right}</td>
                <td>${user.created_at}</td>
                <td>
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-outline-primary" onclick="navigateToUserFromTable(${user.id})" title="View Network">
                            <i class="bi bi-diagram-3"></i>
                        </button>
                        <a href="{{ route('admin.users.show', '') }}/${user.id}" class="btn btn-sm btn-outline-info" title="View Profile">
                            <i class="bi bi-person"></i>
                        </a>
                    </div>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function navigateToUserFromTable(userId) {
        window.location.href = `{{ route('admin.genealogy.network', '') }}/${userId}`;
    }

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + T to toggle table
        if ((e.ctrlKey || e.metaKey) && e.key === 't') {
            e.preventDefault();
            toggleGenealogyTable();
        }

        // Escape to close table
        if (e.key === 'Escape') {
            const tableSection = document.getElementById('genealogyTableSection');
            if (tableSection.style.display !== 'none') {
                tableSection.style.display = 'none';
            }
        }
    });

    update(root);
});
</script>
<style>
    .node circle { stroke: #fff; stroke-width: 2px; }
    .node text { font: 12px sans-serif; fill: #333; }
    .node-avatar { transition: opacity 0.3s; }
    .link { fill: none; stroke: #999; stroke-opacity: 0.6; stroke-width: 3px; } /* thicker edges for customization */
</style>
@endsection