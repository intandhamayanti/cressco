@props([
    'count' => 1,
])

<div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-2xl bg-gray-900 text-white shadow-xl border border-gray-800 text-xs font-medium flex-wrap">
    <div class="flex items-center gap-2 pr-2 border-r border-gray-700">
        <span class="w-4 h-4 rounded bg-terracotta-500 flex items-center justify-center text-white text-[10px] font-bold">
            ✓
        </span>
        <span class="font-bold text-white">{{ $count }} Selected</span>
    </div>

    <!-- Actions -->
    <button type="button" class="px-2.5 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>Change Status</span>
    </button>

    <button type="button" class="px-2.5 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        <span>Change Priority</span>
    </button>

    <button type="button" class="px-2.5 py-1 rounded-lg bg-gray-800 hover:bg-gray-700 text-gray-200 transition flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span>Due Date</span>
    </button>

    <button type="button" class="px-2.5 py-1 rounded-lg bg-red-950/60 hover:bg-red-900 text-red-400 hover:text-red-200 transition flex items-center gap-1.5 border border-red-800/40">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        <span>Delete</span>
    </button>

    <button type="button" class="p-1 rounded-lg text-gray-400 hover:text-white hover:bg-gray-800 transition pl-1">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
</div>
