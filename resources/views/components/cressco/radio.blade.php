@props([
    'checked' => false,
    'disabled' => false,
    'name' => null,
    'id' => null,
    'value' => '',
    'label' => null,
    'size' => 'md', // 'sm', 'md', 'lg'
    'state' => null, // 'default', 'selected', 'disabled', 'disabled-selected'
])

@php
    $id = $id ?? ($name ? $name . '-' . uniqid() : 'radio-' . uniqid());

    if ($state === 'selected') {
        $checked = true;
    } elseif ($state === 'disabled') {
        $disabled = true;
    } elseif ($state === 'disabled-selected') {
        $disabled = true;
        $checked = true;
    }

    $outerSize = match($size) {
        'sm' => 'w-4 h-4',
        'lg' => 'w-6 h-6',
        default => 'w-5 h-5',
    };

    $dotSize = match($size) {
        'sm' => 'w-1.5 h-1.5',
        'lg' => 'w-2.5 h-2.5',
        default => 'w-2 h-2',
    };
@endphp

<label for="{{ $id }}" class="inline-flex items-center gap-2.5 select-none {{ $disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer group' }}">
    <div class="relative flex items-center justify-center">
        <input
            type="radio"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'sr-only peer']) }}
        />

        <!-- Radio Outer Circle -->
        <div class="{{ $outerSize }} rounded-full border transition-all duration-150 flex items-center justify-center
            {{ $checked 
                ? ($disabled ? 'border-terracotta-300 bg-white' : 'border-terracotta-500 bg-white group-hover:border-terracotta-600') 
                : ($disabled ? 'border-gray-200 bg-gray-100' : 'border-gray-300 bg-white group-hover:border-gray-400') }}">
            
            @if ($checked)
                <div class="{{ $dotSize }} rounded-full {{ $disabled ? 'bg-terracotta-300' : 'bg-terracotta-500' }}"></div>
            @endif
        </div>
    </div>

    @if ($label)
        <span class="text-sm {{ $disabled ? 'text-gray-400' : 'text-gray-700 group-hover:text-gray-900' }}">
            {{ $label }}
        </span>
    @endif
</label>
