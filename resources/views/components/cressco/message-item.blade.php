@props([
    'sender' => 'Oliver Queen',
    'message' => 'Hovering in the dashboard and reviewing student schedules.',
    'time' => '12:45 PM',
    'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
    'unreadCount' => 3,
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs flex items-center gap-3.5 hover:border-terracotta-300 transition duration-150 cursor-pointer">
    <div class="relative shrink-0">
        <img src="{{ $avatar }}" alt="{{ $sender }}" class="w-10 h-10 rounded-full object-cover">
        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-white absolute bottom-0 right-0"></span>
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center justify-between">
            <h4 class="text-xs font-bold text-gray-900 truncate">{{ $sender }}</h4>
            <span class="text-[10px] text-gray-400 font-medium">{{ $time }}</span>
        </div>
        <p class="text-xs text-gray-500 mt-0.5 truncate">{{ $message }}</p>
    </div>

    @if ($unreadCount > 0)
        <span class="w-5 h-5 rounded-full bg-amber-400 text-white text-[10px] font-bold flex items-center justify-center shrink-0 shadow-2xs">
            {{ $unreadCount }}
        </span>
    @endif
</div>
