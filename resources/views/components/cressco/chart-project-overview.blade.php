@props([
    'title' => 'Performa Keuangan: Revenue vs Expenses',
    'subtitle' => 'Tren pendapatan, beban honor tutor, dan estimasi laba bulanan',
    'period' => 'Monthly',
    'data' => null,
    'showToggle' => true,
    'showLegend' => true,
    'legend1' => 'Revenue',
    'legend2' => 'Expenses',
    'isCurrency' => true,
])

@php
    $defaultData = [
        ['label' => 'Jan', 'val1' => 22, 'val2' => 8, 'val3' => 14],
        ['label' => 'Feb', 'val1' => 24, 'val2' => 6, 'val3' => 18],
        ['label' => 'Mar', 'val1' => 20, 'val2' => 8, 'val3' => 12],
        ['label' => 'Apr', 'val1' => 26, 'val2' => 5, 'val3' => 21],
        ['label' => 'May', 'val1' => 24, 'val2' => 10, 'val3' => 14],
        ['label' => 'Jun', 'val1' => 35, 'val2' => 20, 'val3' => 15],
        ['label' => 'Jul', 'val1' => 23, 'val2' => 8, 'val3' => 15],
        ['label' => 'Aug', 'val1' => 25, 'val2' => 7, 'val3' => 18],
        ['label' => 'Sep', 'val1' => 26, 'val2' => 8, 'val3' => 18],
        ['label' => 'Oct', 'val1' => 24, 'val2' => 6, 'val3' => 18],
    ];

    $chartData = $data ?? $defaultData;
    $currentMonthIdx = now()->month - 1;
    $maxVal = $isCurrency ? 50 : 35;
@endphp

