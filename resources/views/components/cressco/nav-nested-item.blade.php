@props([
    'label' => '',
    'href' => '#',
    'active' => false,
    'badge' => null,
    'disabled' => false,
])

@php
    $isDisabled = $disabled;
    $baseClasses = 'group relative flex items-center justify-between px-3 py-2 text-xs font-medium rounded-lg transition-all duration-150 pl-9';
    
    if ($isDisabled) {
        $stateClasses = 'text-gray-300 cursor-not-allowed pointer-events-none';
    } elseif ($active) {
        $stateClasses = 'bg-terracotta-50 text-terracotta-600 font-semibold';
    } else {
        $stateClasses = 'text-gray-600 hover:text-gray-900 hover:bg-gray-50';
    }
@endphp

<a
    href="{{ $isDisabled ? '#' : $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->merge(['class' => "{$baseClasses} {$stateClasses}"]) }}
>
    <!-- Indicator bullet line -->
    <span class="absolute left-4 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full {{ $active ? 'bg-terracotta-500 ring-2 ring-terracotta-200' : 'bg-gray-300 group-hover:bg-gray-400' }}"></span>

    <span class="truncate">{{ $label ?: $slot }}</span>

    @if ($badge)
        <span class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full {{ $active ? 'bg-terracotta-100 text-terracotta-700' : 'bg-gray-100 text-gray-600 group-hover:bg-gray-200' }}">
            {{ $badge }}
        </span>
    @endif
</a>
