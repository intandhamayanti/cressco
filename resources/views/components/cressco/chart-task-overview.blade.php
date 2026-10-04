@props([
    'title' => 'Session Overview',
    'subtitle' => 'Track teaching sessions and monitor activity over time.',
    'period' => 'Weekly',
    'unit' => 'Sessions',
    'days' => [
        ['name' => 'Monday', 'total' => 14, 'completed' => 7, 'upcoming' => 5, 'other' => 2, 'height1' => 28, 'height2' => 30, 'height3' => 10],
        ['name' => 'Tuesday', 'total' => 17, 'completed' => 8, 'upcoming' => 7, 'other' => 2, 'height1' => 38, 'height2' => 36, 'height3' => 12],
        ['name' => 'Wednesday', 'total' => 12, 'completed' => 6, 'upcoming' => 4, 'other' => 2, 'height1' => 24, 'height2' => 26, 'height3' => 10],
        ['name' => 'Thursday', 'total' => 23, 'completed' => 11, 'upcoming' => 9, 'other' => 3, 'height1' => 46, 'height2' => 52, 'height3' => 14],
        ['name' => 'Friday', 'total' => 15, 'completed' => 7, 'upcoming' => 6, 'other' => 2, 'height1' => 30, 'height2' => 32, 'height3' => 10],
    ],
])