<div x-data="{ currentPeriod: '{{ $period }}', activeIndex: {{ $currentMonthIdx }}, hoveredIndex: null }"
     {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-6 font-sans select-none']) }}>
    
    <!-- Chart Header: Title/Subtitle (Left) and Legend (Top Right) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $title }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
        </div>

        @if ($showLegend)
            <!-- Legend (Top Right) -->
            <div class="flex items-center gap-4 text-xs font-medium self-start sm:self-center">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-terracotta-500"></span>
                    <span class="text-gray-700 font-semibold">{{ $legend1 }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-800"></span>
                    <span class="text-gray-700 font-semibold">{{ $legend2 }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Chart Main Body with Separated Y-Axis Column and Bars Area -->
    <div class="flex items-stretch gap-2 pt-2">
        
        <!-- Y-Axis Column (Fixed on the Left - Never Overlaps Bars!) -->
        <div class="w-10 sm:w-12 shrink-0 flex flex-col justify-between text-right pr-3 text-[11px] text-gray-400 font-mono select-none h-56 pb-7">
            @if ($isCurrency)
                <div>50M</div>
                <div>35M</div>
                <div>20M</div>
                <div class="font-bold text-gray-600">0M</div>
                <div>-10M</div>
            @else
                <div>30</div>
                <div>20</div>
                <div>10</div>
                <div class="font-bold text-gray-600">00</div>
                <div>-10</div>
            @endif
        </div>

        <!-- Right Side: Grid Lines Background + Dynamic Bars -->
        <div class="relative flex-1">
            
            <!-- Horizontal Grid Lines -->
            <div class="absolute inset-x-0 inset-y-0 flex flex-col justify-between pointer-events-none pb-7">
                <div class="border-b border-gray-100 w-full"></div>
                <div class="border-b border-gray-100 w-full"></div>
                <div class="border-b border-gray-100 w-full"></div>
                <div class="border-b border-gray-200 border-dashed w-full"></div>
                <div class="border-b border-gray-100 w-full"></div>
            </div>

            <!-- Dynamic Bars -->
            <div class="relative z-10 flex items-center justify-between sm:justify-around gap-1.5 sm:gap-2 h-56 px-2"
                 @mouseleave="hoveredIndex = null">
                @foreach ($chartData as $index => $item)
                    @php
                        $isCurrentMonth = ($index === $currentMonthIdx);
                        $val1 = (float) ($item['val1'] ?? 0);
                        $val2 = (float) ($item['val2'] ?? 0);
                        // Scale calculations
                        $topHeight = min(110, max(8, ($val1 / $maxVal) * 105));
                        $botHeight = min(48, max(6, ($val2 / $maxVal) * 44));
                    @endphp
                    <div class="flex-1 max-w-[48px] flex flex-col items-center justify-center h-full group relative cursor-pointer"
                         @mouseenter="hoveredIndex = {{ $index }}">
                        
                        <!-- Floating Tooltip on Hover / Active Month -->
                        <div x-show="hoveredIndex === {{ $index }} || (hoveredIndex === null && activeIndex === {{ $index }})"
                             x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute -top-24 z-40 bg-white/95 backdrop-blur-xs border border-gray-200/90 rounded-2xl p-3 shadow-xl whitespace-nowrap text-left text-xs pointer-events-none min-w-[150px]">
                            <div class="font-bold text-gray-900 border-b border-gray-100 pb-1.5 mb-2 text-xs flex items-center justify-between gap-3">
                                <span>{{ $item['label'] }} {{ now()->year }}</span>
                                @if ($isCurrentMonth)
                                    <span class="text-[9px] px-1.5 py-0.5 bg-terracotta-50 text-terracotta-700 border border-terracotta-200 rounded font-semibold">Bulan Ini</span>
                                @endif
                            </div>

                            @if ($isCurrency)
                                <div class="space-y-1.5 text-[11px]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-1.5 text-gray-600">
                                            <span class="w-2 h-2 rounded-full bg-terracotta-500 shrink-0"></span>
                                            <span>{{ $legend1 }}</span>
                                        </div>
                                        <span class="font-bold text-gray-900">Rp {{ number_format($item['raw_revenue'] ?? ($val1 * 1000000), 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-1.5 text-gray-600">
                                            <span class="w-2 h-2 rounded-full bg-slate-800 shrink-0"></span>
                                            <span>{{ $legend2 }}</span>
                                        </div>
                                        <span class="font-bold text-gray-900">Rp {{ number_format($item['raw_expenses'] ?? ($val2 * 1000000), 0, ',', '.') }}</span>
                                    </div>
                                    @if (isset($item['raw_profit']))
                                        <div class="flex items-center justify-between gap-3 pt-1 border-t border-gray-100 text-emerald-600 font-semibold">
                                            <div class="flex items-center gap-1.5">
                                                <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                                <span>Estimasi Laba</span>
                                            </div>
                                            <span>Rp {{ number_format($item['raw_profit'], 0, ',', '.') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="space-y-1.5 text-[11px]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-1.5 text-gray-600">
                                            <span class="w-2 h-2 rounded-full bg-terracotta-500 shrink-0"></span>
                                            <span>{{ $legend1 }}</span>
                                        </div>
                                        <span class="font-bold text-gray-900">{{ $val1 }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-1.5 text-gray-600">
                                            <span class="w-2 h-2 rounded-full bg-slate-800 shrink-0"></span>
                                            <span>{{ $legend2 }}</span>
                                        </div>
                                        <span class="font-bold text-gray-900">{{ $val2 }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Top Bar (Revenue - Terracotta Gradient with Solid Hover/Active) -->
                        <div class="w-full max-w-[28px] rounded-t-lg transition-all duration-200"
                             :class="(hoveredIndex === {{ $index }} || (hoveredIndex === null && activeIndex === {{ $index }})) ? 'bg-[#B9381E] shadow-sm' : 'bg-gradient-to-t from-[#E26649] to-[#F1937C] opacity-80 group-hover:opacity-100'"
                             style="height: {{ $topHeight }}px;">
                        </div>

                        <!-- Center Baseline Divider -->
                        <div class="w-full h-[2px] transition-colors duration-200"
                             :class="(hoveredIndex === {{ $index }} || (hoveredIndex === null && activeIndex === {{ $index }})) ? 'bg-[#B9381E]' : 'bg-gray-200'"></div>

                        <!-- Bottom Bar (Expenses - Slate/Gray Gradient with Dark Solid Hover/Active) -->
                        <div class="w-full max-w-[28px] rounded-b-lg transition-all duration-200"
                             :class="(hoveredIndex === {{ $index }} || (hoveredIndex === null && activeIndex === {{ $index }})) ? 'bg-[#202938] shadow-sm' : 'bg-gradient-to-b from-gray-200 to-gray-100 group-hover:bg-gray-300'"
                             style="height: {{ $botHeight }}px;">
                        </div>

                        <!-- Month Label -->
                        <span class="text-xs mt-3 transition-colors duration-200"
                              :class="(hoveredIndex === {{ $index }} || (hoveredIndex === null && activeIndex === {{ $index }})) ? 'text-[#B9381E] font-bold' : 'text-gray-400 group-hover:text-gray-700 font-medium'">
                            {{ $item['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

</div>
