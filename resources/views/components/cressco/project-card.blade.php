@props([
    'title' => 'Total Project',
    'value' => '48',
    'prefix' => null,
    'trend' => null,
    'trendType' => 'positive', // 'positive', 'negative', 'neutral', 'warning', 'terracotta'
    'badge' => null,
    'subtitle' => 'From last month',
    'icon' => 'folder',
    'iconColor' => 'text-gray-600',
    'actionIcon' => 'arrow', // 'arrow', 'chevron', 'none'
    'href' => null,
    'layout' => '3-row', // '3-row', '4-row'
])

@php
    $trendText = $trend ?? $badge;
    $isPositive = $trendType === 'positive' || ($trendText && (str_starts_with($trendText, '+') || str_starts_with($trendText, '↗')));
    $isNegative = $trendType === 'negative' || ($trendText && (str_starts_with($trendText, '-') || str_starts_with($trendText, '↘') || str_starts_with($trendText, '↙') || $trendType === 'error'));

    // Pill style matching Figma reference
    $pillStyle = match($trendType) {
        'terracotta' => 'bg-[#FCF5F3] text-[#CC4420]',
        'warning' => 'bg-[#FEF6EE] text-[#C4320A]',
        'error', 'negative' => 'bg-[#FEECEC] text-[#DC2626]',
        'neutral', 'gray' => 'bg-[#F2F4F7] text-[#475467]',
        default => $isNegative ? 'bg-[#FEECEC] text-[#DC2626]' : 'bg-[#E8F8EE] text-[#16A34A]',
    };

    // Clean formatting for trend text without duplicate prefix
    $cleanTrendText = $trendText ? ltrim($trendText, '+-↗↘↙ ') : null;
@endphp

<!-- Outer Container (White Card: #FFFFFF) -->
<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-gray-200/80 p-3 sm:p-3.5 space-y-2.5 font-sans select-none shadow-[0_1px_3px_0_rgba(16,24,40,0.04)] h-full flex flex-col justify-between']) }}>
    
    <!-- Top Row: Icon + Title (Left) and Action Arrow (Right) -->
    <div class="flex items-center justify-between px-1 text-gray-600">
        <div class="flex items-center gap-2 min-w-0">
            @if ($icon)
                <x-cressco.icon-helper :name="$icon" class="w-4 h-4 shrink-0 {{ $iconColor }}" />
            @endif
            <span class="text-xs font-semibold text-gray-700 truncate">{{ $title }}</span>
        </div>

        @if ($actionIcon === 'arrow')
            @if ($href)
                <a href="{{ $href }}" class="text-gray-400 shrink-0" aria-label="Detail">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                </a>
            @else
                <div class="text-gray-400 shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                </div>
            @endif
        @elseif ($actionIcon === 'chevron')
            <div class="text-gray-400 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </div>
        @endif
    </div>

    @if ($layout === '4-row')
        <!-- 4-Row Layout: Dedicated row for Metric, Dedicated row for Pill, Dedicated row for Subtitle -->
        <div class="bg-[#F8F9FA] rounded-xl border border-gray-100 p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-2.5 min-h-[110px]">
            <!-- Row 2: Full-Width Main Metric Value -->
            <div class="flex items-baseline gap-1">
                @if ($prefix)
                    <span class="text-sm sm:text-base font-bold text-gray-900 tracking-tight shrink-0">{{ $prefix }}</span>
                @endif
                <span class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight leading-none whitespace-nowrap">
                    {{ $value }}
                </span>
            </div>

            <!-- Row 3: Dedicated Trend / Status Pill Badge -->
            @if ($trendText)
                <div>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-semibold {{ $pillStyle }}">
                        @if ($trend && ($isPositive || $trendType === 'positive'))
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                            <span>{{ $cleanTrendText }}</span>
                        @elseif ($trend && ($isNegative || $trendType === 'negative' || $trendType === 'error'))
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7l10 10m0 0H7m10 0V7"/></svg>
                            <span>{{ $cleanTrendText }}</span>
                        @else
                            <span>{{ $trendText }}</span>
                        @endif
                    </span>
                </div>
            @endif

            <!-- Row 4: Full-Width Context / Parameter Subtitle -->
            @if ($subtitle)
                <div class="text-xs text-gray-500 font-normal leading-normal">
                    {{ $subtitle }}
                </div>
            @endif
        </div>
    @else
        <!-- 3-Row Layout: Value & Pill on Row 2, Subtitle on Row 3 -->
        <div class="bg-[#F8F9FA] rounded-xl border border-gray-100 p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-2 min-h-[92px]">
            <!-- Row 2: Value + Trend Pill beside it -->
            <div class="flex items-center gap-2.5 flex-wrap">
                <div class="flex items-baseline gap-1">
                    @if ($prefix)
                        <span class="text-base font-bold text-gray-900 tracking-tight shrink-0">{{ $prefix }}</span>
                    @endif
                    <span class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight leading-none">{{ $value }}</span>
                </div>

                @if ($trendText)
                    <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-xs font-semibold shrink-0 {{ $pillStyle }}">
                        @if ($trend && ($isPositive || $trendType === 'positive'))
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 17L17 7M17 7H7M17 7v10"/></svg>
                            <span>{{ $cleanTrendText }}</span>
                        @elseif ($trend && ($isNegative || $trendType === 'negative' || $trendType === 'error'))
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7l10 10m0 0H7m10 0V7"/></svg>
                            <span>{{ $cleanTrendText }}</span>
                        @else
                            <span>{{ $trendText }}</span>
                        @endif
                    </span>
                @endif
            </div>

            <!-- Row 3: Subtitle Parameter -->
            @if ($subtitle)
                <div class="text-xs text-gray-500 font-normal leading-normal truncate pt-0.5">
                    {{ $subtitle }}
                </div>
            @endif
        </div>
    @endif

</div>



