@props([
    'priority' => 'High Priority',
    'priorityType' => 'error', // 'error', 'warning', 'info', 'success'
    'title' => 'UI Kit for SAAS Dashboard',
    'description' => 'Designing a modern UI kit with reusable components for SaaS products.',
    'dueDate' => '12 days left',
    'commentsCount' => 12,
    'attachmentsCount' => 4,
    'avatars' => [
        'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=100&auto=format&fit=crop&q=80',
        'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&auto=format&fit=crop&q=80',
    ],
])

@php
    $priorityClasses = match($priorityType) {
        'warning' => 'bg-amber-50 text-amber-700 border-amber-200/60',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
        'info' => 'bg-blue-50 text-blue-700 border-blue-200/60',
        default => 'bg-red-50 text-red-700 border-red-200/60',
    };
@endphp

<div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-4 hover:border-terracotta-300 transition duration-150">
    <div class="flex items-center justify-between">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-bold border {{ $priorityClasses }}">
            {{ $priority }}
        </span>
        <button type="button" class="text-gray-400 hover:text-gray-600 focus:outline-hidden">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>
        </button>
    </div>

    <div>
        <h4 class="text-sm font-bold text-gray-900 leading-snug">{{ $title }}</h4>
        <p class="text-xs text-gray-500 mt-1 leading-relaxed line-clamp-2">{{ $description }}</p>
    </div>

    <div class="flex items-center gap-1.5 text-xs text-gray-400 font-medium">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>{{ $dueDate }}</span>
    </div>

    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
        <!-- Avatar Stack -->
        <div class="flex -space-x-2 overflow-hidden">
            @foreach ($avatars as $avatar)
                <img class="inline-block h-6 w-6 rounded-full ring-2 ring-white object-cover" src="{{ $avatar }}" alt="Assignee">
            @endforeach
        </div>

        <!-- Metadata Counter Badges -->
        <div class="flex items-center gap-3 text-xs text-gray-400 font-medium">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                <span>{{ $attachmentsCount }}</span>
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span>{{ $commentsCount }}</span>
            </span>
        </div>
    </div>
</div>
