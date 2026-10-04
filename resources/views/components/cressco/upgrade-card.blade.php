@props([
    'title' => 'Storage Almost Full',
    'description' => 'You have used 80% of your total storage. Upgrade for more storage and features.',
    'buttonText' => 'Upgrade Plan',
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs flex flex-col justify-between space-y-4">
    <div>
        <h4 class="text-sm font-bold text-gray-900">{{ $title }}</h4>
        <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $description }}</p>
    </div>

    <!-- Segmented Gradient Progress Bar -->
    <div class="space-y-1.5">
        <div class="flex items-center gap-1">
            @for ($i = 0; $i < 20; $i++)
                @php
                    $color = '#EAECF0';
                    if ($i < 6) $color = '#344054';
                    elseif ($i < 12) $color = '#B5381B';
                    elseif ($i < 16) $color = '#CC4420';
                    elseif ($i < 18) $color = '#E88C74';
                @endphp
                <span class="flex-1 h-3 rounded-xs" style="background-color: {{ $color }};"></span>
            @endfor
        </div>
    </div>

    <button type="button" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white text-xs font-semibold shadow-xs transition duration-150">
        <span>{{ $buttonText }}</span>
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
    </button>
</div>
