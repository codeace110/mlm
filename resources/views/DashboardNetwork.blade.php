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
                    <div id="network-tree" class="w-100" style="height:800px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Treant.js -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/raphael/2.3.0/raphael.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/treant-js/1.0/Treant.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var chart_config = {
        chart: {
            container: "#network-tree",
            rootOrientation: "NORTH",
            connectors: {
                type: "step",
                style: {
                    "stroke": "#d1d5db",
                    "stroke-width": 2
                }
            },
            node: {
                HTMLclass: "soft-node"
            }
        },
        nodeStructure: {
           text: { name: "🏆 {{ auth()->user()->name }}" },
            image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
            children: [
                {
                    text: { name: "Left Earner" },
                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
                    children: [
                        {
                            text: { name: "Left-Left" },
                            image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
                            children: [
                                {
                                    text: { name: "L-L-1" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                },
                                {
                                    text: { name: "L-L-2" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                }
                            ]
                        },
                        {
                            text: { name: "Left-Right" },
                            image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
                            children: [
                                {
                                    text: { name: "L-R-1" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                },
                                {
                                    text: { name: "L-R-2" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                }
                            ]
                        }
                    ]
                },
                {
                    text: { name: "Right Earner" },
                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
                    children: [
                        {
                            text: { name: "Right-Left" },
                            image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
                            children: [
                                {
                                    text: { name: "R-L-1" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                },
                                {
                                    text: { name: "R-L-2" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                }
                            ]
                        },
                        {
                            text: { name: "Right-Right" },
                            image: "https://cdn-icons-png.flaticon.com/512/149/149071.png",
                            children: [
                                {
                                    text: { name: "R-R-1" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                },
                                {
                                    text: { name: "R-R-2" },
                                    image: "https://cdn-icons-png.flaticon.com/512/149/149071.png"
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    };

    new Treant(chart_config);
});
</script>

<style>
    .soft-node {
        border-radius: 1rem;
        background: #fff;
        border: 1px solid #e9ecef;
        box-shadow: 0px 3px 8px rgba(0,0,0,0.08);
        text-align: center;
        padding: 12px;
        transition: all 0.2s ease;
        width: 120px;
    }

    .soft-node:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        border-color: #5e72e4;
    }

    .soft-node img {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-bottom: 8px;
        border: 2px solid #5e72e4;
    }

    .soft-node .node-name {
        font-weight: 600;
        font-size: 14px;
        color: #344767;
    }
</style>

@endsection
