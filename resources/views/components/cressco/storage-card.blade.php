@props([
    'title' => 'Storage Plan',
    'subtitle' => '7.5 GB of 10 GB used',
    'percentage' => 75,
    'actionLabel' => 'Upgrade Plan',
    'actionHref' => '#',
    'variant' => 'storage', // 'storage', 'help'
])

@if ($variant === 'help')
    <div {{ $attributes->merge(['class' => 'p-4 rounded-2xl bg-terracotta-50/80 border border-terracotta-100 text-gray-900 space-y-3']) }}>
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-terracotta-500 text-white flex items-center justify-center shrink-0 shadow-xs">
                <x-cressco.icon-helper name="help-circle" class="w-4 h-4" />
            </div>
            <div>
                <h4 class="text-xs font-bold text-gray-900">Need Assistance?</h4>
                <p class="text-[11px] text-gray-500">Contact our 24/7 tutor support</p>
            </div>
        </div>
        <x-cressco.button variant="secondary-terracotta" size="xs" fullWidth href="{{ $actionHref }}">
            {{ $actionLabel ?: 'Get Support' }}
        </x-cressco.button>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'p-4 rounded-2xl bg-gray-50 border border-gray-200/80 text-gray-900 space-y-3 shadow-2xs']) }}>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-white border border-gray-200 text-gray-700 flex items-center justify-center shadow-2xs">
                    <x-cressco.icon-helper name="folder" class="w-3.5 h-3.5 text-gray-500" />
                </div>
                <span class="text-xs font-bold text-gray-900">{{ $title }}</span>
            </div>
            <span class="text-xs font-bold text-terracotta-600">{{ $percentage }}%</span>
        </div>

        <div class="space-y-1.5">
            <div class="w-full bg-gray-200 rounded-full h-1.5 overflow-hidden">
                <div class="bg-terracotta-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ min(100, max(0, $percentage)) }}%"></div>
            </div>
            <p class="text-[11px] text-gray-500 font-medium">{{ $subtitle }}</p>
        </div>

        <x-cressco.button variant="secondary" size="xs" fullWidth href="{{ $actionHref }}">
            {{ $actionLabel }}
        </x-cressco.button>
    </div>
@endif
