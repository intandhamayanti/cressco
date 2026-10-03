@props([
    'provider' => 'google', // 'google', 'apple'
    'variant' => 'default', // 'default', 'filled-gray', 'icon-only', 'icon-only-gray'
    'size' => 'md',
    'disabled' => false,
    'label' => null,
])

@php
    $defaultLabel = match($provider) {
        'google' => 'Sign In with Google',
        'apple' => 'Sign In with Apple',
        default => 'Sign In',
    };
    $text = $label ?? $defaultLabel;

    $isIconOnly = str_contains($variant, 'icon-only');
    $isGray = str_contains($variant, 'gray') || $variant === 'filled-gray';

    $bgBorder = $isGray 
        ? 'bg-gray-100 hover:bg-gray-200/80 border-transparent text-gray-800'
        : 'bg-white hover:bg-gray-50 border border-gray-200 text-gray-800 shadow-sm';

    $sizeClasses = match($size) {
        'sm' => $isIconOnly ? 'w-9 h-9 p-0' : 'h-9 px-3 text-xs gap-2.5',
        'lg' => $isIconOnly ? 'w-12 h-12 p-0' : 'h-12 px-6 text-base gap-3.5',
        default => $isIconOnly ? 'w-10 h-10 p-0' : 'h-10 px-4 text-sm font-medium gap-2.5',
    };
@endphp

<button type="button"
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => "inline-flex items-center justify-center rounded-xl font-sans tracking-tight transition-all duration-150 outline-none $sizeClasses $bgBorder " . ($disabled ? 'opacity-50 cursor-not-allowed shadow-none' : 'active:scale-[0.98]')]) }}>
    @if ($provider === 'google')
        <!-- Google Logo SVG -->
        <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" width="20" height="20">
            <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
            <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
            <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
            <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
        </svg>
    @elseif ($provider === 'apple')
        <!-- Apple Logo SVG -->
        <svg class="w-5 h-5 shrink-0 fill-current text-gray-900" viewBox="0 0 24 24" width="20" height="20">
            <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M15.97 6.86c.65-.79 1.09-1.89.97-2.99-1 .04-2.19.67-2.88 1.48-.61.71-1.14 1.83-1 2.92 1.12.09 2.26-.62 2.91-1.41z"/>
        </svg>
    @endif

    @if (!$isIconOnly)
        <span class="whitespace-nowrap">{{ $text }}</span>
    @endif
</button>
