@props([
    'items' => [
        ['day' => 19, 'active' => false, 'events' => []],
        ['day' => 20, 'active' => false, 'events' => []],
        ['day' => 21, 'active' => true, 'events' => ['Curriculum Sync • 09:00 AM', 'Tutor Evaluation • 02:00 PM']],
        ['day' => 22, 'active' => false, 'events' => []],
        ['day' => 23, 'active' => true, 'events' => ['Parent Meeting • 10:00 AM', 'System Review • 04:00 PM']],
    ],
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs overflow-x-auto">
    <div class="flex items-start gap-6 min-w-[500px]">
        @foreach ($items as $item)
            <div class="flex-1 flex flex-col items-center text-center space-y-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition {{ $item['active'] ? 'bg-amber-400 text-white shadow-sm ring-4 ring-amber-100' : 'bg-gray-100 text-gray-700' }}">
                    {{ $item['day'] }}
                </div>

                @if (!empty($item['events']))
                    <div class="space-y-1.5 w-full">
                        @foreach ($item['events'] as $event)
                            <div class="p-2 rounded-xl bg-gray-50 border border-gray-100 text-[10px] font-medium text-gray-700 text-left truncate">
                                {{ $event }}
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="h-10"></div>
                @endif
            </div>
        @endforeach
    </div>
</div>
