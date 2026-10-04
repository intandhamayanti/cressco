@props([
    'dates' => [
        ['day' => 19, 'dots' => ['emerald', 'purple', 'blue']],
        ['day' => 20, 'dots' => []],
        ['day' => 21, 'dots' => [], 'active' => true],
    ],
])

<div class="flex items-center gap-2">
    @foreach ($dates as $item)
        <div class="w-10 h-10 rounded-xl border flex flex-col items-center justify-center cursor-pointer transition
            {{ isset($item['active']) && $item['active'] ? 'bg-terracotta-500 border-terracotta-500 text-white shadow-xs font-bold' : 'bg-white border-gray-200 text-gray-700 hover:border-gray-300 font-semibold' }}">
            <span class="text-xs">{{ $item['day'] }}</span>
            @if (!empty($item['dots']))
                <div class="flex items-center justify-center gap-0.5 mt-0.5">
                    <span class="w-1 h-1 rounded-full {{ isset($item['active']) && $item['active'] ? 'bg-white' : 'bg-terracotta-500' }}"></span>
                </div>
            @endif
        </div>
    @endforeach
</div>
