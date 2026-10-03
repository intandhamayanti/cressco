@props([
    'checked' => false,
    'indeterminate' => false,
    'disabled' => false,
    'name' => null,
    'id' => null,
    'value' => '1',
    'label' => null,
    'size' => 'md', // 'sm', 'md', 'lg'
    'state' => null, // 'default', 'checked', 'indeterminate', 'disabled', 'disabled-checked', 'disabled-indeterminate'
])

@php
    $id = $id ?? ($name ? $name : 'checkbox-' . uniqid());
    
    // Figma preview states
    if ($state === 'checked') {
        $checked = true;
    } elseif ($state === 'indeterminate') {
        $indeterminate = true;
    } elseif ($state === 'disabled') {
        $disabled = true;
    } elseif ($state === 'disabled-checked') {
        $disabled = true;
        $checked = true;
    } elseif ($state === 'disabled-indeterminate') {
        $disabled = true;
        $indeterminate = true;
    }

    $boxSize = match($size) {
        'sm' => 'w-4 h-4 rounded-[4px]',
        'lg' => 'w-6 h-6 rounded-md',
        default => 'w-5 h-5 rounded-[5px]',
    };

    $iconSize = match($size) {
        'sm' => 'w-3 h-3',
        'lg' => 'w-4 h-4',
        default => 'w-3.5 h-3.5',
    };
@endphp

<label for="{{ $id }}" class="inline-flex items-center gap-2.5 select-none {{ $disabled ? 'cursor-not-allowed opacity-60' : 'cursor-pointer group' }}">
    <div class="relative flex items-center justify-center">
        <input
            type="checkbox"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $value }}"
            {{ $checked ? 'checked' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'sr-only peer']) }}
        />

        <!-- Default unchecked box -->
        <div class="{{ $boxSize }} border transition-all duration-150 flex items-center justify-center
            {{ ($checked || $indeterminate)
                ? ($disabled ? 'bg-terracotta-300 border-terracotta-300 text-white' : 'bg-terracotta-500 border-terracotta-500 text-white shadow-2xs group-hover:bg-terracotta-600 group-hover:border-terracotta-600') 
                : ($disabled ? 'border-gray-200 bg-gray-100' : 'border-gray-300 bg-white group-hover:border-gray-400') }}">
            
            @if ($checked)
                <svg class="{{ $iconSize }} text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
            @elseif ($indeterminate)
                <svg class="{{ $iconSize }} text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 12h14" />
                </svg>
            @endif
        </div>
    </div>

    @if ($label)
        <span class="text-sm {{ $disabled ? 'text-gray-400' : 'text-gray-700 group-hover:text-gray-900' }}">
            {{ $label }}
        </span>
    @endif
</label>