<div x-data="{
    period: '{{ $period }}',
    activeDay: 1,
    days: @js($days),
    unit: '{{ $unit }}'
}" class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-6">
    <!-- Header with Title & Period Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-base font-bold text-gray-900">{{ $title }}</h3>
            <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
        </div>
        <!-- Weekly / Monthly Toggle -->
        <div class="flex items-center p-1 bg-gray-100/90 rounded-xl border border-gray-200/60 self-start sm:self-auto text-xs font-semibold">
            <button type="button" @click="period = 'Weekly'" :class="period === 'Weekly' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-900'" class="px-3.5 py-1 rounded-lg transition duration-150">Weekly</button>
            <button type="button" @click="period = 'Monthly'" :class="period === 'Monthly' ? 'bg-white text-gray-900 shadow-2xs' : 'text-gray-500 hover:text-gray-900'" class="px-3.5 py-1 rounded-lg transition duration-150">Monthly</button>
        </div>
    </div>

    <!-- Chart Container -->
    <div class="relative pt-8 pb-4">
        <!-- Y-Axis Grid Lines & Labels -->
        <div class="absolute inset-0 flex flex-col justify-between pointer-events-none text-[11px] text-gray-400 font-sans pb-8">
            <div class="border-b border-gray-100/80 w-full flex items-center justify-between"><span>30</span></div>
            <div class="border-b border-gray-100/80 w-full flex items-center justify-between"><span>25</span></div>
            <div class="border-b border-gray-100/80 w-full flex items-center justify-between"><span>20</span></div>
            <div class="border-b border-gray-100/80 w-full flex items-center justify-between"><span>15</span></div>
            <div class="border-b border-gray-100/80 w-full flex items-center justify-between"><span>10</span></div>
            <div class="border-b border-gray-100/80 w-full flex items-center justify-between"><span>5</span></div>
            <div class="border-b border-gray-200 w-full flex items-center justify-between text-gray-500 font-semibold"><span>0</span></div>
        </div>

        <!-- SVG Stacked Bars & Connection Ribbons (Matching Figma Reference Image) -->
        <div class="relative z-10 h-64 pl-6 sm:pl-8">
            <svg class="w-full h-full overflow-visible" viewBox="0 0 600 200" preserveAspectRatio="none">
                <defs>
                    <!-- Connecting Ribbon Gradients -->
                    <linearGradient id="topRibbonGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#E2E8F0" stop-opacity="0.5" />
                        <stop offset="100%" stop-color="#F1F5F9" stop-opacity="0.1" />
                    </linearGradient>
                    <linearGradient id="terracottaRibbonGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#CC4420" stop-opacity="0.18" />
                        <stop offset="100%" stop-color="#CC4420" stop-opacity="0.04" />
                    </linearGradient>
                    <linearGradient id="baseRibbonGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#2B3440" stop-opacity="0.08" />
                        <stop offset="100%" stop-color="#2B3440" stop-opacity="0.02" />
                    </linearGradient>
                </defs>

                <!-- 1. Connecting Polygons (Ribbons between columns) -->
                <!-- Monday -> Tuesday -->
                <polygon points="76,128 174,110 174,180 76,180" fill="url(#baseRibbonGrad)" />
                <polygon points="76,96 174,72 174,108 76,126" fill="url(#terracottaRibbonGrad)" />
                <polygon points="76,84 174,58 174,70 76,94" fill="url(#topRibbonGrad)" />

                <!-- Tuesday -> Wednesday -->
                <polygon points="214,110 314,136 314,180 214,180" fill="url(#baseRibbonGrad)" />
                <polygon points="214,72 314,108 314,134 214,108" fill="url(#terracottaRibbonGrad)" />
                <polygon points="214,58 314,96 314,106 214,70" fill="url(#topRibbonGrad)" />

                <!-- Wednesday -> Thursday -->
                <polygon points="354,136 454,92 454,180 354,180" fill="url(#baseRibbonGrad)" />
                <polygon points="354,108 454,38 454,90 354,134" fill="url(#terracottaRibbonGrad)" />
                <polygon points="354,96 454,22 454,36 354,106" fill="url(#topRibbonGrad)" />

                <!-- Thursday -> Friday -->
                <polygon points="494,92 594,124 594,180 494,180" fill="url(#baseRibbonGrad)" />
                <polygon points="494,38 594,90 594,122 494,90" fill="url(#terracottaRibbonGrad)" />
                <polygon points="494,22 594,78 594,88 494,36" fill="url(#topRibbonGrad)" />

                <!-- 2. Stacked Bars per Day (Monday=56, Tuesday=194, Wednesday=334, Thursday=474, Friday=614 - centered) -->
                <!-- Monday (x=56, width=40) -->
                <g class="cursor-pointer transition-transform duration-200 hover:scale-[1.02]" @mouseenter="activeDay = 0">
                    <rect x="36" y="130" width="40" height="50" rx="6" fill="#2B3440" />
                    <rect x="36" y="96" width="40" height="32" rx="6" fill="#CC4420" />
                    <rect x="36" y="84" width="40" height="10" rx="4" fill="#8A94A6" />
                </g>

                <!-- Tuesday (x=194, width=40) -->
                <g class="cursor-pointer transition-transform duration-200 hover:scale-[1.02]" @mouseenter="activeDay = 1">
                    <rect x="174" y="112" width="40" height="68" rx="6" fill="#2B3440" />
                    <rect x="174" y="72" width="40" height="38" rx="6" fill="#CC4420" />
                    <rect x="174" y="58" width="40" height="12" rx="4" fill="#8A94A6" />
                </g>

                <!-- Wednesday (x=334, width=40) -->
                <g class="cursor-pointer transition-transform duration-200 hover:scale-[1.02]" @mouseenter="activeDay = 2">
                    <rect x="314" y="138" width="40" height="42" rx="6" fill="#2B3440" />
                    <rect x="314" y="108" width="40" height="28" rx="6" fill="#CC4420" />
                    <rect x="314" y="96" width="40" height="10" rx="4" fill="#8A94A6" />
                </g>

                <!-- Thursday (x=474, width=40) -->
                <g class="cursor-pointer transition-transform duration-200 hover:scale-[1.02]" @mouseenter="activeDay = 3">
                    <rect x="454" y="94" width="40" height="86" rx="6" fill="#2B3440" />
                    <rect x="454" y="38" width="40" height="54" rx="6" fill="#CC4420" />
                    <rect x="454" y="22" width="40" height="14" rx="4" fill="#8A94A6" />
                </g>

                <!-- Friday (x=614, width=40) -->
                <g class="cursor-pointer transition-transform duration-200 hover:scale-[1.02]" @mouseenter="activeDay = 4">
                    <rect x="554" y="126" width="40" height="54" rx="6" fill="#2B3440" />
                    <rect x="554" y="90" width="40" height="34" rx="6" fill="#CC4420" />
                    <rect x="554" y="78" width="40" height="10" rx="4" fill="#8A94A6" />
                </g>
            </svg>

            <!-- Floating Top Value Badges & Tooltip -->
            <div class="absolute inset-x-6 sm:inset-x-8 top-0 bottom-8 flex justify-between pointer-events-none text-xs font-bold text-gray-800">
                <!-- Monday (14 Sessions) -->
                <div class="flex flex-col items-center transform -translate-y-2 pointer-events-auto cursor-pointer" @mouseenter="activeDay = 0">
                    <span class="bg-white/95 px-2.5 py-0.5 rounded-lg border border-gray-200/80 shadow-2xs text-[11px] font-bold text-gray-800">14 {{ $unit }}</span>
                </div>

                <!-- Tuesday (17 Sessions + Active Tooltip) -->
                <div class="flex flex-col items-center transform -translate-y-9 relative pointer-events-auto cursor-pointer" @mouseenter="activeDay = 1">
                    <span class="bg-white/95 px-2.5 py-0.5 rounded-lg border border-gray-200/80 shadow-2xs text-[11px] font-bold text-gray-800">17 {{ $unit }}</span>

                    <!-- Interactive Tooltip (Figma Reference Image 2) -->
                    <div x-show="activeDay === 1"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="absolute -top-20 z-30 bg-white border border-gray-200/90 rounded-2xl p-3 shadow-xl whitespace-nowrap text-left text-xs pointer-events-auto min-w-[145px]">
                        <div class="font-bold text-gray-900 border-b border-gray-100 pb-1.5 mb-1.5 text-xs">Tuesday</div>
                        <div class="flex items-center gap-2 text-[11px] text-gray-600">
                            <span class="w-2 h-2 rounded-full bg-terracotta-500 shrink-0"></span>
                            <span>Completed: <strong class="text-gray-900">8</strong></span>
                        </div>
                        <div class="flex items-center gap-2 text-[11px] text-gray-600 mt-1">
                            <span class="w-2 h-2 rounded-full bg-[#2B3440] shrink-0"></span>
                            <span>Upcoming: <strong class="text-gray-900">7</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Wednesday (12 Sessions) -->
                <div class="flex flex-col items-center transform -translate-y-1 pointer-events-auto cursor-pointer" @mouseenter="activeDay = 2">
                    <span class="bg-white/95 px-2.5 py-0.5 rounded-lg border border-gray-200/80 shadow-2xs text-[11px] font-bold text-gray-800">12 {{ $unit }}</span>
                </div>

                <!-- Thursday (23 Sessions) -->
                <div class="flex flex-col items-center transform -translate-y-16 pointer-events-auto cursor-pointer" @mouseenter="activeDay = 3">
                    <span class="bg-white/95 px-2.5 py-0.5 rounded-lg border border-gray-200/80 shadow-2xs text-[11px] font-bold text-gray-800">23 {{ $unit }}</span>
                </div>

                <!-- Friday (15 Sessions) -->
                <div class="flex flex-col items-center transform -translate-y-4 pointer-events-auto cursor-pointer" @mouseenter="activeDay = 4">
                    <span class="bg-white/95 px-2.5 py-0.5 rounded-lg border border-gray-200/80 shadow-2xs text-[11px] font-bold text-gray-800">15 {{ $unit }}</span>
                </div>
            </div>

            <!-- X-Axis Day Labels -->
            <div class="absolute inset-x-6 sm:inset-x-8 bottom-0 flex justify-between text-xs font-medium text-gray-500">
                <span :class="activeDay === 0 ? 'text-terracotta-600 font-bold' : ''">Monday</span>
                <span :class="activeDay === 1 ? 'text-terracotta-600 font-bold' : ''">Tuesday</span>
                <span :class="activeDay === 2 ? 'text-terracotta-600 font-bold' : ''">Wednesday</span>
                <span :class="activeDay === 3 ? 'text-terracotta-600 font-bold' : ''">Thursday</span>
                <span :class="activeDay === 4 ? 'text-terracotta-600 font-bold' : ''">Friday</span>
            </div>
        </div>
    </div>
</div>
