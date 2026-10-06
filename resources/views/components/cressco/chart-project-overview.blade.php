@props([
    'title' => 'Performa Keuangan: Revenue vs Expenses',
    'subtitle' => 'Tren pendapatan, beban honor tutor, dan estimasi laba bulanan tahun ' . now()->year,
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

    $chartData = (!empty($data) && is_array($data) && count($data) > 0) ? $data : $defaultData;
    $currentMonthIdx = now()->month - 1;

    // Calculate max value dynamically for proportional scaling
    $peakVal1 = 0;
    $peakVal2 = 0;
    foreach ($chartData as $row) {
        $peakVal1 = max($peakVal1, (float) ($row['val1'] ?? 0));
        $peakVal2 = max($peakVal2, (float) ($row['val2'] ?? 0));
    }

    $rawPeak = max($peakVal1, $peakVal2);
    if ($isCurrency) {
        if ($rawPeak > 100) {
            $maxVal = ceil($rawPeak / 50) * 50;
        } elseif ($rawPeak > 50) {
            $maxVal = ceil($rawPeak / 25) * 25;
        } elseif ($rawPeak > 0) {
            $maxVal = 50;
        } else {
            $maxVal = 50;
        }
        $step3 = round($maxVal);
        $step2 = round($maxVal * 0.7);
        $step1 = round($maxVal * 0.4);
        $step0 = 0;
        $stepNeg = -round($maxVal * 0.2);
    } else {
        $maxVal = max(35, ceil($rawPeak / 5) * 5);
        $step3 = round($maxVal);
        $step2 = round($maxVal * 0.67);
        $step1 = round($maxVal * 0.33);
        $step0 = 0;
        $stepNeg = -round($maxVal * 0.2);
    }
@endphp

<div x-data="{
        currentPeriod: '{{ $period }}',
        hoveredIndex: null,
        hoveredTopHeight: 0,
        updateHover(idx, topH) {
            this.hoveredIndex = idx;
            this.hoveredTopHeight = topH;
        }
     }"
     {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-gray-200/80 p-6 sm:p-7 shadow-xs flex flex-col justify-between select-none relative font-sans']) }}>
    
    <!-- 1. Header: Title/Subtitle (Left) & Minimal Dot Legend (Right) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base sm:text-lg font-bold text-gray-900 tracking-tight">{{ $title }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
        </div>

        @if ($showLegend)
            <!-- Clean Dot Legend (Right) matching Figma Reference -->
            <div class="flex items-center gap-4 text-xs font-medium self-start sm:self-center">
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-terracotta-500 shadow-2xs"></span>
                    <span class="text-gray-700 font-semibold">{{ $legend1 }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-800 shadow-2xs"></span>
                    <span class="text-gray-700 font-semibold">{{ $legend2 }}</span>
                </div>
            </div>
        @endif
    </div>

    <!-- 2. Chart Workspace (Y-Axis + Grid Lines + Baseline Locked Columns) -->
    <div class="flex items-stretch gap-2 pt-4">
        
        <!-- Y-Axis Fixed Left Column -->
        <div class="w-9 sm:w-11 shrink-0 flex flex-col justify-between text-right pr-2 text-[11px] font-medium text-gray-400 font-mono select-none h-[210px] pb-2">
            @if ($isCurrency)
                <div>{{ $step3 }}M</div>
                <div>{{ $step2 }}M</div>
                <div>{{ $step1 }}M</div>
                <div class="font-bold text-gray-700">0M</div>
                <div>{{ $stepNeg }}M</div>
            @else
                <div>{{ $step3 }}</div>
                <div>{{ $step2 }}</div>
                <div>{{ $step1 }}</div>
                <div class="font-bold text-gray-700">00</div>
                <div>{{ $stepNeg }}</div>
            @endif
        </div>

        <!-- Chart Grid & Dynamic Capsule Columns -->
        <div class="relative flex-1">
            
            <!-- Horizontal Grid Lines (Subtle Minimalist Dash with Solid 0-Baseline) -->
            <div class="absolute inset-x-0 top-0 h-[210px] flex flex-col justify-between pointer-events-none pb-2">
                <div class="border-b border-gray-100 border-dashed w-full"></div> <!-- 50M -->
                <div class="border-b border-gray-100 border-dashed w-full"></div> <!-- 35M -->
                <div class="border-b border-gray-100 border-dashed w-full"></div> <!-- 20M -->
                <div class="border-b border-gray-200 w-full shadow-2xs"></div> <!-- 0M Baseline -->
                <div class="border-b border-gray-100 border-dashed w-full"></div> <!-- -10M -->
            </div>

            <!-- Dynamic Laser Apex Guideline on Hover -->
            <div x-show="hoveredIndex !== null"
                 x-cloak
                 class="absolute inset-x-0 border-b border-dashed border-terracotta-400/90 pointer-events-none z-20 transition-all duration-150"
                 :style="`top: ${155 - hoveredTopHeight}px;`">
            </div>

            <!-- Columns Layout Container -->
            <div class="relative z-10 flex items-stretch justify-between sm:justify-around gap-1.5 sm:gap-2.5 h-[245px] px-1"
                 @mouseleave="hoveredIndex = null">
                @foreach ($chartData as $index => $item)
                    @php
                        $isCurrentMonth = ($index === $currentMonthIdx);
                        $val1 = (float) ($item['val1'] ?? 0);
                        $val2 = (float) ($item['val2'] ?? 0);

                        // Precision scale calculations anchored at baseline (0M at 155px)
                        $maxUpperPx = 140; // Max height for positive revenue zone
                        $maxLowerPx = 45;  // Max height for negative expense zone

                        $topHeight = $val1 > 0 ? min($maxUpperPx, max(12, (int) round(($val1 / $maxVal) * $maxUpperPx))) : 3;
                        $botHeight = $val2 > 0 ? min($maxLowerPx, max(8, (int) round(($val2 / $maxVal) * $maxLowerPx * 1.8))) : 0;
                        
                        $rawRev = $item['raw_revenue'] ?? ($val1 * 1000000);
                        $rawExp = $item['raw_expenses'] ?? ($val2 * 1000000);
                        $rawProf = $item['raw_profit'] ?? max(0, $rawRev - $rawExp);
                    @endphp
                    
                    <!-- Single Column Unit: Locked at 0 Baseline with Upper & Lower Bar -->
                    <div class="flex-1 max-w-[48px] flex flex-col items-center h-full group relative cursor-pointer"
                         @mouseenter="updateHover({{ $index }}, {{ $topHeight }})">
                        
                        <!-- Floating Glassmorphism HUD Tooltip (Hover Only) -->
                        <div x-show="hoveredIndex === {{ $index }}"
                             x-cloak
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                             class="absolute -top-28 z-40 bg-white/95 backdrop-blur-xl border border-gray-200/90 rounded-2xl p-4 shadow-[0_20px_45px_-10px_rgba(0,0,0,0.15),0_1px_3px_rgba(0,0,0,0.05)] whitespace-nowrap text-left text-xs pointer-events-none min-w-[190px]">
                            
                            <!-- Header -->
                            <div class="flex items-center justify-between gap-3 border-b border-gray-100 pb-2 mb-2.5">
                                <span class="font-bold text-gray-900 text-sm tracking-tight">{{ $item['label'] }} {{ now()->year }}</span>
                                @if ($isCurrentMonth)
                                    <span class="inline-flex items-center gap-1 text-[9px] px-2 py-0.5 bg-terracotta-50 text-terracotta-700 border border-terracotta-200/80 rounded-full font-bold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-terracotta-500 animate-pulse"></span>
                                        Bulan Ini
                                    </span>
                                @endif
                            </div>

                            <!-- Financial Breakdown -->
                            @if ($isCurrency)
                                <div class="space-y-2 text-[11px]">
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-1.5 text-gray-500 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-terracotta-500 shrink-0 shadow-2xs"></span>
                                            <span>{{ $legend1 }}</span>
                                        </div>
                                        <span class="font-extrabold text-gray-900">Rp {{ number_format($rawRev, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-1.5 text-gray-500 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-slate-800 shrink-0 shadow-2xs"></span>
                                            <span>{{ $legend2 }}</span>
                                        </div>
                                        <span class="font-extrabold text-gray-900">Rp {{ number_format($rawExp, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-4 pt-2 border-t border-gray-100 text-emerald-600 font-semibold bg-emerald-50/60 px-2.5 py-1.5 rounded-xl">
                                        <div class="flex items-center gap-1.5">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                                            <span>Estimasi Laba</span>
                                        </div>
                                        <span class="font-extrabold text-emerald-700">Rp {{ number_format($rawProf, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="space-y-1.5 text-[11px]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-1.5 text-gray-500 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-terracotta-500 shrink-0"></span>
                                            <span>{{ $legend1 }}</span>
                                        </div>
                                        <span class="font-bold text-gray-900">{{ $val1 }}</span>
                                    </div>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-1.5 text-gray-500 font-medium">
                                            <span class="w-2 h-2 rounded-full bg-slate-800 shrink-0"></span>
                                            <span>{{ $legend2 }}</span>
                                        </div>
                                        <span class="font-bold text-gray-900">{{ $val2 }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Upper Zone (Positive Revenue): Grows Upwards to Baseline -->
                        <div class="h-[155px] w-full flex flex-col justify-end items-center relative">
                            <!-- Apex Dot on Hover -->
                            <div x-show="hoveredIndex === {{ $index }}"
                                 x-cloak
                                 class="absolute w-3 h-3 rounded-full bg-gray-900 border-2 border-white ring-4 ring-terracotta-500/20 shadow-md z-30 pointer-events-none -translate-y-1/2 transition-all duration-150"
                                 :style="`bottom: ${ {{ $topHeight }} - 6 }px;`"></div>

                            <!-- Revenue Upper Bar (Rounded Top) -->
                            <div class="w-6 sm:w-7 rounded-t-lg transition-all duration-300 relative overflow-hidden"
                                 :class="hoveredIndex === {{ $index }}
                                    ? 'bg-terracotta-500 shadow-[0_6px_16px_-2px_rgba(204,68,32,0.45)] scale-x-105'
                                    : 'bg-gradient-to-t from-[#DE6544] to-[#E88C74] hover:brightness-105'"
                                 style="height: {{ $topHeight }}px;">
                                <!-- Micro Rim Highlight -->
                                <div class="absolute inset-x-0 top-0 h-1 bg-white/30 pointer-events-none"></div>
                            </div>
                        </div>

                        <!-- Hairline Baseline Divider -->
                        <div class="w-6 sm:w-7 h-[1.5px] bg-white z-10"></div>

                        <!-- Lower Zone (Expenses): Grows Downwards from Baseline -->
                        <div class="h-[52px] w-full flex flex-col justify-start items-center">
                            @if ($botHeight > 0)
                                <!-- Expenses Lower Bar (Rounded Bottom) -->
                                <div class="w-6 sm:w-7 rounded-b-lg transition-all duration-300 relative overflow-hidden"
                                     :class="hoveredIndex === {{ $index }}
                                        ? 'bg-slate-800 shadow-[0_4px_12px_-2px_rgba(15,23,42,0.35)] scale-x-105'
                                        : 'bg-[#CBD5E1] hover:bg-[#94A3B8]'"
                                     style="height: {{ $botHeight }}px;">
                                </div>
                            @endif
                        </div>

                        <!-- X-Axis Month Label: Strictly Fixed Row at Bottom -->
                        <div class="h-8 flex items-center justify-center pt-2 text-center">
                            <span class="transition-colors duration-150 text-xs font-semibold"
                                  :class="hoveredIndex === {{ $index }}
                                    ? 'text-terracotta-600 font-bold scale-105'
                                    : 'text-gray-400 group-hover:text-gray-700'">
                                {{ $item['label'] }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

    </div>

</div>
