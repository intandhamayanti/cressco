@props([
    'sender' => 'Oliver Queen',
    'time' => '04:30 PM',
    'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
    'isSender' => false,
])

<div class="flex items-start gap-3 {{ $isSender ? 'flex-row-reverse' : '' }}">
    <img src="{{ $avatar }}" alt="{{ $sender }}" class="w-8 h-8 rounded-full object-cover shrink-0">
    
    <div class="flex flex-col {{ $isSender ? 'items-end' : 'items-start' }} max-w-[85%] sm:max-w-md">
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-bold text-gray-900">{{ $sender }}</span>
            <span class="text-[10px] text-gray-400 font-medium">{{ $time }}</span>
        </div>

        <div class="px-4 py-3 rounded-2xl text-xs sm:text-sm leading-relaxed {{ $isSender ? 'bg-terracotta-500 text-white rounded-tr-xs shadow-xs' : 'bg-gray-100 text-gray-800 rounded-tl-xs' }}">
            {{ $slot }}
        </div>
    </div>
</div>
