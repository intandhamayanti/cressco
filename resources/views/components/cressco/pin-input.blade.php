@props([
    'length' => 4,
    'name' => 'pin',
    'id' => null,
    'label' => null,
    'helper' => null,
    'error' => null,
    'disabled' => false,
    'values' => [],
    'state' => null, // 'default', 'filled', 'error', 'disabled'
])

@php
    $id = $id ?? 'pin-' . uniqid();
    $length = (int) $length;
    $hasError = !empty($error) || $state === 'error';
    $isDisabled = $disabled || $state === 'disabled';
    
    // Normalize prefilled values
    $initialValues = array_pad($values, $length, '');
    if ($state === 'filled' && empty($values)) {
        $initialValues = ['1', '2', '3', '4'];
    }
@endphp

<div
    x-data="{
        length: {{ $length }},
        values: {{ json_encode($initialValues) }},
        handleInput(e, index) {
            const val = e.target.value;
            const digit = val.replace(/\D/g, '').slice(-1);
            this.values[index] = digit;
            e.target.value = digit;

            if (digit && index < this.length - 1) {
                const nextInput = this.$refs['input_' + (index + 1)];
                if (nextInput) nextInput.focus();
            }
        },
        handleKeydown(e, index) {
            if (e.key === 'Backspace') {
                if (!this.values[index] && index > 0) {
                    const prevInput = this.$refs['input_' + (index - 1)];
                    if (prevInput) {
                        prevInput.focus();
                        this.values[index - 1] = '';
                        prevInput.value = '';
                    }
                } else {
                    this.values[index] = '';
                    e.target.value = '';
                }
            } else if (e.key === 'ArrowLeft' && index > 0) {
                const prevInput = this.$refs['input_' + (index - 1)];
                if (prevInput) prevInput.focus();
            } else if (e.key === 'ArrowRight' && index < this.length - 1) {
                const nextInput = this.$refs['input_' + (index + 1)];
                if (nextInput) nextInput.focus();
            }
        },
        handlePaste(e) {
            e.preventDefault();
            const pastedData = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, this.length);
            for (let i = 0; i < pastedData.length; i++) {
                this.values[i] = pastedData[i];
                if (this.$refs['input_' + i]) {
                    this.$refs['input_' + i].value = pastedData[i];
                }
            }
            const focusIndex = Math.min(pastedData.length, this.length - 1);
            if (this.$refs['input_' + focusIndex]) {
                this.$refs['input_' + focusIndex].focus();
            }
        }
    }"
    class="space-y-2 font-sans"
>
    @if ($label)
        <label class="block text-sm font-medium {{ $isDisabled ? 'text-gray-400' : 'text-gray-700' }}">
            {{ $label }}
        </label>
    @endif

    <div class="flex items-center gap-3">
        @for ($i = 0; $i < $length; $i++)
            @php
                $val = $initialValues[$i] ?? '';
                
                $borderClass = 'border-gray-300 text-gray-900 bg-white focus:border-terracotta-500 focus:ring-2 focus:ring-terracotta-500/20';
                if ($hasError) {
                    $borderClass = 'border-error-500 text-error-600 bg-white focus:border-error-500 focus:ring-2 focus:ring-error-500/20';
                } elseif ($isDisabled) {
                    $borderClass = 'border-gray-200 bg-gray-50 text-gray-400 cursor-not-allowed';
                }
            @endphp

            <div class="relative w-14 h-16 sm:w-16 sm:h-20">
                <input
                    x-ref="input_{{ $i }}"
                    type="text"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    maxlength="1"
                    value="{{ $val }}"
                    @input="handleInput($event, {{ $i }})"
                    @keydown="handleKeydown($event, {{ $i }})"
                    @paste="handlePaste($event)"
                    {{ $isDisabled ? 'disabled' : '' }}
                    class="w-full h-full text-center text-2xl font-bold rounded-xl border shadow-2xs transition duration-150 focus:outline-hidden {{ $borderClass }}"
                />
            </div>
        @endfor
    </div>

    @if ($hasError)
        <p class="text-xs text-error-600 font-normal mt-1 flex items-center gap-1">
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
