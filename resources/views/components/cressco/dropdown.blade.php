@props([
    'label' => null,
    'placeholder' => 'Pilih Opsi',
    'helper' => null,
    'error' => null,
    'disabled' => false,
    'variant' => 'single', // 'single', 'multi', 'radio'
    'options' => [],
    'selected' => null,
    'id' => null,
    'name' => null,
    'size' => 'md', // 'sm', 'md'
    'autoSubmit' => false,
])

@php
    $id = $id ?? ($name ? $name : 'dropdown-' . uniqid());
    $isDisabled = (bool) $disabled;
    $hasError = !empty($error);

    // Normalize options
    $formattedOptions = [];
    foreach ($options as $opt) {
        if (is_array($opt)) {
            $formattedOptions[] = [
                'value' => (string) ($opt['value'] ?? ''),
                'label' => (string) ($opt['label'] ?? ($opt['value'] ?? '')),
            ];
        } else {
            $formattedOptions[] = ['value' => (string) $opt, 'label' => (string) $opt];
        }
    }

    if (empty($formattedOptions)) {
        $formattedOptions = [
            ['value' => 'Option 1', 'label' => 'Option 1'],
            ['value' => 'Option 2', 'label' => 'Option 2'],
            ['value' => 'Option 3', 'label' => 'Option 3'],
            ['value' => 'Option 4', 'label' => 'Option 4'],
        ];
    }

    $initialSelected = $selected;

    // Precalculate server-side label fallback
    $selectedLabelFallback = $placeholder;
    if ($initialSelected !== null && !is_array($initialSelected)) {
        foreach ($formattedOptions as $opt) {
            if ((string)$opt['value'] === (string)$initialSelected) {
                $selectedLabelFallback = $opt['label'];
                break;
            }
        }
    } elseif (is_array($initialSelected) && !empty($initialSelected)) {
        $selectedLabelFallback = count($initialSelected) . ' opsi dipilih';
    }

    $sizeClasses = match($size) {
        'sm' => 'min-h-[36px] py-1.5 px-3 text-xs font-semibold rounded-xl',
        default => 'min-h-[42px] py-2 px-3.5 text-sm rounded-lg',
    };

    $itemSizeClasses = match($size) {
        'sm' => 'py-2 px-3 text-xs font-semibold',
        default => 'py-2.5 px-3.5 text-sm font-medium',
    };
@endphp

<div
    x-data="{
        open: false,
        disabled: @js($isDisabled),
        variant: @js($variant),
        options: @js($formattedOptions),
        selected: @js(is_array($initialSelected) ? $initialSelected : ($initialSelected ? [$initialSelected] : [])),
        singleValue: @js($initialSelected !== null && !is_array($initialSelected) ? (string)$initialSelected : null),
        autoSubmit: @js($autoSubmit),
        toggle() {
            if (!this.disabled) {
                this.open = !this.open;
            }
        },
        selectSingle(val) {
            this.singleValue = String(val);
            this.open = false;
            this.$dispatch('change', this.singleValue);
            if (this.autoSubmit) {
                this.$nextTick(() => {
                    this.$el.closest('form')?.submit();
                });
            }
        },
        getSingleLabel() {
            if (this.singleValue === null || this.singleValue === undefined || this.singleValue === '') return '{{ $placeholder }}';
            const opt = this.options.find(o => String(o.value) === String(this.singleValue));
            return opt ? opt.label : this.singleValue;
        },
        toggleMulti(val) {
            const index = this.selected.indexOf(val);
            if (index > -1) {
                this.selected.splice(index, 1);
            } else {
                this.selected.push(val);
            }
        },
        removeChip(val) {
            const index = this.selected.indexOf(val);
            if (index > -1) {
                this.selected.splice(index, 1);
            }
        },
        isSelected(val) {
            if (this.variant === 'single' || this.variant === 'radio') {
                return String(this.singleValue) === String(val);
            }
            return this.selected.includes(val);
        }
    }"
    @click.outside="open = false"
    class="relative w-full space-y-1.5 font-sans"
