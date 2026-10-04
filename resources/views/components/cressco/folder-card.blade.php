@props([
    'title' => 'Project Files',
    'filesCount' => '254 files',
    'size' => '456 MB',
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs flex flex-col items-center text-center space-y-4 hover:border-terracotta-300 transition duration-150 group">
    <!-- Elevated Folder Icon Box -->
    <div class="w-full h-24 rounded-xl bg-sky-50/70 border border-sky-100 flex items-center justify-center">
        <svg class="w-12 h-12 text-sky-400 fill-sky-400" viewBox="0 0 24 24">
            <path d="M19.5 21a3 3 0 0 0 3-3v-4.5a3 3 0 0 0-3-3h-1.5V9a3 3 0 0 0-3-3h-3.414a2 2 0 0 1-1.414-.586L9.172 4.414A2 2 0 0 0 7.758 4H4.5A3 3 0 0 0 1.5 7v11a3 3 0 0 0 3 3h15z"/>
        </svg>
    </div>

    <div>
        <h4 class="text-sm font-bold text-gray-900 group-hover:text-terracotta-600 transition">{{ $title }}</h4>
        <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $filesCount }} • {{ $size }}</p>
    </div>
</div>
