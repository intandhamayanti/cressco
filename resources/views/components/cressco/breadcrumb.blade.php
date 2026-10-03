@props([
    'items' => [], // e.g. [['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'home'], ['label' => 'Courses', 'url' => '/courses'], ['label' => 'Course Detail']]
    'separator' => 'chevron', // 'chevron', 'slash'
    'class' => '',
])

<nav aria-label="Breadcrumb" {{ $attributes->merge(['class' => 'flex items-center text-sm font-medium text-gray-500 ' . $class]) }}>
    <ol class="inline-flex items-center gap-1.5 sm:gap-2 flex-wrap">
        @if (empty($items))
            <li class="inline-flex items-center">
                <a href="/" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-900 transition-colors">
                    <x-cressco.icon-helper name="home" class="w-4 h-4 text-gray-400" />
                    <span>Home</span>
                </a>
            </li>
            <li class="flex items-center gap-1.5 text-gray-400">
                <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                <span class="text-gray-900 font-semibold">Current Page</span>
            </li>
        @else
            @foreach ($items as $index => $item)
                @php
                    $isLast = $index === count($items) - 1;
                    $hasUrl = !empty($item['url']) && !$isLast;
                @endphp
                
                <li class="inline-flex items-center gap-1.5">
                    @if ($index > 0)
                        @if ($separator === 'slash')
                            <span class="text-gray-300 font-light px-0.5">/</span>
                        @else
                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                        @endif
                    @endif

                    @if ($hasUrl)
                        <a href="{{ $item['url'] }}" class="inline-flex items-center gap-1.5 text-gray-500 hover:text-gray-900 transition-colors">
                            @if (!empty($item['icon']))
                                <x-cressco.icon-helper :name="$item['icon']" class="w-4 h-4 text-gray-400" />
                            @endif
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @else
                        <span class="inline-flex items-center gap-1.5 {{ $isLast ? 'text-gray-900 font-semibold' : 'text-gray-500' }}" @if ($isLast) aria-current="page" @endif>
                            @if (!empty($item['icon']))
                                <x-cressco.icon-helper :name="$item['icon']" class="w-4 h-4 {{ $isLast ? 'text-terracotta-500' : 'text-gray-400' }}" />
                            @endif
                            <span>{{ $item['label'] }}</span>
                        </span>
                    @endif
                </li>
            @endforeach
        @endif
    </ol>
</nav>
