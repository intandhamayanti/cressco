@props([
    'type' => 'success', // 'success', 'error', 'warning', 'info'
    'title' => null,
    'dismissible' => true,
])

@php
    $typeClasses = match($type) {
        'error', 'destructive' => 'bg-red-50/80 border-red-200 text-red-900',
        'warning' => 'bg-amber-50/80 border-amber-200 text-amber-900',
        'info' => 'bg-blue-50/80 border-blue-200 text-blue-900',
        default => 'bg-emerald-50/80 border-emerald-200 text-emerald-900',
    };

    $iconColor = match($type) {
        'error', 'destructive' => 'text-red-500',
        'warning' => 'text-amber-500',
        'info' => 'text-blue-500',
        default => 'text-emerald-500',
    };
@endphp

<div x-data="{ show: true }" x-show="show" x-transition class="w-full rounded-2xl border p-4 shadow-2xs flex items-start gap-3.5 {{ $typeClasses }}">
    <!-- Status Icon -->
    <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0 mt-0.5 {{ $iconColor }}">
        @if ($type === 'error' || $type === 'destructive')
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        @else
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        @endif
    </div>

    <div class="flex-1 min-w-0">
        @if ($title)
            <h4 class="text-xs font-bold">{{ $title }}</h4>
        @endif
        <div class="text-xs mt-0.5 opacity-90 leading-relaxed">
            {{ $slot }}
        </div>
    </div>

    @if ($dismissible)
        <button type="button" @click="show = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition shrink-0 focus:outline-hidden">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    @endif
</div>
