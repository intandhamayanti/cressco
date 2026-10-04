<x-tutor-layout :tenant="$tenant" title="Schedule">
    <x-slot:breadcrumbSub>Schedule</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-[1600px] mx-auto">
        
        <!-- Two Column Main Layout (Left: Mini Calendar & Reminders, Right: Time Grid Planner) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT PANEL (4 Cols on LG, 3 Cols on XL) -->
            <div class="lg:col-span-4 xl:col-span-3 space-y-5">
                
                <!-- Title & Subtitle -->
                <div class="space-y-1">
                    <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Schedule</h1>
                    <p class="text-xs text-gray-500 leading-relaxed">Stay organized and monitor your schedule.</p>
                </div>

                <!-- 1. Mini Calendar Component from Reusable Components -->
                <x-cressco.calendar
                    :month="$monthName"
                    :days="$calendarDays"
                    :prevUrl="$prevMonthUrl"
                    :nextUrl="$nextMonthUrl"
                />
            </div>

            <!-- RIGHT PANEL: PLANNER TIMELINE / CALENDAR GRID (8 Cols on LG, 9 Cols on XL) -->
            <div class="lg:col-span-8 xl:col-span-9 space-y-5">
                
                <!-- Top Controls Toolbar (View Switcher: Day | Week | Month, and Filter Actions) -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    
                    <!-- Pill View Switcher: Day, Week, Month -->
                    <div class="inline-flex items-center p-1 bg-gray-100 rounded-2xl">
                        <a href="{{ route('tutor.schedules.index', array_merge(request()->except('view'), ['view' => 'day'])) }}"
                           class="px-5 py-2 rounded-xl text-xs font-bold transition duration-150 {{ $viewType === 'day' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Day
                        </a>
                        <a href="{{ route('tutor.schedules.index', array_merge(request()->except('view'), ['view' => 'week'])) }}"
                           class="px-5 py-2 rounded-xl text-xs font-bold transition duration-150 {{ $viewType === 'week' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Week
                        </a>
                        <a href="{{ route('tutor.schedules.index', array_merge(request()->except('view'), ['view' => 'month'])) }}"
                           class="px-5 py-2 rounded-xl text-xs font-bold transition duration-150 {{ $viewType === 'month' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900' }}">
                            Month
                        </a>
                    </div>

                    <!-- Right Controls (Branch Filter & Funnel) -->
                    <div class="flex items-center gap-2.5 self-start sm:self-center">
                        @if ($tutorBranches->count() > 1)
                            <form method="GET" action="{{ route('tutor.schedules.index') }}" class="inline-block">
                                <input type="hidden" name="view" value="{{ $viewType }}">
                                <input type="hidden" name="date" value="{{ $currentDate->toDateString() }}">
                                <select name="branch_id"
                                        onchange="this.form.submit()"
                                        class="text-xs font-semibold text-gray-700 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-2xs focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                                    <option value="all" {{ $selectedBranch === 'all' ? 'selected' : '' }}>Semua Cabang ({{ $tutorBranches->count() }})</option>
                                    @foreach ($tutorBranches as $branch)
                                        <option value="{{ $branch->id }}" {{ $selectedBranch === $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        @endif

                        <button type="button" class="p-2.5 rounded-xl bg-white border border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-50 transition shadow-2xs cursor-pointer" title="Filter Options">
                            <x-cressco.icon-helper name="filter" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- MAIN PLANNER CONTAINER -->
                <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
                    
                    @if ($viewType === 'day')
                        <!-- ============================================== -->
                        <!-- VIEW 1: DAY TIMELINE VIEW (Matching Figma 1) -->
                        <!-- ============================================== -->
                        <div class="p-6 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h2 class="text-sm sm:text-base font-extrabold text-gray-900">
                                    {{ $currentDate->translatedFormat('D, d F Y') }}
                                </h2>
                                <span class="text-xs text-gray-400 font-medium">({{ $days[$currentDate->dayOfWeek] ?? '' }})</span>
                            </div>
                            <span class="text-xs font-bold text-gray-400">GMT +07</span>
                        </div>

                        <!-- Time Slots Grid -->
                        <div class="divide-y divide-gray-100">
                            @foreach ($timeSlots as $timeKey => $timeLabel)
                                @php
                                    $hourPrefix = substr($timeKey, 0, 2);
                                    // Match schedules starting around this hour
                                    $slotSchedules = $selectedDaySchedules->filter(function($s) use ($hourPrefix) {
                                        return substr($s->start_time, 0, 2) === $hourPrefix;
                                    });

                                    // Match sessions on this date starting around this hour
                                    $slotSessions = $sessions->filter(function($sess) use ($currentDate, $hourPrefix) {
                                        return $sess->session_date && \Carbon\Carbon::parse($sess->session_date)->isSameDay($currentDate) && substr($sess->start_time, 0, 2) === $hourPrefix;
                                    });

                                    $hasItems = $slotSchedules->isNotEmpty() || $slotSessions->isNotEmpty();
                                @endphp

                                <div class="grid grid-cols-12 min-h-[72px] hover:bg-gray-50/40 transition">
                                    <!-- Time Column -->
                                    <div class="col-span-3 sm:col-span-2 p-4 text-xs font-semibold text-gray-400 select-none">
                                        {{ $timeLabel }}
                                    </div>

                                    <!-- Content Slot Column -->
                                    <div class="col-span-9 sm:col-span-10 p-3 flex flex-col justify-center gap-2 border-l border-gray-100">
                                        @if ($slotSessions->isNotEmpty())
                                            @foreach ($slotSessions as $sess)
                                                @php
                                                    $colors = ['terracotta', 'slate', 'blue', 'amber'];
                                                    $cardColor = $colors[$loop->index % count($colors)];
                                                @endphp
                                                <a href="{{ route('tutor.classes.show', $sess->class_id) }}" class="block">
                                                    <x-cressco.schedule-card
                                                        :title="$sess->classModel?->name ?? 'Kelas'"
                                                        :description="($sess->classModel?->subject ?? 'Umum') . ' • Ruang: ' . ($sess->room ?? '101') . ' • Cabang: ' . ($sess->branch?->name ?? 'Malang')"
                                                        :time="substr($sess->start_time, 0, 5) . ' - ' . substr($sess->end_time, 0, 5) . ' WIB'"
                                                        :color="$cardColor"
                                                        :avatar="'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=CC4420&color=fff'"
                                                    />
                                                </a>
                                            @endforeach
                                        @elseif ($slotSchedules->isNotEmpty())
                                            @foreach ($slotSchedules as $sched)
                                                @php
                                                    $colors = ['terracotta', 'slate', 'blue', 'amber'];
                                                    $cardColor = $colors[$loop->index % count($colors)];
                                                @endphp
                                                <a href="{{ route('tutor.classes.show', $sched->class_id) }}" class="block">
                                                    <x-cressco.schedule-card
                                                        :title="$sched->class?->name ?? 'Kelas'"
                                                        :description="($sched->class?->subject ?? 'Umum') . ' • Ruang: ' . ($sched->room ?? '101') . ' • Cabang: ' . ($sched->class?->branch?->name ?? 'Malang')"
                                                        :time="substr($sched->start_time, 0, 5) . ' - ' . substr($sched->end_time, 0, 5) . ' WIB'"
                                                        :color="$cardColor"
                                                        :avatar="'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=CC4420&color=fff'"
                                                    />
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    @elseif ($viewType === 'week')
                        <!-- ============================================== -->
                        <!-- VIEW 2: WEEK MULTI-COLUMN GRID (Matching Figma 2) -->
                        <!-- ============================================== -->
                        <div class="overflow-x-auto">
                            <div class="min-w-[760px]">
                                <!-- Header Row with GMT and Days of the Week -->
                                <div class="grid grid-cols-8 border-b border-gray-100 bg-gray-50/50 text-center text-xs font-bold text-gray-500 py-3">
                                    <div class="p-2 text-gray-400 font-semibold">GMT +07</div>
                                    @foreach ($weekColumns as $col)
                                        <div class="p-2 flex flex-col items-center justify-center">
                                            @if ($col['is_active'])
                                                <a href="{{ route('tutor.schedules.index', ['view' => 'week', 'date' => $col['date']]) }}"
                                                   class="px-3 py-1 rounded-xl bg-terracotta-500 text-white font-bold shadow-xs">
                                                    {{ $col['label'] }}
                                                </a>
                                            @else
                                                <a href="{{ route('tutor.schedules.index', ['view' => 'week', 'date' => $col['date']]) }}"
                                                   class="text-gray-700 hover:text-gray-900 transition">
                                                    {{ $col['label'] }}
                                                </a>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Time Slot Rows with 7 Day Columns -->
                                <div class="divide-y divide-gray-100">
                                    @foreach ($timeSlots as $timeKey => $timeLabel)
                                        @php
                                            $hourPrefix = substr($timeKey, 0, 2);
                                        @endphp
                                        <div class="grid grid-cols-8 min-h-[76px]">
                                            <!-- Time Column -->
                                            <div class="p-3 text-xs font-semibold text-gray-400 flex items-start justify-center border-r border-gray-100/80 select-none">
                                                {{ $timeLabel }}
                                            </div>

                                            <!-- 7 Day Cells -->
                                            @foreach ($weekColumns as $col)
                                                @php
                                                    $cellSchedules = $schedules->filter(function($s) use ($col, $hourPrefix) {
                                                        return $s->day_of_week === $col['day_of_week'] && substr($s->start_time, 0, 2) === $hourPrefix;
                                                    });
                                                @endphp

                                                <div class="p-1.5 border-r border-gray-100 last:border-r-0 flex flex-col justify-center gap-1.5">
                                                    @foreach ($cellSchedules as $sched)
                                                        @php
                                                            $colors = ['terracotta', 'slate', 'blue', 'amber'];
                                                            $cardColor = $colors[$loop->index % count($colors)];
                                                        @endphp
                                                        <a href="{{ route('tutor.classes.show', $sched->class_id) }}" class="block">
                                                            <x-cressco.schedule-card
                                                                variant="compact"
                                                                :title="$sched->class?->name ?? 'Kelas'"
                                                                :description="$sched->room ?: ($sched->class?->subject ?? '')"
                                                                :time="substr($sched->start_time, 0, 5) . ' - ' . substr($sched->end_time, 0, 5)"
                                                                :color="$cardColor"
                                                                :avatar="'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=CC4420&color=fff'"
                                                            />
                                                        </a>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                    @else
                        <!-- ============================================== -->
                        <!-- VIEW 3: SESSIONS / MONTH LOG VIEW -->
                        <!-- ============================================== -->
                        <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Riwayat & Daftar Sesi Mengajar</h2>
                                <p class="text-[11px] text-gray-500">Daftar sesi pengajaran spesifik dengan status kehadiran</p>
                            </div>
                            <span class="text-xs font-semibold text-gray-500">{{ $sessions->total() }} Sesi Terdata</span>
                        </div>

                        @if ($sessions->isNotEmpty())
                            <div class="overflow-x-auto">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                            <th class="p-4">Tanggal & Waktu</th>
                                            <th class="p-4">Kelas & Mapel</th>
                                            <th class="p-4">Cabang & Ruang</th>
                                            <th class="p-4">Status Sesi</th>
                                            <th class="p-4 text-center">Presensi Siswa</th>
                                            <th class="p-4 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 text-gray-700">
                                        @foreach ($sessions as $session)
                                            <tr class="hover:bg-gray-50/60 transition">
                                                <td class="p-4">
                                                    <div class="font-bold text-gray-900">
                                                        {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d F Y') : '-' }}
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                                        {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                                                    </div>
                                                </td>

                                                <td class="p-4">
                                                    <div class="font-bold text-gray-900">
                                                        {{ $session->classModel?->name ?? 'Kelas' }}
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                                        {{ $session->classModel?->subject ?? 'Umum' }}
                                                    </div>
                                                </td>

                                                <td class="p-4">
                                                    <div class="font-semibold text-gray-900">
                                                        {{ $session->branch?->name ?? 'Cabang' }}
                                                    </div>
                                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                                        Ruang: {{ $session->room ?? '-' }}
                                                    </div>
                                                </td>

                                                <td class="p-4">
                                                    @if ($session->status === 'completed')
                                                        <x-cressco.badge variant="success" dot>Selesai</x-cressco.badge>
                                                    @elseif ($session->status === 'in_progress')
                                                        <x-cressco.badge variant="warning" dot>Berlangsung</x-cressco.badge>
                                                    @elseif ($session->status === 'cancelled')
                                                        <x-cressco.badge variant="danger" dot>Dibatalkan</x-cressco.badge>
                                                    @else
                                                        <x-cressco.badge variant="info" dot>Terjadwal</x-cressco.badge>
                                                    @endif
                                                </td>

                                                <td class="p-4 text-center">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-800">
                                                        {{ $session->student_attendances_count }} Siswa Tercatat
                                                    </span>
                                                </td>

                                                <td class="p-4 text-right">
                                                    @if ($session->class_id)
                                                        <a href="{{ route('tutor.classes.show', $session->class_id) }}" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold text-terracotta-700 hover:bg-terracotta-50 transition">
                                                            <span>Lihat Kelas</span>
                                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5" />
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if ($sessions->hasPages())
                                <div class="p-4 border-t border-gray-100">
                                    {{ $sessions->links() }}
                                </div>
                            @endif
                        @else
                            <div class="p-12 text-center text-xs text-gray-500">
                                Belum ada riwayat sesi mengajar yang tercatat.
                            </div>
                        @endif
                    @endif

                </div>

            </div>

        </div>

    </div>
</x-tutor-layout>
