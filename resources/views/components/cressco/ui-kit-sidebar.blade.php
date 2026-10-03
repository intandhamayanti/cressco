@props([
    'activeSection' => 'button',
])

@php
    $styleGuideItems = [
        [
            'id' => 'color',
            'label' => 'Color',
            'route' => url('/design-system/color'),
            'icon' => 'color',
        ],
        [
            'id' => 'typography',
            'label' => 'Typography',
            'route' => url('/design-system/typography'),
            'icon' => 'typography',
        ],
    ];

    $componentItems = [
        [
            'id' => 'text-field',
            'label' => 'Text Field',
            'route' => url('/design-system/text-field'),
            'icon' => 'text-field',
            'badge' => null,
        ],
        [
            'id' => 'button',
            'label' => 'Button',
            'route' => url('/design-system/button'),
            'icon' => 'button',
            'badge' => null,
        ],
        [
            'id' => 'navigation',
            'label' => 'Navigation',
            'route' => url('/design-system/navigation'),
            'icon' => 'navigation',
            'badge' => null,
        ],
        [
            'id' => 'chart-card',
            'label' => 'Chart & Card',
            'route' => '#',
            'icon' => 'chart',
            'badge' => 'Soon',
            'disabled' => true,
        ],
        [
            'id' => 'other',
            'label' => 'Other',
            'route' => '#',
            'icon' => 'other',
            'badge' => 'Soon',
            'disabled' => true,
        ],
    ];
@endphp

<aside class="w-64 bg-white border-r border-gray-200/90 flex flex-col justify-between h-screen sticky top-0 font-sans select-none shrink-0 shadow-xs z-20">
    
    <!-- Top Brand & Header -->
    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
        <a href="{{ url('/design-system') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('images/logo.png') }}" alt="Cressco Logo" class="h-8 w-auto object-contain transition-transform duration-150 group-hover:scale-105">
            <div>
                <div class="font-bold text-gray-900 text-base leading-tight tracking-tight">Documentation</div>
                <div class="text-[10px] font-bold text-terracotta-600 uppercase tracking-widest mt-0.5">Cressco UI Kit</div>
            </div>
        </a>
    </div>

    <!-- Navigation Scrollable Area -->
    <div class="p-4 space-y-6 overflow-y-auto flex-1">
        
        <!-- SECTION 1: STYLE GUIDE -->
        <div class="space-y-1.5">
            <div class="px-3 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center justify-between">
                <span>Style Guide</span>
                <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span>
            </div>

            @foreach ($styleGuideItems as $item)
                @php
                    $isActive = ($activeSection === $item['id']);
                @endphp
                <a href="{{ $item['route'] }}"
                   class="group flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150 {{ $isActive ? 'bg-terracotta-50 text-terracotta-700 font-semibold shadow-2xs border border-terracotta-200/60' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70' }}">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 {{ $isActive ? 'text-terracotta-600' : 'text-gray-400 group-hover:text-gray-600' }}">
                            @if ($item['icon'] === 'color')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                            @elseif ($item['icon'] === 'typography')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h8m-8 6h16"/></svg>
                            @endif
                        </div>
                        <span>{{ $item['label'] }}</span>
                    </div>
                    @if ($isActive)
                        <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500"></span>
                    @endif
                </a>
            @endforeach
        </div>

        <!-- SECTION 2: COMPONENTS -->
        <div class="space-y-1.5">
            <div class="px-3 py-1 text-[11px] font-bold text-gray-400 uppercase tracking-wider flex items-center justify-between">
                <span>Components</span>
                <span class="w-1.5 h-1.5 rounded-full bg-terracotta-400"></span>
            </div>

            @foreach ($componentItems as $item)
                @php
                    $isActive = ($activeSection === $item['id']);
                    $isDisabled = $item['disabled'] ?? false;
                @endphp
                <a href="{{ $isDisabled ? 'javascript:void(0)' : $item['route'] }}"
                   class="group flex items-center justify-between px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150 {{ $isDisabled ? 'text-gray-400 cursor-not-allowed opacity-75' : ($isActive ? 'bg-terracotta-50 text-terracotta-700 font-semibold shadow-2xs border border-terracotta-200/60' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/70') }}">
                    <div class="flex items-center gap-3">
                        <div class="shrink-0 {{ $isDisabled ? 'text-gray-300' : ($isActive ? 'text-terracotta-600' : 'text-gray-400 group-hover:text-gray-600') }}">
                            @if ($item['icon'] === 'text-field')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            @elseif ($item['icon'] === 'button')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/></svg>
                            @elseif ($item['icon'] === 'navigation')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h7"/></svg>
                            @elseif ($item['icon'] === 'chart')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            @elseif ($item['icon'] === 'other')
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
                            @endif
                        </div>
                        <span>{{ $item['label'] }}</span>
                    </div>

                    @if ($item['badge'])
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500 border border-gray-200/80">
                            {{ $item['badge'] }}
                        </span>
                    @elseif ($isActive)
                        <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500"></span>
                    @endif
                </a>
            @endforeach
        </div>

    </div>

    <!-- Bottom Footer Meta -->
    <div class="p-4 border-t border-gray-100 bg-gray-50/50 flex items-center justify-between text-xs text-gray-500">
        <div>
            <div class="font-semibold text-gray-700">Phase 3.2.2</div>
            <div class="text-[10px]">Button & Navigation</div>
        </div>
        <a href="{{ url('/') }}" class="text-xs text-terracotta-600 hover:text-terracotta-700 font-semibold flex items-center gap-1">
            <span>App</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        </a>
    </div>

</aside>
