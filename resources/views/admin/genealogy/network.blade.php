@extends('layouts.admin')

@section('content')
<div class="container-fluid mt-4">
    <h2 class="mb-4 fw-bold">Network Tree - {{ $user->name }}</h2>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Genealogy Network</h6>
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

                    <div id="network-tree" class="text-center">
                        <svg width="100%" height="800" style="max-width: 1200px; height: auto;"></svg>
                        <script>
                            var treeData = {!! json_encode($treeData) !!};
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <a href="{{ route('admin.genealogy.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i>Back to Genealogy
            </a>
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
            .on("click", clicked);

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