@props(['node', 'level' => 0])

@php
    $user = $node['user'];
    $children = $node['children'] ?? [];
    $hasChildren = count($children) > 0;
@endphp

<div class="tree-node mb-4">
    <!-- User Card -->
    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border-l-4 {{ isset($node['placement_side']) && $node['placement_side'] === 'left' ? 'border-l-blue-500' : 'border-l-orange-500' }}">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->name }}</h4>
                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500">ID: {{ $user->id }}</p>
                </div>
            </div>

            <div class="text-right">
                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                    Level {{ $level + 1 }}
                </div>
                @if(isset($node['tree']) && $node['tree'])
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Vol: {{ $node['tree']->total_left_volume + $node['tree']->total_right_volume }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Placement Side Badge -->
        @if(isset($node['placement_side']))
            <div class="mt-2">
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                    {{ $node['placement_side'] === 'left' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200' }}">
                    {{ ucfirst($node['placement_side']) }} Side
                </span>
            </div>
        @endif
    </div>

    <!-- Children -->
    @if($hasChildren)
        <div class="ml-6 mt-4">
            <div class="relative">
                <!-- Connection Line -->
                <div class="absolute -top-4 left-4 w-0.5 h-8 bg-gray-300 dark:bg-gray-600"></div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach($children as $side => $childNode)
                        <div class="relative">
                            <!-- Side Label -->
                            <div class="absolute -top-6 left-0 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                {{ $side }} Side
                            </div>

                            <!-- Connection Line for Child -->
                            <div class="absolute -top-4 left-4 w-0.5 h-4 bg-gray-300 dark:bg-gray-600"></div>

                            @include('genealogy.tree-node', ['node' => $childNode, 'level' => $level + 1])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>