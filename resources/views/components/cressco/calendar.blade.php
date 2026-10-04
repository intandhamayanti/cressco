@props([
    'month' => 'September 2025',
    'activeDay' => null,
    'days' => null,
    'prevUrl' => null,
    'nextUrl' => null,
])

@php
    $days = $days ?? [
        ['d' => 31, 'curr' => false], ['d' => 1, 'curr' => true], ['d' => 2, 'curr' => true], ['d' => 3, 'curr' => true, 'dots' => ['blue', 'emerald', 'purple']], ['d' => 4, 'curr' => true], ['d' => 5, 'curr' => true], ['d' => 6, 'curr' => true],
        ['d' => 7, 'curr' => true], ['d' => 8, 'curr' => true, 'dots' => ['emerald', 'blue']], ['d' => 9, 'curr' => true], ['d' => 10, 'curr' => true, 'dots' => ['blue']], ['d' => 11, 'curr' => true, 'dots' => ['blue', 'emerald', 'purple']], ['d' => 12, 'curr' => true], ['d' => 13, 'curr' => true],
        ['d' => 14, 'curr' => true, 'dots' => ['purple']], ['d' => 15, 'curr' => true, 'dots' => ['emerald']], ['d' => 16, 'curr' => true], ['d' => 17, 'curr' => true, 'active' => true, 'dots' => ['terracotta', 'purple']], ['d' => 18, 'curr' => true], ['d' => 19, 'curr' => true, 'dots' => ['emerald']], ['d' => 20, 'curr' => true],
        ['d' => 21, 'curr' => true], ['d' => 22, 'curr' => true, 'dots' => ['emerald', 'blue']], ['d' => 23, 'curr' => true], ['d' => 24, 'curr' => true, 'dots' => ['purple']], ['d' => 25, 'curr' => true], ['d' => 26, 'curr' => true, 'dots' => ['terracotta', 'emerald']], ['d' => 27, 'curr' => true],
        ['d' => 28, 'curr' => true], ['d' => 29, 'curr' => true], ['d' => 30, 'curr' => true, 'dots' => ['purple']], ['d' => 1, 'curr' => false], ['d' => 2, 'curr' => false], ['d' => 3, 'curr' => false], ['d' => 4, 'curr' => false],
    ];
@endphp

<div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs space-y-4 select-none font-sans">
    <!-- Header with Prev/Next Navigation -->
    <div class="flex items-center justify-between">
        @if ($prevUrl)
            <a href="{{ $prevUrl }}" class="w-8 h-8 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-2xs">
                <x-cressco.icon-helper name="chevron-left" class="w-4 h-4" />
            </a>
        @else
            <button type="button" class="w-8 h-8 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-50 transition shadow-2xs">
                <x-cressco.icon-helper name="chevron-left" class="w-4 h-4" />
            </button>
        @endif

        <span class="text-xs sm:text-sm font-bold text-gray-900">{{ $month }}</span>

        @if ($nextUrl)
            <a href="{{ $nextUrl }}" class="w-8 h-8 rounded-xl border border-gray-200 flex items-center justify-center text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-2xs">
                <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
            </a>
        @else
            <button type="button" class="w-8 h-8 rounded-xl border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-50 transition shadow-2xs">
                <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
            </button>
        @endif
    </div>

    <!-- Day Headers -->
    <div class="grid grid-cols-7 gap-1 text-center text-[10px] font-bold text-gray-400 uppercase tracking-wider">
        <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
    </div>

    <!-- Days Grid -->
    <div class="grid grid-cols-7 gap-1 text-center text-xs">
        @foreach ($days as $item)
            @php
                $itemDots = $item['dots'] ?? (isset($item['dot']) ? [$item['dot']] : []);
                $isActive = !empty($item['active']) || ($activeDay !== null && $item['curr'] && $item['d'] == $activeDay);
                $dayUrl = $item['url'] ?? null;
            @endphp

            @if ($dayUrl)
                <a href="{{ $dayUrl }}" class="h-9 flex flex-col items-center justify-center relative cursor-pointer group rounded-xl hover:bg-gray-50 transition">
            @else
                <div class="h-9 flex flex-col items-center justify-center relative cursor-pointer group">
            @endif

                <span class="w-7 h-7 flex items-center justify-center rounded-xl text-xs transition
                    {{ $isActive ? 'bg-terracotta-500 text-white font-bold shadow-xs' : ($item['curr'] ? 'text-gray-700 font-semibold group-hover:bg-gray-100' : 'text-gray-300') }}">
                    {{ $item['d'] }}
                </span>

                @if (!empty($itemDots))
                    <div class="flex items-center justify-center gap-0.5 absolute bottom-0.5">
                        <span class="w-1 h-1 rounded-full {{ $isActive ? 'bg-white' : 'bg-terracotta-500' }}"></span>
                    </div>
                @endif

            @if ($dayUrl)
                </a>
            @else
                </div>
            @endif
        @endforeach
    </div>
</div>
