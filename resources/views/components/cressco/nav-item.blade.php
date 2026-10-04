@props([
    'label' => '',
    'icon' => null,
    'active' => false,
    'href' => '#',
    'badge' => null,
    'hasSubmenu' => false,
    'isOpen' => false,
    'indent' => false,
    'isNested' => false,
])

@php
    $activeClass = $active 
        ? 'bg-gray-100/90 text-gray-900 font-semibold shadow-2xs' 
        : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100/60 font-medium';
@endphp

@if ($isNested)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => 'group flex items-center justify-between py-1.5 px-3 rounded-lg text-xs transition-colors duration-150 ' . ($active ? 'text-gray-900 font-bold bg-gray-100/60' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-50 font-normal')]) }}>
        <span class="truncate">{{ $label }}</span>
        @if ($badge)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-[10px] font-semibold bg-gray-200 text-gray-700">
                {{ $badge }}
            </span>
        @endif
    </a>
@else
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => 'group flex items-center justify-between px-3 py-2 rounded-xl text-sm transition-all duration-150 select-none ' . $activeClass]) }}>
        <div class="flex items-center gap-3 min-w-0">
            @if ($icon)
                <div class="shrink-0 text-gray-500 group-hover:text-gray-900 {{ $active ? 'text-gray-900' : '' }}">
                    <x-cressco.icon-helper :name="$icon" class="w-4.5 h-4.5" />
                </div>
            @endif
            <span class="truncate">{{ $label }}</span>
        </div>

        @if ($badge)
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-800">
                {{ $badge }}
            </span>
        @elseif ($hasSubmenu)
            <svg class="w-4 h-4 text-gray-400 transition-transform duration-150 {{ $isOpen ? 'rotate-90 text-gray-600' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        @endif
    </a>
@endif
