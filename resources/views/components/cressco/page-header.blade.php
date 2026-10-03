@props([
    'title' => 'Title',
    'subtitle' => 'Subtitle',
    'avatars' => false,
    'search' => false,
    'filter' => false,
    'buttonText' => null,
    'buttonVariant' => 'primary',
    'buttonIcon' => null,
])

<div {{ $attributes->merge(['class' => 'w-full py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 font-sans select-none border-b border-gray-100 pb-5']) }}>
    
    <!-- Title & Subtitle -->
    <div class="space-y-0.5">
        <h2 class="text-xl font-bold tracking-tight text-gray-900">{{ $title }}</h2>
        <p class="text-xs text-gray-500 font-normal">{{ $subtitle }}</p>
    </div>

    <!-- Action / Filter Area -->
    <div class="flex items-center gap-3 shrink-0">
        
        @if ($avatars)
            <!-- Avatars Stack -->
            <div class="flex items-center -space-x-2 overflow-hidden py-1">
                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80" alt="Avatar 1">
                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover" src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=100&auto=format&fit=crop&q=80" alt="Avatar 2">
                <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white object-cover" src="https://images.unsplash.com/photo-1570295999919-56ceb5ecca61?w=100&auto=format&fit=crop&q=80" alt="Avatar 3">
                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 ring-2 ring-white text-[10px] font-bold text-gray-600">
                    +4
                </div>
            </div>
        @endif

        @if ($search)
            <!-- Search Bar Control -->
            <div class="relative w-48 sm:w-60">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text"
                       placeholder="Search"
                       class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-gray-200 rounded-xl placeholder-gray-400 text-gray-800 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                />
            </div>
        @endif

        @if ($filter)
            <!-- Filter Button -->
            <x-cressco.button variant="secondary" size="sm" leadingIcon="filter">
                Filter
            </x-cressco.button>
        @endif

        @if ($buttonText)
            <x-cressco.button :variant="$buttonVariant" size="sm" :leadingIcon="$buttonIcon">
                {{ $buttonText }}
            </x-cressco.button>
        @endif

        @if (isset($slot) && trim($slot) !== '')
            {{ $slot }}
        @endif

    </div>

</div>
