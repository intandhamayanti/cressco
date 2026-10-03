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
                    @if ($icon === 'dashboard')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    @elseif ($icon === 'projects' || $icon === 'folder')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                    @elseif ($icon === 'message' || $icon === 'chat')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    @elseif ($icon === 'schedule' || $icon === 'calendar')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    @elseif ($icon === 'document' || $icon === 'file')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    @elseif ($icon === 'teams' || $icon === 'users')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    @elseif ($icon === 'help' || $icon === 'lifebuoy')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    @elseif ($icon === 'setting' || $icon === 'settings' || $icon === 'gear')
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    @else
                        {{ $icon }}
                    @endif
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
