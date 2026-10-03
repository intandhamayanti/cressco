@props([
    'checked' => false,
    'disabled' => false,
    'name' => null,
    'id' => null,
    'value' => '1',
    'label' => null,
    'size' => 'md', // 'sm', 'md', 'lg'
    'state' => null, // 'default', 'checked', 'disabled', 'disabled-checked'
])

@php
    $id = $id ?? ($name ? $name . '-' . uniqid() : 'toggle-' . uniqid());

    if ($state === 'checked' || $state === 'on') {
        $checked = true;
    } elseif ($state === 'disabled') {
        $disabled = true;
    } elseif ($state === 'disabled-checked' || $state === 'disabled-on') {
        $disabled = true;
        $checked = true;
    }

    $switchTrack = match($size) {
        'sm' => 'w-8 h-4',
        'lg' => 'w-12 h-6.5',
        default => 'w-11 h-6',
    };

    $thumbSize = match($size) {
        'sm' => 'w-3 h-3',
        'lg' => 'w-5.5 h-5.5',
        default => 'w-5 h-5',
    };

    $translateClass = match($size) {
        'sm' => $checked ? 'translate-x-4' : 'translate-x-0.5',
        'lg' => $checked ? 'translate-x-5.5' : 'translate-x-0.5',
        default => $checked ? 'translate-x-5' : 'translate-x-0.5',
    };
@endphp

<label for="{{ $id }}" class="inline-flex items-center gap-3 select-none {{ $disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}">
    <div class="relative inline-flex items-center">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'sr-only peer']) }}
        />

        <!-- Switch Track -->
        <div class="{{ $switchTrack }} rounded-full transition-colors duration-200 ease-in-out flex items-center p-0.5
            {{ $checked 
                ? ($disabled ? 'bg-terracotta-300' : 'bg-terracotta-500 hover:bg-terracotta-600') 
                : ($disabled ? 'bg-gray-200' : 'bg-gray-200 hover:bg-gray-300') }}">
            
            <!-- Switch Thumb -->
            <div class="{{ $thumbSize }} rounded-full bg-white shadow-xs transform transition-transform duration-200 ease-in-out {{ $translateClass }}"></div>
        </div>
    </div>

    @if ($label)
        <span class="text-sm {{ $disabled ? 'text-gray-400' : 'text-gray-700' }}">
            {{ $label }}
        </span>
    @endif
</label>
