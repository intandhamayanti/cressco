<x-tutor-layout :tenant="$tenant" :title="'Kelas ' . $class->name">
    <x-slot:breadcrumbSub>{{ $class->name }}</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto" x-data="{ activeTab: 'students' }">
        
        <!-- Header Banner with Back Button -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3">
                <a href="{{ route('tutor.classes.index') }}" class="p-2 rounded-xl bg-white border border-gray-200 text-gray-600 hover:text-gray-900 hover:bg-gray-50 transition shadow-2xs">
                    <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-700">
                            {{ $class->branch?->name ?? 'Cabang' }}
                        </span>
                        @if ($class->level)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-terracotta-50 text-terracotta-800 border border-terracotta-200">
                                {{ $class->level }}
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-900 text-white">
                            Aktif
                        </span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 tracking-tight mt-1">{{ $class->name }}</h1>
                </div>
            </div>

            <div class="flex items-center gap-2 self-start sm:self-center">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-terracotta-50 border border-terracotta-200 text-xs font-bold text-terracotta-800">
                    <x-cressco.icon-helper name="academic" class="w-4 h-4 text-terracotta-600" />
                    <span>{{ $class->enrollments->where('status', 'active')->count() }} / {{ $class->capacity ?? 0 }} Siswa Terdaftar</span>
                </span>
            </div>
        </div>

        <!-- Class Quick Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-3xl border border-gray-200/80 p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-gray-500">Mata Pelajaran</div>
                <div class="text-sm font-bold text-gray-900 mt-1">{{ $class->subject ?? 'Umum' }}</div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200/80 p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-gray-500">Cabang</div>
                <div class="text-sm font-bold text-gray-900 mt-1">{{ $class->branch?->name ?? '-' }}</div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200/80 p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-gray-500">Jadwal Reguler</div>
                <div class="text-sm font-bold text-gray-900 mt-1">{{ $class->schedules->count() }} Sesi / Minggu</div>
            </div>

            <div class="bg-white rounded-3xl border border-gray-200/80 p-4 shadow-xs">
                <div class="text-[11px] font-semibold text-gray-500">Total Sesi Terlaksana</div>
                <div class="text-sm font-bold text-terracotta-700 mt-1">{{ $class->teachingSessions->where('status', 'completed')->count() }} Sesi</div>
            </div>
        </div>

        <!-- Tab Navigation Buttons -->
        <div class="flex items-center gap-2 border-b border-gray-200/80">
            <button type="button"
                    @click="activeTab = 'students'"
                    :class="activeTab === 'students' ? 'border-terracotta-600 text-terracotta-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-semibold'"
                    class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2 cursor-pointer">
                <x-cressco.icon-helper name="academic" class="w-4 h-4" />
                <span>Siswa Terdaftar ({{ $class->enrollments->count() }})</span>
            </button>

            <button type="button"
                    @click="activeTab = 'schedules'"
                    :class="activeTab === 'schedules' ? 'border-terracotta-600 text-terracotta-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-semibold'"
                    class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2 cursor-pointer">
                <x-cressco.icon-helper name="calendar" class="w-4 h-4" />
                <span>Jadwal Reguler ({{ $class->schedules->count() }})</span>
            </button>

            <button type="button"
                    @click="activeTab = 'sessions'"
                    :class="activeTab === 'sessions' ? 'border-terracotta-600 text-terracotta-800 font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-semibold'"
                    class="py-3 px-4 text-xs border-b-2 transition flex items-center gap-2 cursor-pointer">
                <x-cressco.icon-helper name="clock" class="w-4 h-4" />
                <span>Riwayat Sesi ({{ $class->teachingSessions->count() }})</span>
            </button>
        </div>

        <!-- TAB 1: ENROLLED STUDENTS -->
        <div x-show="activeTab === 'students'" class="space-y-4">
            <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Daftar Siswa Kelas {{ $class->name }}</h3>
                        <p class="text-[11px] text-gray-500">Informasi siswa dan kontak darurat/wali untuk koordinasi belajar</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">{{ $class->enrollments->count() }} Total Siswa</span>
                </div>

                @if ($class->enrollments->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="p-4">Nama Siswa</th>
                                    <th class="p-4">Jenis Kelamin</th>
                                    <th class="p-4">Telepon Siswa</th>
                                    <th class="p-4">Orang Tua / Wali</th>
                                    <th class="p-4">No. Telepon / WA Wali</th>
                                    <th class="p-4 text-right">Status Enrollment</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @foreach ($class->enrollments as $enrollment)
                                    @php $student = $enrollment->student; @endphp
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <!-- Nama Siswa -->
                                        <td class="p-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($student?->name ?? 'S', 0, 2)) }}
                                                </div>
                                                <div class="font-bold text-gray-900">
                                                    {{ $student?->name ?? 'Siswa' }}
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Gender -->
                                        <td class="p-4 font-medium text-gray-700">
                                            {{ $student?->gender === 'male' ? 'Laki-laki' : ($student?->gender === 'female' ? 'Perempuan' : '-') }}
                                        </td>

                                        <!-- Telepon Siswa -->
                                        <td class="p-4 text-gray-600">
                                            {{ $student?->phone ?? '-' }}
                                        </td>

                                        <!-- Orang Tua -->
                                        <td class="p-4 font-medium text-gray-900">
                                            {{ $student?->parent_name ?? '-' }}
                                        </td>

                                        <!-- Kontak Wali -->
                                        <td class="p-4 text-gray-600">
                                            {{ $student?->parent_phone ?? '-' }}
                                        </td>

                                        <!-- Status -->
                                        <td class="p-4 text-right">
                                            @if ($enrollment->status === 'active')
                                                <x-cressco.badge variant="success" dot>Aktif</x-cressco.badge>
                                            @else
                                                <x-cressco.badge variant="neutral" dot>{{ ucfirst($enrollment->status) }}</x-cressco.badge>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-xs text-gray-500">
                        Belum ada siswa yang terdaftar aktif di kelas ini.
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 2: REGULAR SCHEDULES -->
        <div x-show="activeTab === 'schedules'" x-cloak class="space-y-4">
            <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Jadwal Rutin Mingguan</h3>
                        <p class="text-[11px] text-gray-500">Waktu pelaksanaan sesi reguler setiap pekan</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">{{ $class->schedules->count() }} Jadwal</span>
                </div>

                @if ($class->schedules->isNotEmpty())
                    <div class="divide-y divide-gray-100">
                        @foreach ($class->schedules as $schedule)
                            <div class="p-4 hover:bg-gray-50/60 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 rounded-2xl bg-terracotta-50 border border-terracotta-200/80 flex flex-col items-center justify-center shrink-0">
                                        <span class="text-[11px] font-extrabold text-terracotta-800 uppercase">
                                            {{ $days[$schedule->day_of_week] ?? '' }}
                                        </span>
                                    </div>

                                    <div>
                                        <div class="text-xs font-bold text-gray-900">
                                            {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-2">
                                            <span>Ruang: <strong class="text-gray-700">{{ $schedule->room ?? '-' }}</strong></span>
                                            <span>•</span>
                                            <span>Tutor: <strong class="text-gray-700">{{ $schedule->scheduledTutor?->name ?? 'Tutor Terjadwal' }}</strong></span>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <x-cressco.badge variant="info" dot>Jadwal Aktif</x-cressco.badge>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-12 text-center text-xs text-gray-500">
                        Belum ada jadwal mingguan yang dikonfigurasi untuk kelas ini.
                    </div>
                @endif
            </div>
        </div>

        <!-- TAB 3: TEACHING SESSIONS HISTORY -->
        <div x-show="activeTab === 'sessions'" x-cloak class="space-y-4">
            <div class="bg-white rounded-3xl border border-gray-200/80 shadow-xs overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Riwayat Sesi Mengajar</h3>
                        <p class="text-[11px] text-gray-500">Catatan sesi kelas yang telah berlangsung atau terjadwal</p>
                    </div>
                    <span class="text-xs font-semibold text-gray-500">{{ $class->teachingSessions->count() }} Sesi</span>
                </div>

                @if ($class->teachingSessions->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50/50 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                                    <th class="p-4">Tanggal & Waktu</th>
                                    <th class="p-4">Tutor Pengajar</th>
                                    <th class="p-4">Ruang</th>
                                    <th class="p-4">Status Sesi</th>
                                    <th class="p-4 text-right">Presensi Siswa</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                @foreach ($class->teachingSessions as $session)
                                    <tr class="hover:bg-gray-50/60 transition">
                                        <td class="p-4">
                                            <div class="font-bold text-gray-900">
                                                {{ $session->session_date ? \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d F Y') : '-' }}
                                            </div>
                                            <div class="text-[11px] text-gray-500 mt-0.5">
                                                {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                                            </div>
                                        </td>

                                        <td class="p-4">
                                            <span class="font-medium text-gray-900">
                                                {{ $session->actualTutor?->name ?? ($session->scheduledTutor?->name ?? '-') }}
                                            </span>
                                        </td>

                                        <td class="p-4 text-gray-600">
                                            {{ $session->room ?? '-' }}
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

                                        <td class="p-4 text-right flex items-center justify-end gap-2">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-800">
                                                {{ $session->student_attendances_count }} Siswa
                                            </span>
                                            <a href="{{ route('tutor.sessions.show', $session->id) }}" class="p-1 rounded-lg hover:bg-terracotta-50 text-terracotta-700 font-bold transition" title="Buka Sesi & Presensi">
                                                <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-xs text-gray-500">
                        Belum ada sesi mengajar yang tercatat untuk kelas ini.
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-tutor-layout>
