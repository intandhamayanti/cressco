@props([
    'ext' => 'pdf', // 'pdf', 'fig', 'png', 'doc', 'zip', 'xls'
    'color' => 'red',
])

@php
    $bg = match($ext) {
        'pdf' => 'text-red-500 bg-red-50 border-red-100',
        'fig' => 'text-purple-500 bg-purple-50 border-purple-100',
        'png', 'jpg' => 'text-emerald-500 bg-emerald-50 border-emerald-100',
        'doc' => 'text-blue-500 bg-blue-50 border-blue-100',
        'zip' => 'text-amber-500 bg-amber-50 border-amber-100',
        default => 'text-gray-500 bg-gray-50 border-gray-100',
    };
@endphp

<div class="w-10 h-10 rounded-xl border flex items-center justify-center font-bold text-[10px] uppercase tracking-wider {{ $bg }} shadow-2xs">
    {{ $ext }}
</div>
