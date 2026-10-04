@props([
    'variant' => 'primary', // 'primary', 'secondary', 'outline', 'ghost', 'dark', 'white'
    'size' => 'md', // 'xs', 'sm', 'md', 'lg', 'xl'
    'leadingIcon' => null,
    'trailingIcon' => null,
    'iconOnly' => false,
    'disabled' => false,
    'state' => 'default', // 'default', 'hover', 'focus', 'active', 'disabled'
    'as' => 'button', // 'button' or 'a'
    'type' => 'button',
    'href' => '#',
])

@php
    $isDisabled = $disabled || $state === 'disabled';

    // Size Classes
    $sizeClasses = match ($size) {
        'xs' => $iconOnly ? 'w-7 h-7 p-0' : 'h-7 px-2.5 text-xs gap-1.5',
        'sm' => $iconOnly ? 'w-8 h-8 p-0' : 'h-8 px-3 text-xs font-medium gap-1.5',
        'md' => $iconOnly ? 'w-10 h-10 p-0' : 'h-10 px-4 text-sm font-medium gap-2',
        'lg' => $iconOnly ? 'w-11 h-11 p-0' : 'h-11 px-5 text-sm font-semibold gap-2.5',
        'xl' => $iconOnly ? 'w-12 h-12 p-0' : 'h-12 px-6 text-base font-semibold gap-3',
        default => $iconOnly ? 'w-10 h-10 p-0' : 'h-10 px-4 text-sm font-medium gap-2',
    };

    $roundedClass = match ($size) {
        'xs' => 'rounded-md',
        'sm' => 'rounded-lg',
        'md' => 'rounded-xl',
        'lg' => 'rounded-xl',
        'xl' => 'rounded-2xl',
        default => 'rounded-xl',
    };

    // Variant & State Classes
    $variantClasses = match ($variant) {
        'primary' => match ($state) {
            'disabled' => 'bg-[#98A2B3] text-white cursor-not-allowed opacity-90 border-transparent shadow-none',
            'hover' => 'bg-terracotta-600 text-white shadow-xs',
            'focus' => 'bg-terracotta-600 text-white ring-2 ring-terracotta-500 ring-offset-2 shadow-xs',
            'active' => 'bg-terracotta-700 text-white shadow-none',
            default => $isDisabled
                ? 'bg-[#98A2B3] text-white cursor-not-allowed border-transparent shadow-none'
                : 'bg-terracotta-500 text-white hover:bg-terracotta-600 active:bg-terracotta-700 focus-visible:ring-2 focus-visible:ring-terracotta-500 focus-visible:ring-offset-2 shadow-xs',
        },
        'secondary', 'outline' => match ($state) {
            'disabled' => 'bg-white border border-gray-200 text-gray-300 cursor-not-allowed shadow-none',
            'hover' => 'bg-gray-50 border border-gray-300 text-gray-800 shadow-2xs',
            'focus' => 'bg-white border border-gray-400 text-gray-900 ring-2 ring-gray-200 shadow-2xs',
            'active' => 'bg-gray-100 border border-gray-300 text-gray-900 shadow-none',
            default => $isDisabled
                ? 'bg-white border border-gray-200 text-gray-300 cursor-not-allowed shadow-none'
                : 'bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-gray-900 hover:border-gray-300 active:bg-gray-100 focus-visible:ring-2 focus-visible:ring-gray-200 shadow-2xs',
        },
        'ghost' => match ($state) {
            'disabled' => 'bg-transparent text-gray-300 cursor-not-allowed shadow-none',
            'hover' => 'bg-gray-100 text-gray-900',
            'focus' => 'bg-gray-100 text-gray-900 ring-2 ring-gray-200',
            'active' => 'bg-gray-200 text-gray-900',
            default => $isDisabled
                ? 'bg-transparent text-gray-300 cursor-not-allowed shadow-none'
                : 'bg-transparent text-gray-600 hover:bg-gray-100 hover:text-gray-900 active:bg-gray-200 focus-visible:ring-2 focus-visible:ring-gray-200',
        },
        'dark' => match ($state) {
            'disabled' => 'bg-gray-400 text-white cursor-not-allowed shadow-none',
            'hover' => 'bg-gray-800 text-white shadow-xs',
            'focus' => 'bg-gray-800 text-white ring-2 ring-gray-900 ring-offset-2 shadow-xs',
            'active' => 'bg-black text-white shadow-none',
            default => $isDisabled
                ? 'bg-gray-400 text-white cursor-not-allowed shadow-none'
                : 'bg-[#101828] text-white hover:bg-gray-800 active:bg-black focus-visible:ring-2 focus-visible:ring-gray-900 focus-visible:ring-offset-2 shadow-xs',
        },
        'white' => match ($state) {
            'disabled' => 'bg-gray-50 text-gray-300 border border-gray-100 cursor-not-allowed shadow-none',
            'hover' => 'bg-gray-50 text-gray-900 shadow-sm border border-gray-200',
            'focus' => 'bg-white text-gray-900 ring-2 ring-terracotta-500 shadow-sm border border-gray-200',
            'active' => 'bg-gray-100 text-gray-900 shadow-none border border-gray-200',
            default => $isDisabled
                ? 'bg-gray-50 text-gray-300 border border-gray-100 cursor-not-allowed shadow-none'
                : 'bg-white text-gray-900 border border-gray-200/80 hover:bg-gray-50 active:bg-gray-100 focus-visible:ring-2 focus-visible:ring-terracotta-500 shadow-xs',
        },
        default => 'bg-terracotta-500 text-white hover:bg-terracotta-600',
    };

    $iconSizeClass = match ($size) {
        'xs' => 'w-3.5 h-3.5',
        'sm' => 'w-4 h-4',
        'md' => 'w-4 h-4',
        'lg' => 'w-5 h-5',
        'xl' => 'w-5 h-5',
        default => 'w-4 h-4',
    };
