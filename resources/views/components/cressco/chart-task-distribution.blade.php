@props([
    'title' => 'Realisasi Pembayaran',
    'subtitle' => 'Persentase tagihan siswa yang telah lunas',
    'centerLabel' => 'TAGIHAN LUNAS',
    'percentage' => null,
    'paidCount' => null,
    'total' => null,
    'unit' => 'Tagihan',
    'ratioText' => null,
    'segments' => [],
])

@php
    // If no props are passed at all, default to a realistic aesthetic dummy presentation
    $isDummy = ($percentage === null && $paidCount === null && $total === null && empty($segments));

    if ($isDummy) {
        $calcPercentage = 78;
        $paidVal = 18;
        $totalVal = 23;
        $ratioText = "18 dari 23 {$unit}";
    } else {
        $totalVal = (int) ($total ?? 0);

        // Determine paid count
        if ($paidCount !== null) {
            $paidVal = (int) $paidCount;
        } elseif (!empty($segments) && isset($segments[0]['value'])) {
            $paidVal = (int) $segments[0]['value'];
            if ($totalVal <= 0) {
                $totalVal = (int) array_sum(array_column($segments, 'value'));
            }
        } else {
            $paidVal = 0;
        }

        // Determine percentage
        if ($percentage !== null) {
            $calcPercentage = max(0, min(100, (int) round((float) $percentage)));
        } else {
            $calcPercentage = $totalVal > 0 ? (int) round(($paidVal / $totalVal) * 100) : 0;
        }

        if ($ratioText === null) {
            $ratioText = "{$paidVal} dari {$totalVal} {$unit}";
        }
    }

    $totalTicks = 20;
    $activeTicks = (int) round(($calcPercentage / 100) * $totalTicks);

    // Build radiating ticks with smooth terracotta fading gradient
    $ticks = [];
    for ($i = 0; $i < $totalTicks; $i++) {
        // Angle from -90 deg (left) to +90 deg (right)
        $angle = -90 + ($i * (180 / ($totalTicks - 1)));

        if ($i < $activeTicks) {
            // Smooth gradient interpolation from vibrant terracotta #CC4420 to soft faded terracotta #F3B9AA
            $ratio = $activeTicks > 1 ? ($i / ($activeTicks - 1)) : 0;
            $r = (int) round(204 + (243 - 204) * $ratio);
            $g = (int) round(68 + (185 - 68) * $ratio);
            $b = (int) round(32 + (170 - 32) * $ratio);
            $fill = sprintf('#%02X%02X%02X', $r, $g, $b);
        } else {
            // Inactive ticks: light gray/white (#EAECF0)
            $fill = '#EAECF0';
        }

        $ticks[] = [
            'angle' => round($angle, 2),
            'fill' => $fill,
        ];
    }
@endphp

<div {{ $attributes->merge(['class' => 'bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between select-none font-sans']) }}>
    <!-- Header -->
    <div>
        <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $title }}</h3>
        <p class="text-xs text-gray-500 mt-0.5">{{ $subtitle }}</p>
    </div>

    <!-- Segmented Radiating Ticks Gauge (Ultra Minimalist) -->
    <div class="relative flex flex-col items-center justify-center my-auto py-4">
        <svg class="w-full max-w-[280px] h-36 sm:h-40 overflow-visible" viewBox="0 0 280 145">
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
        <div class="absolute inset-x-0 bottom-3 flex flex-col items-center justify-center text-center px-4">
            <div class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight leading-none">
                {{ $calcPercentage }}<span class="text-xl sm:text-2xl font-bold text-terracotta-500">%</span>
            </div>
            <span class="text-[10px] sm:text-[11px] font-bold text-gray-400 tracking-wider uppercase mt-1.5">{{ $centerLabel }}</span>
            <span class="text-[11px] sm:text-xs text-gray-500 font-medium mt-0.5">({{ $ratioText }})</span>
        </div>
    </div>
</div>
