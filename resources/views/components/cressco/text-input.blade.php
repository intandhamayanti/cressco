@props([
    'disabled' => false,
    'label' => null,
    'helper' => null,
    'error' => null,
    'leadingIcon' => null,
    'trailingIcon' => null,
    'countryCode' => null,
    'countryFlag' => '🇺🇸',
    'id' => null,
    'name' => null,
    'type' => 'text',
    'placeholder' => '',
    'value' => null,
    'required' => false,
    'state' => null, // 'default', 'focus', 'filled', 'disabled', 'error' for previewing
])

@php
    $id = $id ?? ($name ? $name : 'input-' . uniqid());
    $hasError = !empty($error) || $state === 'error';
    $isDisabled = $disabled || $state === 'disabled';
    $isFocus = $state === 'focus';
    
    // Border & Focus Classes according to Cressco Design System
    $borderClasses = 'border-gray-300 focus-within:border-terracotta-500 focus-within:ring-1 focus-within:ring-terracotta-500';
    if ($hasError) {
        $borderClasses = 'border-error-500 focus-within:border-error-500 focus-within:ring-1 focus-within:ring-error-500';
    } elseif ($isFocus) {
        $borderClasses = 'border-terracotta-500 ring-1 ring-terracotta-500';
    } elseif ($isDisabled) {
        $borderClasses = 'border-gray-200 bg-gray-50/80 cursor-not-allowed';
    }
@endphp

<div {{ $attributes->only('class')->merge(['class' => 'w-full space-y-1.5 font-sans']) }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium {{ $isDisabled ? 'text-gray-400' : 'text-gray-700' }}">
            {{ $label }}
            @if ($required)
                <span class="text-terracotta-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative flex items-center rounded-lg border bg-white shadow-2xs transition duration-150 {{ $borderClasses }} {{ $isDisabled ? 'bg-gray-50' : '' }}">
        
        <!-- Country Code Variant -->
        @if ($countryCode)
            <div class="flex items-center gap-1.5 pl-3 pr-2 py-2 border-r border-gray-200 {{ $isDisabled ? 'text-gray-400' : 'text-gray-700' }}">
                <span class="text-base leading-none">{{ $countryFlag }}</span>
                <span class="text-sm font-medium">{{ $countryCode }}</span>
                <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
            </div>
        @elseif ($leadingIcon)
            <div class="pl-3.5 pr-1 flex items-center pointer-events-none text-gray-400 {{ $hasError ? 'text-error-500' : '' }}">
                @if ($leadingIcon === 'mail')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                @elseif ($leadingIcon === 'search')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                @elseif ($leadingIcon === 'lock')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                @elseif ($leadingIcon === 'user')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                @elseif ($leadingIcon === 'document')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                @else
                    {{ $leadingIcon }}
                @endif
            </div>
        @endif

        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            placeholder="{{ $placeholder }}"
            value="{{ $value }}"
            {{ $isDisabled ? 'disabled' : '' }}
            {{ $required ? 'required' : '' }}
            {{ $attributes->except('class') }}
            class="w-full bg-transparent px-3.5 py-2.5 text-sm text-gray-900 placeholder-gray-400 focus:outline-hidden disabled:text-gray-400 disabled:cursor-not-allowed border-0 focus:ring-0"
        />

        <!-- Trailing Icon / Action -->
        @if ($trailingIcon)
            <div class="pr-3.5 pl-1 flex items-center text-gray-400">
                @if ($trailingIcon === 'help')
                    <svg class="w-4 h-4 hover:text-gray-600 cursor-pointer" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif ($trailingIcon === 'clear')
                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-hidden">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                @elseif ($trailingIcon === 'sort')
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                @elseif ($trailingIcon === 'eye')
                    <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-hidden">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </button>
                @else
                    {{ $trailingIcon }}
                @endif
            </div>
        @endif
    </div>

    @if ($hasError)
        <p class="flex items-center gap-1.5 text-xs text-error-600 font-normal mt-1">
            <svg class="w-3.5 h-3.5 shrink-0 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10" stroke-width="1.8"/>
                <line x1="12" y1="8" x2="12" y2="12" stroke-width="1.8" stroke-linecap="round"/>
                <line x1="12" y1="16" x2="12.01" y2="16" stroke-width="2" stroke-linecap="round"/>
            </svg>
            <span>{{ $error ?? 'This is a hint text to help user.' }}</span>
        </p>
    @elseif ($helper)
        <p class="text-xs text-gray-500 font-normal mt-1">{{ $helper }}</p>
    @endif
</div>
