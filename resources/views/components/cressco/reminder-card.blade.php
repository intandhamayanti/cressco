@props([
    'title' => 'UI Revision',
    'time' => '09:00 AM - 10:00 AM',
    'url' => '#',
    'laterText' => 'Later',
    'detailsText' => 'Details',
])

<div class="bg-white rounded-3xl border border-gray-200/80 p-5 shadow-xs relative overflow-hidden space-y-4 font-sans select-none">
    <!-- Abstract Watermark Graphic in Background -->
    <div class="absolute -right-4 -bottom-4 w-28 h-28 opacity-[0.05] pointer-events-none text-terracotta-900">
        <svg viewBox="0 0 100 100" fill="currentColor">
            <path d="M50 0C50 27.614 27.614 50 0 50C27.614 50 50 72.386 50 100C50 72.386 72.386 50 100 50C72.386 50 50 27.614 50 0Z" />
        </svg>
    </div>

    <!-- Header Icon & Time -->
    <div class="space-y-2 relative z-10">
        <div class="w-10 h-10 rounded-2xl bg-terracotta-50 text-terracotta-700 border border-terracotta-200/70 flex items-center justify-center shadow-2xs">
            <x-cressco.icon-helper name="clock" class="w-5 h-5" />
        </div>
        
        <div>
            <span class="text-[11px] font-semibold text-gray-400 block">{{ $time }}</span>
            <h4 class="text-sm font-bold text-gray-900 mt-0.5 truncate">{{ $title }}</h4>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex items-center gap-2 pt-1 relative z-10">
        <button type="button" class="flex-1 px-3 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition text-center cursor-pointer shadow-2xs">
            {{ $laterText }}
        </button>
        <a href="{{ $url }}" class="flex-1 px-3 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-xs font-bold text-white transition text-center flex items-center justify-center gap-1 shadow-2xs">
            <span>{{ $detailsText }}</span>
            <x-cressco.icon-helper name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>
</div>
