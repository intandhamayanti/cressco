<x-tutor-layout :tenant="$tenant" title="Riwayat Mengajar">
    <x-slot:breadcrumbSub>Riwayat Mengajar</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto font-sans">
        
        <!-- Header & Description -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-900 tracking-tight">Riwayat Mengajar</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Catatan dan rekapitulasi seluruh sesi pengajaran yang telah selesai Anda laksanakan secara aktual.
                </p>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                <a href="{{ route('tutor.schedules.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition shadow-2xs">
                    <x-cressco.icon-helper name="calendar" class="w-4 h-4 text-gray-500" />
                    <span>Lihat Jadwal Planner</span>
                </a>
            </div>
        </div>

        <!-- Metric Summary Cards (Standard Cressco Design) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Sesi Selesai"
                icon="check-circle"
                iconColor="text-terracotta-500"
                value="{{ $totalCompletedSessions }}"
                trend="Terlaksana"
                trendType="positive"
                subtitle="Sesi mengajar diselesaikan"
                layout="4-row"
            />

            <x-cressco.project-card
                title="Total Jam Mengajar"
                icon="clock"
                iconColor="text-gray-500"
                value="{{ $totalHoursFormatted }}"
                trend="Jam"
                trendType="neutral"
                subtitle="Durasi mengajar akumulatif"
                layout="4-row"
            />

            <x-cressco.project-card
                title="Total Presensi Siswa"
                icon="users"
                iconColor="text-terracotta-500"
                value="{{ $totalStudentsTaught }}"
                trend="Presensi"
                trendType="terracotta"
                subtitle="Akumulasi kehadiran siswa"
                layout="4-row"
            />

            <x-cressco.project-card
                title="Kelas Diampu"
                icon="academic"
                iconColor="text-gray-500"
                value="{{ $distinctClassesCount }}"
                trend="Rombel"
                trendType="neutral"
                subtitle="Rombongan belajar aktif"
                layout="4-row"
                :href="route('tutor.classes.index')"
            />
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('tutor.history.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                <!-- Search -->
                <div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari materi / kelas / ruang..."
                           class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                </div>

                <!-- Branch Filter -->
                <div>
                    <select name="branch_id"
                            class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                        <option value="all">Semua Cabang</option>
                        @foreach ($tutorBranches as $branch)
                            <option value="{{ $branch->id }}" {{ $selectedBranch === $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Filter -->
                <div>
                    <select name="class_id"
                            class="w-full text-xs font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-3 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                        <option value="all">Semua Kelas</option>
                        @foreach ($tutorClasses as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClass === $cls->id ? 'selected' : '' }}>
                                {{ $cls->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-1.5">
                    <input type="date"
                           name="date_from"
                           value="{{ $dateFrom }}"
                           placeholder="Dari"
                           class="w-full text-[11px] font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-2 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                    <input type="date"
                           name="date_to"
                           value="{{ $dateTo }}"
                           placeholder="Sampai"
                           class="w-full text-[11px] font-medium bg-gray-50/70 border border-gray-200 rounded-xl px-2 py-2 focus:bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-bold text-xs shadow-xs transition cursor-pointer">
                        Filter
                    </button>
                    @if ($search || $selectedBranch !== 'all' || $selectedClass !== 'all' || $dateFrom || $dateTo)
                        <a href="{{ route('tutor.history.index') }}" class="p-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-500 text-xs font-bold transition" title="Reset Filter">
                            <x-cressco.icon-helper name="refresh" class="w-4 h-4" />
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- History Table -->
        <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
            @if ($sessions->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                <th class="p-4">Tanggal & Jam</th>
                                <th class="p-4">Durasi</th>
                                <th class="p-4">Kelas & Cabang</th>
                                <th class="p-4">Materi Diajarkan</th>
                                <th class="p-4 text-center">Kehadiran Siswa</th>
                                <th class="p-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            @foreach ($sessions as $session)
                                @php
                                    $durationMinutes = 0;
                                    if ($session->start_time && $session->end_time) {
                                        $sTime = \Carbon\Carbon::parse('2000-01-01 ' . $session->start_time);
                                        $eTime = \Carbon\Carbon::parse('2000-01-01 ' . $session->end_time);
                                        $durationMinutes = abs($eTime->diffInMinutes($sTime));
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50/60 transition">
                                    <!-- Tanggal & Jam -->
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">
                                            {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') : '-' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 font-medium mt-0.5">
                                            {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                                        </div>
                                    </td>

                                    <!-- Durasi -->
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200/60">
                                            {{ $durationMinutes }} Menit
                                        </span>
                                    </td>

                                    <!-- Kelas & Cabang -->
                                    <td class="p-4">
                                        <div class="font-bold text-gray-900">
                                            {{ $session->classModel?->name ?? 'Kelas' }}
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1.5">
                                            <span>{{ $session->branch?->name ?? '-' }}</span>
                                            <span>•</span>
                                            <span>Ruang: {{ $session->room ?? '-' }}</span>
                                        </div>
                                    </td>

                                    <!-- Materi -->
                                    <td class="p-4 max-w-sm">
                                        @if ($session->material)
                                            <p class="text-xs font-semibold text-gray-800 line-clamp-1">{{ $session->material }}</p>
                                        @else
                                            <span class="text-[11px] text-gray-400 italic">Materi tidak dicatat</span>
                                        @endif
                                        @if ($session->notes)
                                            <p class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">{{ $session->notes }}</p>
                                        @endif
                                    </td>

                                    <!-- Kehadiran Siswa -->
                                    <td class="p-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-800">
                                            {{ $session->student_attendances_count }} Siswa
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="p-4 text-right">
                                        <a href="{{ route('tutor.sessions.show', $session->id) }}"
                                           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-terracotta-50 text-terracotta-700 hover:bg-terracotta-100 font-bold text-xs transition shadow-2xs">
                                            <span>Lihat Jurnal & Presensi</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5" />
                                        </a>
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
                <div class="p-12 text-center space-y-3">
                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 mx-auto flex items-center justify-center">
                        <x-cressco.icon-helper name="clock" class="w-6 h-6" />
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-gray-900">Belum ada riwayat mengajar selesai</h4>
                        <p class="text-xs text-gray-500">Sesi mengajar yang berstatus 'Selesai' akan muncul otomatis di sini.</p>
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-tutor-layout>
