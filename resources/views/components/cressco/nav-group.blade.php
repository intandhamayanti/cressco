@props([
    'title' => null,
])

<div {{ $attributes->merge(['class' => 'space-y-1']) }}>
    @if ($title)
        <div class="px-3 pt-4 pb-1">
            <h3 class="text-[11px] font-bold uppercase tracking-wider text-gray-400 font-sans">
                {{ $title }}
            </h3>
        </div>
    @endif

    <div class="space-y-1">
        {{ $slot }}
    </div>
</div>