>
    @if ($label)
        <label class="block text-sm font-medium {{ $isDisabled ? 'text-gray-400' : 'text-gray-700' }}">
            {{ $label }}
        </label>
    @endif

    @if ($name)
        <input type="hidden" name="{{ $name }}" :value="singleValue">
    @endif

    <!-- Trigger Button -->
    <div
        @click="toggle()"
        :class="{
            'border-terracotta-500 ring-1 ring-terracotta-500': open,
            'border-gray-200 hover:border-gray-300': !open && !disabled,
            'border-gray-200 bg-gray-50/80 cursor-not-allowed': disabled
        }"
        class="w-full flex items-center justify-between border bg-white shadow-2xs cursor-pointer transition duration-150 {{ $sizeClasses }} {{ $isDisabled ? 'border-gray-200 bg-gray-50/80 cursor-not-allowed' : '' }}"
    >
        <!-- Value / Tags Display -->
        <div class="flex flex-wrap items-center gap-1.5 overflow-hidden flex-1 mr-2">
            @if ($variant === 'multi')
                <template x-if="selected.length === 0">
                    <span class="text-gray-400">{{ $placeholder }}</span>
                </template>

                <template x-for="chip in selected" :key="chip">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 text-gray-800 text-xs font-medium border border-gray-200">
                        <span x-text="chip"></span>
                        <button type="button" @click.stop="removeChip(chip)" :disabled="disabled" class="text-gray-400 hover:text-gray-600 focus:outline-hidden">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                </template>

                @if ($isDisabled && is_array($initialSelected))
                    @foreach($initialSelected as $chip)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-gray-100 text-gray-400 text-xs font-medium border border-gray-200">
                            <span>{{ $chip }}</span>
                            <span class="text-gray-300">×</span>
                        </span>
                    @endforeach
                @endif
            @else
                <!-- Single & Radio -->
                <span :class="disabled ? 'text-gray-400' : 'text-gray-800 truncate'" x-text="getSingleLabel()">{{ $selectedLabelFallback }}</span>
            @endif
        </div>

        <!-- Chevron Icon -->
        <div class="text-gray-400 transition-transform duration-150 shrink-0" :class="{ 'rotate-180': open }">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </div>
    </div>

    <!-- Dropdown Menu / Option List -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="transform opacity-0 scale-95"
        x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95"
        style="display: none;"
        class="absolute z-50 mt-1.5 w-full min-w-[200px] bg-white rounded-xl border border-gray-200 shadow-xl py-1 text-xs sm:text-sm overflow-hidden"
    >
        @foreach ($formattedOptions as $opt)
            @php $optVal = $opt['value']; $optLabel = $opt['label']; @endphp

            @if ($variant === 'single')
                <!-- Single Select Item with Checkmark -->
                <div
                    @click="selectSingle('{{ addslashes($optVal) }}')"
                    :class="{
                        'bg-terracotta-50/80 text-terracotta-800 font-bold': singleValue === '{{ addslashes($optVal) }}',
                        'text-gray-700 hover:bg-gray-50': singleValue !== '{{ addslashes($optVal) }}'
                    }"
                    class="flex items-center justify-between {{ $itemSizeClasses }} cursor-pointer transition"
                >
                    <span class="truncate">{{ $optLabel }}</span>
                    <span x-show="singleValue === '{{ addslashes($optVal) }}'">
                        <svg class="w-4 h-4 text-terracotta-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </span>
                </div>

            @elseif ($variant === 'multi')
                <!-- Multi Select Item with Checkbox -->
                <div
                    @click="toggleMulti('{{ addslashes($optVal) }}')"
                    class="flex items-center gap-2.5 {{ $itemSizeClasses }} cursor-pointer hover:bg-gray-50 text-gray-700 transition"
                >
                    <div
                        :class="isSelected('{{ addslashes($optVal) }}') ? 'bg-terracotta-500 border-terracotta-500 text-white' : 'border-gray-300 bg-white'"
                        class="w-4 h-4 rounded border flex items-center justify-center transition shrink-0"
                    >
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-gray-800 truncate">{{ $optLabel }}</span>
                </div>

            @elseif ($variant === 'radio')
                <!-- Radio Selection Item -->
                <div
                    @click="selectSingle('{{ addslashes($optVal) }}')"
                    class="flex items-center gap-2.5 {{ $itemSizeClasses }} cursor-pointer hover:bg-gray-50 text-gray-700 transition"
                >
                    <div
                        :class="singleValue === '{{ addslashes($optVal) }}' ? 'border-terracotta-500' : 'border-gray-300'"
                        class="w-4 h-4 rounded-full border flex items-center justify-center transition shrink-0"
                    >
                        <div
                            :class="singleValue === '{{ addslashes($optVal) }}' ? 'bg-terracotta-500' : 'bg-transparent'"
                            class="w-2 h-2 rounded-full transition"
                        ></div>
                    </div>
                    <span class="text-gray-800 truncate">{{ $optLabel }}</span>
                </div>
            @endif
        @endforeach
    </div>

    @if ($helper)
        <p class="text-xs text-gray-500 font-normal mt-1">{{ $helper }}</p>
    @endif
</div>
