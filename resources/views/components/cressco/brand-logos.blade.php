<div class="grid grid-cols-6 sm:grid-cols-12 gap-3">
    @php
        $brands = [
            ['name' => 'Google', 'color' => 'bg-red-50 text-red-500', 'symbol' => 'G'],
            ['name' => 'Slack', 'color' => 'bg-emerald-50 text-emerald-600', 'symbol' => '#'],
            ['name' => 'Figma', 'color' => 'bg-purple-50 text-purple-600', 'symbol' => 'F'],
            ['name' => 'Dropbox', 'color' => 'bg-blue-50 text-blue-600', 'symbol' => 'D'],
            ['name' => 'Notion', 'color' => 'bg-gray-100 text-gray-900', 'symbol' => 'N'],
            ['name' => 'Zapier', 'color' => 'bg-orange-50 text-orange-600', 'symbol' => '⚡'],
            ['name' => 'Asana', 'color' => 'bg-rose-50 text-rose-500', 'symbol' => 'A'],
            ['name' => 'Trello', 'color' => 'bg-sky-50 text-sky-600', 'symbol' => 'T'],
            ['name' => 'GitHub', 'color' => 'bg-gray-900 text-white', 'symbol' => 'gh'],
            ['name' => 'Stripe', 'color' => 'bg-indigo-50 text-indigo-600', 'symbol' => 'S'],
            ['name' => 'Discord', 'color' => 'bg-indigo-100 text-indigo-700', 'symbol' => 'D'],
            ['name' => 'Linear', 'color' => 'bg-slate-100 text-slate-800', 'symbol' => 'L'],
        ];
    @endphp

    @foreach ($brands as $b)
        <div class="w-10 h-10 rounded-xl border border-gray-200/80 flex items-center justify-center font-bold text-xs shadow-2xs {{ $b['color'] }}" title="{{ $b['name'] }}">
            {{ $b['symbol'] }}
        </div>
    @endforeach
</div>
