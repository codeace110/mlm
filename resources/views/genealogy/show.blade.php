<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __("Genealogy Tree - ") }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Downlines</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['total_downlines'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Volume</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($stats['total_volume']) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Current Level</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $stats['current_level'] }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Earnings</div>
                                <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">₱{{ number_format($stats['total_earnings'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Network Tree Visualization -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-8">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Network Tree</h3>
                    <div id="network-container" style="height: 600px; width: 100%;"></div>
                </div>
            </div>

            <!-- Tree Structure -->
            @if($genealogy['children'])
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tree Structure</h3>
                    <div class="tree-structure">
                        @include('genealogy.tree-node', ['node' => ['user' => $user, 'children' => $genealogy['children']], 'level' => 0])
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!-- Network Visualization Script -->
    <script src="https://unpkg.com/vis-network@9.1.2/dist/vis-network.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load network data
            fetch('{{ route("genealogy.network-data", $user) }}')
                .then(response => response.json())
                .then(data => {
                    const container = document.getElementById('network-container');

                    const nodes = new vis.DataSet(data.nodes.map(node => ({
                        id: node.id,
                        label: node.label,
                        title: node.title,
                        level: node.level,
                        color: {
                            background: node.level <= 3 ? '#4CAF50' : node.level <= 6 ? '#FF9800' : '#F44336',
                            border: '#2E7D32'
                        }
                    })));

                    const edges = new vis.DataSet(data.edges.map(edge => ({
                        from: edge.from,
                        to: edge.to,
                        label: edge.label,
                        color: edge.side === 'left' ? '#2196F3' : '#FF5722',
                        width: 2
                    })));

                    const networkData = { nodes, edges };
                    const options = {
                        nodes: {
                            shape: 'circle',
                            size: 25,
                            font: {
                                size: 12,
                                color: '#ffffff'
                            },
                            borderWidth: 2
                        },
                        edges: {
                            arrows: 'to',
                            smooth: {
                                type: 'cubicBezier',
                                forceDirection: 'horizontal'
                            }
                        },
                        layout: {
                            hierarchical: {
                                direction: 'UD',
                                sortMethod: 'directed',
                                levelSeparation: 100,
                                nodeSpacing: 150
                            }
                        },
                        physics: {
                            enabled: false
                        },
                        interaction: {
                            dragNodes: true,
                            dragView: true,
                            zoomView: true
                        }
                    };

                    new vis.Network(container, networkData, options);
                });
        });
    </script>
</x-app-layout>