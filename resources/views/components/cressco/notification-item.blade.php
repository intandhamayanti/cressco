@props([
    'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=100&auto=format&fit=crop&q=80',
    'title' => 'Jane Cooper',
    'action' => 'requested access to Meta Pixel on iOS project.',
    'time' => '10 mins ago',
    'isActionable' => false,
    'snippet' => null,
    'unread' => true,
])

<div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs flex items-start gap-3.5 hover:border-terracotta-300 transition duration-150">
    <img src="{{ $avatar }}" alt="" class="w-9 h-9 rounded-full object-cover shrink-0 mt-0.5">

    <div class="flex-1 min-w-0 space-y-2">
        <div>
            <p class="text-xs text-gray-800 leading-relaxed">
                <strong class="font-bold text-gray-900">{{ $title }}</strong> {{ $action }}
            </p>
            <span class="text-[10px] text-gray-400 font-medium block mt-0.5">{{ $time }}</span>
        </div>

        @if ($snippet)
            <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900 font-medium leading-relaxed">
                {{ $snippet }}
            </div>
        @endif

        @if ($isActionable)
            <div class="flex items-center gap-2 pt-1">
                <button type="button" class="px-3 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-xs font-semibold text-gray-700 transition">
                    Deny
                </button>
                <button type="button" class="px-3 py-1 rounded-lg bg-amber-400 hover:bg-amber-500 text-xs font-semibold text-gray-900 transition">
                    Approve
                </button>
            </div>
        @endif
    </div>

    @if ($unread)
        <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0 mt-1.5 shadow-2xs"></span>
    @endif
</div>