@endphp

@if ($as === 'a')
    <a href="{{ $isDisabled ? 'javascript:void(0)' : $href }}"
       {{ $attributes->merge(['class' => "inline-flex items-center justify-center font-sans tracking-tight select-none transition-all duration-150 outline-none $roundedClass $sizeClasses $variantClasses " . ($isDisabled ? 'pointer-events-none' : '')]) }}>
        @if ($leadingIcon)
            @if (is_string($leadingIcon) && !str_contains($leadingIcon, '<svg'))
                <x-cressco.icon-helper :name="$leadingIcon" class="{{ $iconSizeClass }} shrink-0" />
            @else
                {!! $leadingIcon !!}
            @endif
        @endif

        @if (!$iconOnly && trim($slot) !== '')
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap">{{ $slot }}</span>
        @endif

        @if ($trailingIcon)
            @if (is_string($trailingIcon) && !str_contains($trailingIcon, '<svg'))
                <x-cressco.icon-helper :name="$trailingIcon" class="{{ $iconSizeClass }} shrink-0" />
            @else
                {!! $trailingIcon !!}
            @endif
        @endif
    </a>
@else
    <button type="{{ $type }}"
            {{ $isDisabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => "inline-flex items-center justify-center font-sans tracking-tight select-none transition-all duration-150 outline-none $roundedClass $sizeClasses $variantClasses"]) }}>
        @if ($leadingIcon)
            @if (is_string($leadingIcon) && !str_contains($leadingIcon, '<svg'))
                <x-cressco.icon-helper :name="$leadingIcon" class="{{ $iconSizeClass }} shrink-0" />
            @else
                {!! $leadingIcon !!}
            @endif
        @endif

        @if (!$iconOnly && trim($slot) !== '')
            <span class="inline-flex items-center gap-1.5 whitespace-nowrap">{{ $slot }}</span>
        @endif

        @if ($trailingIcon)
            @if (is_string($trailingIcon) && !str_contains($trailingIcon, '<svg'))
                <x-cressco.icon-helper :name="$trailingIcon" class="{{ $iconSizeClass }} shrink-0" />
            @else
                {!! $trailingIcon !!}
            @endif
        @endif
    </button>
@endif
