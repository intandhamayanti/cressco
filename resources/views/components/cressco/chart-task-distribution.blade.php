@props([
    'title' => 'Payment Overview',
    'subtitle' => 'Monitor payment status and transactions',
    'centerLabel' => 'Total Payments',
    'total' => 200,
    'unit' => 'Payments',
    'segments' => [
        ['label' => 'Paid', 'value' => 125, 'color' => '#C84B31', 'barColor' => 'bg-[#C84B31]'],
        ['label' => 'Pending', 'value' => 50, 'color' => '#2B3440', 'barColor' => 'bg-[#2B3440]'],
        ['label' => 'Overdue', 'value' => 25, 'color' => '#717B8C', 'barColor' => 'bg-[#717B8C]'],
    ],
])

@php
    $totalVal = max(1, (float) $total);
    $val1 = (float) ($segments[0]['value'] ?? 125);
    $val2 = (float) ($segments[1]['value'] ?? 50);
    $val3 = (float) ($segments[2]['value'] ?? 25);

    $totalTicks = 20;
    $count1 = round(($val1 / $totalVal) * $totalTicks);
    $count2 = round(($val2 / $totalVal) * $totalTicks);
    $count3 = $totalTicks - $count1 - $count2;

    $color1 = $segments[0]['color'] ?? '#C84B31';
    $color2 = $segments[1]['color'] ?? '#2B3440';
    $color3 = $segments[2]['color'] ?? '#717B8C';

    $ticks = [];
    for ($i = 0; $i < $totalTicks; $i++) {
        // Angle from -90 degrees (left horizontal) to +90 degrees (right horizontal)
        $angle = -90 + ($i * (180 / ($totalTicks - 1)));
        
        if ($i < $count1) {
            $fill = $color1;
        } elseif ($i < ($count1 + $count2)) {
            $fill = $color2;
        } else {
            $fill = $color3;
        }

        $ticks[] = [
            'angle' => round($angle, 2),
            'fill' => $fill,
        ];
    }
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-6 select-none font-sans']) }}>
    <!-- Header -->
    <div>
        <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $title }}</h3>
        <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
    </div>

    <!-- Segmented Radiating Ticks Gauge (Exact Figma Reference) -->
    <div class="relative flex flex-col items-center justify-center my-2">
        <svg class="w-full max-w-[280px] h-40 overflow-visible" viewBox="0 0 280 145">
            <g transform="translate(140, 132)">
                @foreach ($ticks as $tick)
                    <rect x="-5.5"
                          y="-124"
                          width="11"
                          height="32"
                          rx="5.5"
                          fill="{{ $tick['fill'] }}"
                          transform="rotate({{ $tick['angle'] }})" />
                @endforeach
            </g>
        </svg>

        <!-- Center Counter Label & Value (Proportionate Typography) -->
        <div class="absolute inset-x-0 bottom-2 flex flex-col items-center justify-center text-center px-4">
            <span class="text-[11px] font-medium text-gray-400 tracking-wider uppercase">{{ $centerLabel }}</span>
            <div class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight mt-0.5">
                {{ $total }} <span class="text-sm sm:text-base font-semibold text-gray-600">{{ $unit }}</span>
            </div>
        </div>
    </div>

    <!-- Bottom Legend with Vertical Accent Bars (Figma Reference Layout) -->
    <div class="grid grid-cols-3 gap-3 sm:gap-4 pt-4 border-t border-gray-100">
        @foreach ($segments as $seg)
            <div class="flex items-start gap-2 sm:gap-2.5">
                <div class="w-1 h-8 rounded-full {{ $seg['barColor'] ?? 'bg-terracotta-500' }} shrink-0 mt-0.5"></div>
                <div class="min-w-0">
                    <div class="text-xs font-medium text-gray-500 truncate">{{ $seg['label'] }}</div>
                    <div class="text-base sm:text-lg font-bold text-gray-900 leading-tight mt-0.5">
                        {{ $seg['value'] }} <span class="text-xs font-normal text-gray-400">{{ $unit }}</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
