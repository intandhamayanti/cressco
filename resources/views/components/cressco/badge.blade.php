@props([
    'variant' => 'terracotta', // 'terracotta', 'success', 'warning', 'info', 'error', 'gray', 'purple'
    'size' => 'md', // 'sm', 'md', 'lg'
    'dot' => false,
])

@php
    $variantClasses = match($variant) {
        'success', 'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200/70',
        'warning', 'amber' => 'bg-amber-50 text-amber-700 border-amber-200/70',
        'info', 'blue' => 'bg-blue-50 text-blue-700 border-blue-200/70',
        'error', 'red' => 'bg-red-50 text-red-700 border-red-200/70',
        'purple' => 'bg-purple-50 text-purple-700 border-purple-200/70',
        'gray' => 'bg-gray-100 text-gray-700 border-gray-200',
        default => 'bg-terracotta-50 text-terracotta-700 border-terracotta-200/70',
    };

    $dotColor = match($variant) {
        'success', 'emerald' => 'bg-emerald-500',
        'warning', 'amber' => 'bg-amber-500',
        'info', 'blue' => 'bg-blue-500',
        'error', 'red' => 'bg-red-500',
        'purple' => 'bg-purple-500',
        'gray' => 'bg-gray-500',
        default => 'bg-terracotta-500',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-2 py-0.5 text-[10px]',
        'lg' => 'px-3 py-1 text-sm',
        default => 'px-2.5 py-0.5 text-xs',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 font-semibold rounded-md border $variantClasses $sizeClasses transition"]) }}>
    @if ($dot)
        <span class="w-1.5 h-1.5 rounded-full {{ $dotColor }}"></span>
    @endif
    <span>{{ $slot }}</span>
</span>
