@props([
    'breadcrumbs' => ['Main Menu'],
    'showActions' => true,
])

<div {{ $attributes->merge(['class' => 'h-16 w-full bg-white border border-gray-200/90 rounded-2xl px-6 flex items-center justify-between shadow-2xs font-sans select-none']) }}>
    
    <!-- Breadcrumbs Navigation -->
    <nav class="flex items-center space-x-2 text-sm">
        @foreach ($breadcrumbs as $index => $crumb)
            @if ($index > 0)
                <span class="text-gray-400 font-medium">/</span>
            @endif

            @if ($index === count($breadcrumbs) - 1)
                <!-- Current / Active Leaf Crumb -->
                <span class="text-gray-900 font-semibold">{{ $crumb }}</span>
            @else
                <!-- Parent Crumb -->
                <span class="text-gray-500 hover:text-gray-700 transition cursor-pointer font-medium">{{ $crumb }}</span>
            @endif
        @endforeach
    </nav>

    <!-- Right Action Area -->
    @if ($showActions)
        <div class="flex items-center gap-2">
            @if (isset($actions))
                {{ $actions }}
            @else
                <!-- 3 Action Button Placeholders matching Figma -->
                <button type="button" class="w-9 h-9 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-500 hover:text-gray-900 transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
                <button type="button" class="w-9 h-9 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-500 hover:text-gray-900 transition relative shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <span class="absolute top-2 right-2 w-1.5 h-1.5 bg-terracotta-500 rounded-full"></span>
                </button>
                <button type="button" class="w-9 h-9 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 flex items-center justify-center text-gray-500 hover:text-gray-900 transition shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            @endif
        </div>
    @endif

</div>
