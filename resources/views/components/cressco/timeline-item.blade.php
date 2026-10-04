@props([
    'priority' => 'High Priority',
    'priorityVariant' => 'error', // 'error', 'warning', 'success', 'info'
    'title' => 'Cressco Dashboard MVP',
    'date' => 'October 12, 2026',
    'avatars' => [
        'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
    ],
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs flex items-center justify-between gap-4 hover:border-terracotta-300 transition duration-150">
    <div class="space-y-1.5 min-w-0">
        <x-cressco.badge :variant="$priorityVariant" size="sm">
            {{ $priority }}
        </x-cressco.badge>
        <h4 class="text-xs sm:text-sm font-bold text-gray-900 truncate">{{ $title }}</h4>
        <div class="flex items-center gap-1.5 text-[11px] text-gray-400 font-medium">
            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <span>{{ $date }}</span>
        </div>
    </div>

    <!-- Avatar Stack -->
    <div class="flex -space-x-1.5 overflow-hidden shrink-0">
        @foreach ($avatars as $avatar)
            <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white object-cover" src="{{ $avatar }}" alt="">
        @endforeach
    </div>
</div>
