@props([
    'type' => 'owner', // 'owner', 'admin', 'tutor'
])

<div {{ $attributes->merge(['class' => 'space-y-6 max-w-7xl mx-auto font-sans animate-pulse select-none pointer-events-none']) }} aria-hidden="true">
    @if ($type === 'owner')
        <!-- Header Skeleton -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div class="space-y-2">
                <div class="h-7 w-56 bg-gray-200 rounded-xl"></div>
                <div class="h-4 w-96 max-w-full bg-gray-100 rounded-lg"></div>
            </div>
            <div class="flex items-center gap-3">
                <div class="h-9 w-44 bg-gray-100 rounded-xl border border-gray-200/60"></div>
                <div class="h-9 w-32 bg-gray-100 rounded-xl border border-gray-200/60"></div>
            </div>
        </div>

        <!-- 4 Metric Cards Skeleton -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-3xl border border-gray-200/80 p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-28 bg-gray-200 rounded-lg"></div>
                        <div class="w-9 h-9 rounded-xl bg-gray-100 border border-gray-200/60"></div>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <div class="h-7 w-36 bg-gray-200 rounded-xl"></div>
                        <div class="h-5 w-14 bg-gray-100 rounded-full"></div>
                    </div>
                    <div class="h-3 w-40 bg-gray-100 rounded-md"></div>
                </div>
            @endfor
        </div>

        <!-- Charts Row Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- Left Chart Skeleton (8 Cols) -->
            <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-3xl border border-gray-200/80 p-6 space-y-6 shadow-xs min-h-[360px] flex flex-col justify-between">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                    <div class="space-y-1.5">
                        <div class="h-5 w-64 bg-gray-200 rounded-lg"></div>
                        <div class="h-3 w-80 max-w-full bg-gray-100 rounded-md"></div>
                    </div>
                    <div class="flex gap-2">
                        <div class="h-6 w-20 bg-gray-100 rounded-lg"></div>
                        <div class="h-6 w-20 bg-gray-100 rounded-lg"></div>
                    </div>
                </div>

                <!-- Fake Bars / Grid Graphic -->
                <div class="h-48 flex items-end justify-between gap-3 pt-4 px-2">
                    @for ($b = 0; $b < 8; $b++)
                        <div class="flex-1 flex items-end justify-center gap-1.5 h-full">
                            <div class="w-3.5 bg-gray-200/90 rounded-t-md" style="height: {{ [40, 65, 50, 85, 70, 90, 60, 80][$b] }}%;"></div>
                            <div class="w-3.5 bg-gray-100 rounded-t-md" style="height: {{ [30, 45, 35, 55, 45, 60, 40, 50][$b] }}%;"></div>
                        </div>
                    @endfor
                </div>

                <div class="flex justify-between pt-2 border-t border-gray-100">
                    @foreach(['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu'] as $m)
                        <div class="h-3 w-6 bg-gray-100 rounded"></div>
                    @endforeach
                </div>
            </div>

            <!-- Right Gauge Skeleton (4 Cols) -->
            <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-3xl border border-gray-200/80 p-6 space-y-6 shadow-xs min-h-[360px] flex flex-col justify-between">
                <div class="space-y-1.5 border-b border-gray-100 pb-4">
                    <div class="h-5 w-44 bg-gray-200 rounded-lg"></div>
                    <div class="h-3 w-56 max-w-full bg-gray-100 rounded-md"></div>
                </div>

                <!-- Circular Arc Placeholder -->
                <div class="flex flex-col items-center justify-center py-4 space-y-3">
                    <div class="w-36 h-36 rounded-full border-8 border-gray-100 border-t-terracotta-200 flex items-center justify-center">
                        <div class="h-8 w-16 bg-gray-200 rounded-xl"></div>
                    </div>
                    <div class="h-4 w-32 bg-gray-100 rounded-md"></div>
                </div>

                <div class="p-3 bg-gray-50 rounded-2xl flex justify-between items-center">
                    <div class="h-4 w-24 bg-gray-200 rounded-md"></div>
                    <div class="h-4 w-16 bg-gray-200 rounded-md"></div>
                </div>
            </div>
        </div>

    @elseif ($type === 'admin')
        <!-- Header Skeleton -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="space-y-2">
                <div class="h-7 w-52 bg-gray-200 rounded-xl"></div>
                <div class="h-4 w-80 max-w-full bg-gray-100 rounded-lg"></div>
            </div>
            <div class="h-9 w-48 bg-gray-100 rounded-xl border border-gray-200/60"></div>
        </div>

        <!-- 4 Stat Cards Skeleton -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-3xl border border-gray-200/80 p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-28 bg-gray-200 rounded-lg"></div>
                        <div class="w-9 h-9 rounded-xl bg-gray-100 border border-gray-200/60"></div>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <div class="h-7 w-24 bg-gray-200 rounded-xl"></div>
                        <div class="h-5 w-16 bg-gray-100 rounded-full"></div>
                    </div>
                    <div class="h-3 w-40 bg-gray-100 rounded-md"></div>
                </div>
            @endfor
        </div>

        <!-- Today Sessions Card Skeleton -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-100"></div>
                    <div class="space-y-1.5">
                        <div class="h-4 w-48 bg-gray-200 rounded-lg"></div>
                        <div class="h-3 w-32 bg-gray-100 rounded-md"></div>
                    </div>
                </div>
                <div class="h-6 w-24 bg-gray-100 rounded-full"></div>
            </div>

            <div class="divide-y divide-gray-100 p-2">
                @for ($s = 0; $s < 3; $s++)
                    <div class="p-4 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 shrink-0"></div>
                            <div class="space-y-1.5">
                                <div class="h-4 w-40 bg-gray-200 rounded"></div>
                                <div class="h-3 w-56 bg-gray-100 rounded"></div>
                            </div>
                        </div>
                        <div class="h-8 w-24 bg-gray-100 rounded-xl"></div>
                    </div>
                @endfor
            </div>
        </div>

    @elseif ($type === 'tutor')
        <!-- 4 Metric Cards Skeleton -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @for ($i = 0; $i < 4; $i++)
                <div class="bg-white rounded-3xl border border-gray-200/80 p-5 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <div class="h-4 w-28 bg-gray-200 rounded-lg"></div>
                        <div class="w-9 h-9 rounded-xl bg-gray-100 border border-gray-200/60"></div>
                    </div>
                    <div class="flex items-baseline justify-between gap-2">
                        <div class="h-7 w-20 bg-gray-200 rounded-xl"></div>
                        <div class="h-5 w-14 bg-gray-100 rounded-full"></div>
                    </div>
                    <div class="h-3 w-36 bg-gray-100 rounded-md"></div>
                </div>
            @endfor
        </div>

        <!-- Row 1: Today Schedule & Calendar Skeleton -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            <!-- Left: Today Schedule Skeleton (8 Cols) -->
            <div class="lg:col-span-7 xl:col-span-8 bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-5">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-gray-100"></div>
                            <div class="space-y-1.5">
                                <div class="h-4 w-44 bg-gray-200 rounded-lg"></div>
                                <div class="h-3 w-32 bg-gray-100 rounded-md"></div>
                            </div>
                        </div>
                        <div class="h-6 w-20 bg-gray-100 rounded-full"></div>
                    </div>

                    <div class="mt-4 space-y-3">
                        @for ($sc = 0; $sc < 2; $sc++)
                            <div class="p-4 rounded-2xl bg-gray-50/80 border border-gray-100 flex items-center justify-between">
                                <div class="space-y-1.5">
                                    <div class="h-4 w-36 bg-gray-200 rounded"></div>
                                    <div class="h-3 w-48 bg-gray-100 rounded"></div>
                                </div>
                                <div class="h-6 w-28 bg-gray-200/70 rounded-lg"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- Right: Calendar Skeleton (4 Cols) -->
            <div class="lg:col-span-5 xl:col-span-4 bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="h-4 w-28 bg-gray-200 rounded-lg"></div>
                    <div class="flex gap-1">
                        <div class="w-7 h-7 bg-gray-100 rounded-lg"></div>
                        <div class="w-7 h-7 bg-gray-100 rounded-lg"></div>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-2 pt-2">
                    @for ($d = 0; $d < 28; $d++)
                        <div class="h-8 bg-gray-50 rounded-lg flex items-center justify-center">
                            <div class="h-3 w-3 bg-gray-200 rounded-full"></div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        <!-- Row 2: Upcoming Sessions Skeleton -->
        <div class="w-full bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gray-100"></div>
                    <div class="space-y-1.5">
                        <div class="h-4 w-48 bg-gray-200 rounded-lg"></div>
                        <div class="h-3 w-56 bg-gray-100 rounded-md"></div>
                    </div>
                </div>
                <div class="h-8 w-32 bg-gray-100 rounded-xl"></div>
            </div>

            <div class="divide-y divide-gray-100">
                @for ($up = 0; $up < 2; $up++)
                    <div class="py-3 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3.5">
                            <div class="w-12 h-12 rounded-xl bg-gray-100 shrink-0"></div>
                            <div class="space-y-1.5">
                                <div class="h-4 w-40 bg-gray-200 rounded"></div>
                                <div class="h-3 w-48 bg-gray-100 rounded"></div>
                            </div>
                        </div>
                        <div class="h-6 w-16 bg-gray-100 rounded-full"></div>
                    </div>
                @endfor
            </div>
        </div>
    @endif
</div>
