<x-admin-layout :tenant="$tenant" :title="'Detail Tutor - ' . $tutor->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('admin.tutors.index') }}" class="hover:text-primary-600 transition-colors">Tutor</a>
        <span class="text-gray-300">/</span>
        <span>{{ $tutor->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             activeTab: 'classes',
             editModalOpen: false,
             toggleModalOpen: false,
             assignClassModalOpen: false,
             editTutor: {
                 name: '{{ addslashes($tutor->name) }}',
                 email: '{{ addslashes($tutor->email) }}',
                 phone: '{{ addslashes($tutor->phone ?? '') }}',
                 status: '{{ $tutor->status }}'
             }
         }">

        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.tutors.index') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-xs">
                    <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                </a>
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-base shrink-0">
                        {{ strtoupper(substr($tutor->name, 0, 2)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $tutor->name }}</h1>
                            @if($tutor->status === 'active')
                                <x-cressco.badge variant="success" size="sm" dot>Aktif</x-cressco.badge>
                            @else
                                <x-cressco.badge variant="neutral" size="sm" dot>Nonaktif</x-cressco.badge>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Email: <span class="font-medium text-gray-700">{{ $tutor->email }}</span> &bull; 
                            HP: <span class="font-medium text-gray-700">{{ $tutor->phone ?? '-' }}</span> &bull; 
                            Bergabung: <span class="font-medium text-gray-700">{{ $tutor->created_at ? $tutor->created_at->translatedFormat('d F Y') : '-' }}</span>
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-cressco.button variant="outline" size="sm" leadingIcon="pencil" @click="editModalOpen = true">
                    Edit Profil
                </x-cressco.button>
                <x-cressco.button variant="{{ $tutor->status === 'active' ? 'danger' : 'success' }}" size="sm" @click="toggleModalOpen = true">
                    {{ $tutor->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                </x-cressco.button>
            </div>
        </div>

        <!-- Operational Stat Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="layers" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Kelas Diampu</div>
                    <div class="text-xl font-bold text-gray-900 mt-0.5">{{ $activeClassesCount }} Kelas</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="calendar" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Jadwal Mingguan</div>
                    <div class="text-xl font-bold text-purple-700 mt-0.5">{{ $weeklySchedulesCount }} Sesi / Minggu</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="check-circle" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Sesi Selesai Diajar</div>
                    <div class="text-xl font-bold text-emerald-700 mt-0.5">{{ $completedSessionsCount }} Sesi</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="users" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Presensi Dicatat</div>
                    <div class="text-xl font-bold text-amber-700 mt-0.5">{{ $totalAttendancesCount }} Kehadiran</div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-6" aria-label="Tabs">
                <button type="button"
                        @click="activeTab = 'classes'"
                        :class="activeTab === 'classes' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="layers" class="w-4 h-4" />
                    <span>Kelas yang Diampu</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $tutor->tutorAssignments->count() }}</span>
                </button>

                <button type="button"
                        @click="activeTab = 'schedules'"
                        :class="activeTab === 'schedules' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="calendar" class="w-4 h-4" />
                    <span>Jadwal Mengajar Rutin</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $tutor->scheduledSchedules->count() }}</span>
                </button>

                <button type="button"
                        @click="activeTab = 'sessions'"
                        :class="activeTab === 'sessions' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="clock" class="w-4 h-4" />
                    <span>Riwayat Sesi Pengajaran</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $tutor->actualTeachingSessions->count() }}</span>
                </button>
            </nav>
        </div>

        <!-- TAB 1: KELAS YANG DIAMPU -->
        <div x-show="activeTab === 'classes'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Penugasan Kelas Belajar</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar kelas yang diampu tutor {{ $tutor->name }} di cabang Anda.</p>
                </div>
                <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="assignClassModalOpen = true">
                    Tugaskan ke Kelas
                </x-cressco.button>
            </div>

            @if($tutor->tutorAssignments->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="layers" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada penugasan kelas</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Tutor ini belum ditugaskan untuk mengajar pada kelas manapun di cabang Anda.
                    </p>
                    <div class="mt-4">
                        <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="assignClassModalOpen = true">
                            Tugaskan ke Kelas Sekarang
                        </x-cressco.button>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Nama Kelas</th>
                                    <th class="py-3.5 px-4">Cabang</th>
                                    <th class="py-3.5 px-4">Mata Pelajaran</th>
                                    <th class="py-3.5 px-4">Periode Penugasan</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($tutor->tutorAssignments as $assignment)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4">
                                            @if($assignment->class)
                                                <a href="{{ route('admin.classes.show', $assignment->class) }}" class="font-semibold text-gray-900 hover:text-primary-600 transition-colors">
                                                    {{ $assignment->class->name }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-gray-900">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-xs font-medium text-gray-700">
                                            {{ $assignment->class?->branch?->name ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-xs">
                                            {{ $assignment->class?->subject ?? '-' }}
                                            @if($assignment->class?->level)
                                                <span class="text-gray-400">({{ $assignment->class->level }})</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-600">
                                            <div>Mulai: <span class="font-medium text-gray-800">{{ $assignment->started_at ? \Carbon\Carbon::parse($assignment->started_at)->translatedFormat('d M Y') : '-' }}</span></div>
                                            @if($assignment->ended_at)
                                                <div class="text-gray-500 mt-0.5">Selesai: {{ \Carbon\Carbon::parse($assignment->ended_at)->translatedFormat('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($assignment->status === 'active')
                                                <x-cressco.badge variant="success" size="sm" dot>Aktif</x-cressco.badge>
                                            @else
                                                <x-cressco.badge variant="neutral" size="sm" dot>Nonaktif</x-cressco.badge>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                @if($assignment->class)
                                                    <a href="{{ route('admin.classes.show', $assignment->class) }}"
                                                       class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                                       title="Lihat Kelas">
                                                        <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                                    </a>
                                                @endif

                                                <form action="{{ route('admin.tutors.classes.toggle-status', [$tutor, $assignment]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                                            title="{{ $assignment->status === 'active' ? 'Nonaktifkan Penugasan' : 'Aktifkan Penugasan' }}">
                                                        <x-cressco.icon-helper name="{{ $assignment->status === 'active' ? 'slash' : 'check' }}" class="w-4 h-4" />
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 2: JADWAL MENGAJAR RUTIN -->
        <div x-show="activeTab === 'schedules'" class="space-y-4" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Jadwal Mengajar Rutin Mingguan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Jadwal sesi rutin mingguan yang diampu tutor di cabang Anda.</p>
                </div>
            </div>

            @php
                $dayNames = [
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu',
                ];
            @endphp

            @if($tutor->scheduledSchedules->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="calendar" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada jadwal mengajar rutin</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Jadwal rutin mingguan dapat diatur melalui halaman detail kelas masing-masing.
                    </p>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Hari & Jam</th>
                                    <th class="py-3.5 px-4">Kelas</th>
                                    <th class="py-3.5 px-4">Cabang & Ruangan</th>
                                    <th class="py-3.5 px-4">Periode Efektif</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($tutor->scheduledSchedules as $schedule)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-gray-900">
                                                {{ $dayNames[$schedule->day_of_week] ?? 'Hari ' . $schedule->day_of_week }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono mt-0.5">
                                                {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-medium text-gray-900">{{ $schedule->class?->name ?? 'Kelas' }}</div>
                                            <div class="text-xs text-gray-500">{{ $schedule->class?->subject ?? '-' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs">
                                            <div class="font-medium text-gray-800">{{ $schedule->class?->branch?->name ?? '-' }}</div>
                                            <div class="text-gray-500 mt-0.5">{{ $schedule->room ?? 'Ruangan Reguler' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-600">
                                            <div>Mulai: {{ $schedule->starts_on ? \Carbon\Carbon::parse($schedule->starts_on)->translatedFormat('d M Y') : '-' }}</div>
                                            @if($schedule->ends_on)
                                                <div class="text-gray-500 mt-0.5">Sampai: {{ \Carbon\Carbon::parse($schedule->ends_on)->translatedFormat('d M Y') }}</div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($schedule->status === 'active')
                                                <x-cressco.badge variant="success" size="sm" dot>Aktif</x-cressco.badge>
                                            @else
                                                <x-cressco.badge variant="neutral" size="sm" dot>Nonaktif</x-cressco.badge>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            @if($schedule->class)
                                                <a href="{{ route('admin.classes.show', $schedule->class) }}"
                                                   class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center"
                                                   title="Buka Kelas">
                                                    <x-cressco.icon-helper name="external-link" class="w-4 h-4" />
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 3: RIWAYAT SESI PENGAJARAN -->
        <div x-show="activeTab === 'sessions'" class="space-y-4" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Riwayat Sesi Pengajaran & Presensi</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Sesi-sesi yang telah diajar oleh tutor beserta catatan presensi siswa.</p>
                </div>
            </div>

            @if($tutor->actualTeachingSessions->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="clock" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada riwayat sesi mengajar</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Sesi mengajar yang diselesaikan tutor akan tercatat otomatis di sini.
                    </p>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Tanggal & Jam</th>
                                    <th class="py-3.5 px-4">Kelas & Cabang</th>
                                    <th class="py-3.5 px-4">Materi / Topik</th>
                                    <th class="py-3.5 px-4">Presensi Siswa</th>
                                    <th class="py-3.5 px-4">Status Sesi</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($tutor->actualTeachingSessions as $session)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-gray-900">
                                                {{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('d M Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 font-mono mt-0.5">
                                                {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-medium text-gray-900">{{ $session->classModel?->name ?? 'Kelas' }}</div>
                                            <div class="text-xs text-gray-500">{{ $session->branch?->name ?? '-' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-700 max-w-xs truncate">
                                            {{ $session->material ?? $session->notes ?? 'Materi Reguler' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-xs font-semibold text-gray-900">
                                            {{ $session->student_attendances_count }} Siswa
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                                {{ ucfirst($session->status) }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <a href="{{ route('admin.attendances.sessions.show', $session) }}"
                                               class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center"
                                               title="Detail Presensi Sesi">
                                                <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- =================== MODALS =================== -->

        <!-- 1. EDIT TUTOR MODAL -->
        <div x-show="editModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Edit Profil Tutor</h3>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.tutors.update', $tutor) }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap Tutor *</label>
                            <input type="text" name="name" x-model="editTutor.name" required
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Email *</label>
                                <input type="email" name="email" x-model="editTutor.email" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nomor HP</label>
                                <input type="text" name="phone" x-model="editTutor.phone"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Ubah Password</label>
                                <input type="password" name="password" placeholder="Biarkan kosong jika tidak diubah"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status *</label>
                                <select name="status" x-model="editTutor.status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="editModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Perubahan
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. TOGGLE TUTOR STATUS MODAL -->
        <div x-show="toggleModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="toggleModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="w-12 h-12 rounded-full {{ $tutor->status === 'active' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }} flex items-center justify-center mx-auto mb-4">
                        <x-cressco.icon-helper name="{{ $tutor->status === 'active' ? 'alert-triangle' : 'check' }}" class="w-6 h-6" />
                    </div>

                    <h3 class="text-base font-bold text-gray-900 text-center">
                        {{ $tutor->status === 'active' ? 'Nonaktifkan Tutor?' : 'Aktifkan Tutor?' }}
                    </h3>

                    <p class="text-xs text-gray-500 text-center mt-2">
                        @if($tutor->status === 'active')
                            Tutor <strong>{{ $tutor->name }}</strong> akan dinonaktifkan dari jadwal pengajaran aktif.
                        @else
                            Tutor <strong>{{ $tutor->name }}</strong> akan diaktifkan kembali.
                        @endif
                    </p>

                    <form action="{{ route('admin.tutors.toggle-status', $tutor) }}" method="POST" class="mt-6 flex items-center justify-center gap-3">
                        @csrf
                        @method('PATCH')
                        <x-cressco.button variant="outline" size="md" type="button" @click="toggleModalOpen = false">
                            Batal
                        </x-cressco.button>
                        <x-cressco.button variant="{{ $tutor->status === 'active' ? 'danger' : 'success' }}" size="md" type="submit">
                            {{ $tutor->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                        </x-cressco.button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. ASSIGN CLASS MODAL -->
        <div x-show="assignClassModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="assignClassModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Tugaskan Tutor ke Kelas</h3>
                        <button type="button" @click="assignClassModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.tutors.assign-class', $tutor) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Pilih Kelas *</label>
                            <select name="class_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">-- Pilih Kelas Aktif di Cabang Anda --</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }} &bull; Cabang: {{ $c->branch?->name }} ({{ $c->subject ?? '-' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal Mulai *</label>
                                <input type="date" name="started_at" value="{{ now()->toDateString() }}" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tanggal Selesai (Opsional)</label>
                                <input type="date" name="ended_at"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status Penugasan *</label>
                            <select name="status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="active" selected>Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="assignClassModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Penugasan
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
