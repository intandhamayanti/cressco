@props([
    'avatars' => [
        'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=100&auto=format&fit=crop&q=80',
    ],
    'max' => 3,
    'size' => 'md', // 'sm', 'md', 'lg'
])

@php
    $sizeClass = match($size) {
        'sm' => 'w-6 h-6',
        'lg' => 'w-10 h-10',
        default => 'w-8 h-8',
    };
    $displayed = array_slice($avatars, 0, $max);
    $extraCount = count($avatars) - $max;
@endphp

<div class="flex -space-x-2 overflow-hidden items-center">
    @foreach ($displayed as $avatar)
        <img class="inline-block {{ $sizeClass }} rounded-full ring-2 ring-white object-cover shadow-2xs" src="{{ $avatar }}" alt="User">
    @endforeach

    @if ($extraCount > 0)
        <span class="inline-flex items-center justify-center {{ $sizeClass }} rounded-full ring-2 ring-white bg-gray-100 text-gray-700 text-[10px] font-bold shadow-2xs">
            +{{ $extraCount }}
        </span>
    @endif
</div>
