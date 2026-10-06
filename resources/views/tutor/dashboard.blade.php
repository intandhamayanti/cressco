<x-tutor-layout :tenant="$tenant" title="Tutor Dashboard">
    <x-slot:breadcrumbSub>Dashboard</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto font-sans"
         x-data="{ loading: true }"
         x-init="setTimeout(() => loading = false, 2500)">
        
        <!-- Skeleton Loading State (Visible for 2.5s) -->
        <div x-show="loading" class="transition-opacity duration-300">
            <x-cressco.skeleton-dashboard type="tutor" />
        </div>

        <!-- Real Dashboard Content -->
        <div x-show="!loading"
             x-cloak
             x-transition:enter="transition ease-out duration-300 transform"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="space-y-6">

            <!-- 4 Metric Cards (Using Reusable project-card Component with 4-Row Layout) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Kelas yang Diampu"
                icon="book-open"
                iconColor="text-terracotta-500"
                value="{{ $totalClassesCount }}"
                trend="Aktif"
                trendType="terracotta"
                subtitle="Total kelas rombel penugasan"
                layout="4-row"
                :href="route('tutor.classes.index')"
            />

            <x-cressco.project-card
                title="Total Siswa"
                icon="users"
                iconColor="text-gray-500"
                value="{{ $totalStudentsCount }}"
                trend="Siswa"
                trendType="neutral"
                subtitle="Siswa di seluruh kelas Anda"
                layout="4-row"
                :href="route('tutor.classes.index')"
            />

            <x-cressco.project-card
                title="Jadwal Mingguan"
                icon="calendar"
                iconColor="text-gray-500"
                value="{{ $weeklySchedulesCount }}"
                trend="Sesi / Minggu"
                trendType="neutral"
                subtitle="Total slot jadwal reguler"
                layout="4-row"
                :href="route('tutor.schedules.index')"
            />

            <x-cressco.project-card
                title="Sesi Selesai"
                icon="check-circle"
                iconColor="text-terracotta-500"
                value="{{ $completedSessionsCount }}"
                trend="Bulan Ini"
                trendType="positive"
                subtitle="Sesi mengajar terlaksana"
                layout="4-row"
                :href="route('tutor.history.index')"
            />
        </div>

        <!-- ROW 1: Jadwal Mengajar Hari Ini & Kalender (Aligned Grid Row) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-stretch">
            
            <!-- Left: Jadwal Mengajar Hari Ini -->
            <div class="lg:col-span-7 xl:col-span-8 flex flex-col">
                <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs flex-1 flex flex-col justify-between space-y-5">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold border border-terracotta-200/60 shadow-2xs">
                                    <x-cressco.icon-helper name="clock" class="w-4 h-4" />
                                </div>
                                <div>
                                    <h2 class="text-base font-bold text-gray-900">Jadwal Mengajar Hari Ini</h2>
                                    <p class="text-xs text-gray-500 mt-0.5">{{ $days[$today->dayOfWeek] }}, {{ $today->translatedFormat('d F Y') }}</p>
                                </div>
                            </div>

                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $todaySchedules->isNotEmpty() || $todaySessions->isNotEmpty() ? 'bg-terracotta-50 text-terracotta-700 border border-terracotta-200' : 'bg-gray-100 text-gray-600' }}">
                                {{ max($todaySchedules->count(), $todaySessions->count()) }} Jadwal
                            </span>
                        </div>

                        <div class="mt-4">
                            @if ($todaySessions->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach ($todaySessions as $session)
                                        <a href="{{ route('tutor.classes.show', $session->class_id) }}" class="block">
                                            <x-cressco.schedule-card
                                                :title="$session->classModel?->name ?? 'Kelas'"
                                                :description="($session->classModel?->subject ?? 'Umum') . ' • Cabang: ' . ($session->branch?->name ?? 'Cabang') . ($session->room ? ' • Ruang ' . $session->room : '')"
                                                :time="substr($session->start_time, 0, 5) . ' - ' . substr($session->end_time, 0, 5) . ' WIB'"
                                                color="terracotta"
                                            />
                                        </a>
                                    @endforeach
                                </div>
                            @elseif ($todaySchedules->isNotEmpty())
                                <div class="space-y-3">
                                    @foreach ($todaySchedules as $schedule)
                                        <a href="{{ route('tutor.classes.show', $schedule->class_id) }}" class="block">
                                            <x-cressco.schedule-card
                                                :title="$schedule->class?->name ?? 'Kelas'"
                                                :description="($schedule->class?->subject ?? 'Umum') . ' • Cabang: ' . ($schedule->class?->branch?->name ?? 'Cabang') . ($schedule->room ? ' • Ruang ' . $schedule->room : '')"
                                                :time="substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5) . ' WIB'"
                                                color="terracotta"
                                            />
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="py-10 text-center">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 mx-auto flex items-center justify-center mb-3">
                                        <x-cressco.icon-helper name="calendar" class="w-6 h-6 text-gray-400" />
                                    </div>
                                    <h3 class="text-xs font-bold text-gray-800">Tidak Ada Jadwal Mengajar Hari Ini</h3>
                                    <p class="text-[11px] text-gray-500 mt-1">
                                        Anda tidak memiliki sesi mengajar yang terjadwal untuk hari ini.
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Calendar (October 2026) -->
            <div class="lg:col-span-5 xl:col-span-4 flex flex-col">
                <div class="h-full flex flex-col">
                    <x-cressco.calendar
                        :month="$monthName"
                        :days="$calendarDays"
                        :prevUrl="$prevMonthUrl"
                        :nextUrl="$nextMonthUrl"
                    />
                </div>
            </div>

        </div>

        <!-- ROW 2: Sesi Mengajar Mendatang (Full Width Card) -->
        <div class="w-full">
            <div class="bg-white rounded-3xl border border-gray-200/80 p-6 shadow-xs space-y-5">
                <div>
                    <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-terracotta-50 text-terracotta-700 flex items-center justify-center font-bold border border-terracotta-200/60 shadow-2xs">
                                <x-cressco.icon-helper name="calendar" class="w-4.5 h-4.5" />
                            </div>
                            <div>
                                <h2 class="text-base font-bold text-gray-900">Sesi Mengajar Mendatang</h2>
                                <p class="text-xs text-gray-500 mt-0.5">Daftar sesi mengajar Anda dalam beberapa hari ke depan</p>
                            </div>
                        </div>

                        <a href="{{ route('tutor.schedules.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-gray-50 hover:bg-gray-100 text-xs font-bold text-terracotta-600 hover:text-terracotta-700 transition border border-gray-200/70">
                            <span>Lihat Semua Jadwal</span>
                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5" />
                        </a>
                    </div>

                    <div class="mt-3">
                        @if ($upcomingSessions->isNotEmpty())
                            <div class="divide-y divide-gray-100">
                                @foreach ($upcomingSessions as $session)
                                    <div class="py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-gray-50/50 px-2 rounded-xl transition">
                                        <div class="flex items-center gap-3.5 min-w-0">
                                            <div class="text-center px-3.5 py-2 rounded-xl bg-terracotta-50 border border-terracotta-200/70 shrink-0">
                                                <div class="text-[10px] font-bold text-terracotta-700 uppercase">
                                                    {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('M') : '' }}
                                                </div>
                                                <div class="text-sm font-extrabold text-terracotta-900">
                                                    {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->format('d') : '' }}
                                                </div>
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2">
                                                    <h4 class="text-sm font-bold text-gray-900 truncate">
                                                        {{ $session->classModel?->name ?? 'Kelas' }}
                                                    </h4>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700">
                                                        {{ $session->branch?->name ?? 'Cabang' }}
                                                    </span>
                                                </div>
                                                <div class="text-xs text-gray-500 mt-1 flex flex-wrap items-center gap-x-2 gap-y-0.5">
                                                    <span class="font-medium text-gray-700">{{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('l') : '' }}</span>
                                                    <span>•</span>
                                                    <span>{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB</span>
                                                    @if ($session->room)
                                                        <span>•</span>
                                                        <span class="text-gray-600">Ruang {{ $session->room }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0 self-end sm:self-center">
                                            @if ($session->status === 'completed')
                                                <x-cressco.badge variant="success" size="sm">Selesai</x-cressco.badge>
                                            @elseif ($session->status === 'cancelled')
                                                <x-cressco.badge variant="danger" size="sm">Dibatalkan</x-cressco.badge>
                                            @else
                                                <x-cressco.badge variant="info" size="sm">Terjadwal</x-cressco.badge>
                                            @endif

                                            <a href="{{ route('tutor.classes.show', $session->class_id) }}"
                                               class="p-1.5 rounded-lg text-gray-400 hover:text-terracotta-600 hover:bg-gray-100 transition"
                                               title="Detail Kelas">
                                                <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-10 text-center text-xs text-gray-400">
                                Belum ada jadwal sesi khusus yang dibuat untuk periode mendatang.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</x-tutor-layout>
