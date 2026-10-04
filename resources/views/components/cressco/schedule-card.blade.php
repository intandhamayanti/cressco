@props([
    'title' => 'UI Module',
    'description' => 'Discussion on responsive layout & mobile navigation structure.',
    'time' => '07:00 PM - 09:00 PM',
    'color' => 'terracotta', // 'terracotta', 'blue', 'purple', 'emerald', 'amber'
    'avatar' => null,
    'avatars' => [],
    'variant' => 'horizontal', // 'horizontal' or 'compact'
])

@php
    $borderAccent = match($color) {
        'purple' => 'border-l-purple-500',
        'emerald', 'green' => 'border-l-emerald-500',
        'blue' => 'border-l-blue-500',
        'amber', 'yellow' => 'border-l-amber-500',
        default => 'border-l-terracotta-500',
    };

    $bgAccent = match($color) {
        'purple' => 'bg-purple-50/40',
        'emerald', 'green' => 'bg-emerald-50/40',
        'blue' => 'bg-blue-50/40',
        'amber', 'yellow' => 'bg-amber-50/40',
        default => 'bg-terracotta-50/30',
    };
@endphp

@if ($variant === 'compact')
    <div class="bg-white rounded-2xl border border-gray-200/90 border-l-4 {{ $borderAccent }} p-3 shadow-2xs space-y-1.5 hover:shadow-xs hover:border-gray-300 transition duration-150">
        <div class="space-y-0.5">
            <h4 class="text-[11px] font-bold text-gray-900 leading-snug line-clamp-2">{{ $title }}</h4>
            <span class="text-[10px] font-semibold text-gray-500 block">{{ $time }}</span>
            @if (!empty($description))
                <span class="text-[10px] text-gray-400 block truncate">{{ $description }}</span>
            @endif
        </div>

        @if (!empty($avatars))
            <div class="flex items-center -space-x-1.5 pt-1">
                @foreach (array_slice($avatars, 0, 3) as $av)
                    <img src="{{ $av }}" alt="" class="w-5 h-5 rounded-full object-cover border border-white">
                @endforeach
            </div>
        @elseif ($avatar)
            <div class="pt-0.5">
                <img src="{{ $avatar }}" alt="" class="w-5 h-5 rounded-full object-cover">
            </div>
        @endif
    </div>
@else
    <div class="bg-white rounded-2xl border border-gray-200/80 border-l-4 {{ $borderAccent }} p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-gray-300 hover:shadow-sm transition duration-150">
        <div class="space-y-1 min-w-0">
            <h4 class="text-xs sm:text-sm font-bold text-gray-900">{{ $title }}</h4>
            <p class="text-xs text-gray-500 leading-relaxed line-clamp-1">{{ $description }}</p>
        </div>

        <div class="flex items-center gap-3 shrink-0 self-start sm:self-center">
            <span class="text-[11px] font-semibold text-gray-500 bg-gray-50 px-2.5 py-1 rounded-lg border border-gray-100">{{ $time }}</span>

            @if (!empty($avatars))
                <div class="flex items-center -space-x-1.5">
                    @foreach (array_slice($avatars, 0, 3) as $av)
                        <img src="{{ $av }}" alt="" class="w-6 h-6 rounded-full object-cover border-2 border-white">
                    @endforeach
                </div>
            @elseif ($avatar)
                <img src="{{ $avatar }}" alt="" class="w-7 h-7 rounded-full object-cover">
            @else
                <div class="w-7 h-7 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-[10px] border border-terracotta-200/60">
                    {{ strtoupper(substr($title, 0, 2)) }}
                </div>
            @endif
        </div>
    </div>
@endif
