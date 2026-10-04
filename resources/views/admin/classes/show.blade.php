<x-admin-layout :tenant="$tenant" :title="'Detail Kelas - ' . $class->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('admin.classes.index') }}" class="hover:text-primary-600 transition-colors">Kelas</a>
        <span class="text-gray-300">/</span>
        <span>{{ $class->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             activeTab: 'schedules',
             editModalOpen: false,
             toggleModalOpen: false,
             assignTutorModalOpen: false,
             toggleTutorModalOpen: false,
             activeAssignment: null,
             addScheduleModalOpen: false,
             editScheduleModalOpen: false,
             toggleScheduleModalOpen: false,
             deleteScheduleModalOpen: false,
             activeSchedule: { id: '', scheduled_tutor_id: '', day_of_week: '1', start_time: '14:00', end_time: '15:30', room: '', starts_on: '{{ now()->toDateString() }}', ends_on: '', status: 'active' },
             openEditSchedule(sch) {
                 this.activeSchedule = {
                     id: sch.id,
                     scheduled_tutor_id: sch.scheduled_tutor_id,
                     day_of_week: sch.day_of_week,
                     start_time: sch.start_time.substring(0, 5),
                     end_time: sch.end_time.substring(0, 5),
                     room: sch.room || '',
                     starts_on: sch.starts_on || '',
                     ends_on: sch.ends_on || '',
                     status: sch.status || 'active'
                 };
                 this.editScheduleModalOpen = true;
             }
         }">

        <!-- Back Button & Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-2">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.classes.index') }}"
                   class="inline-flex items-center justify-center w-9 h-9 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-xs">
                    <x-cressco.icon-helper name="arrow-left" class="w-4 h-4" />
                </a>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $class->name }}</h1>
                        @if($class->status === 'active')
                            <x-cressco.badge variant="success" size="sm" dot>Aktif</x-cressco.badge>
                        @else
                            <x-cressco.badge variant="neutral" size="sm" dot>Nonaktif</x-cressco.badge>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Cabang: <span class="font-medium text-gray-700">{{ $class->branch?->name ?? '-' }}</span> &bull; 
                        Mata Pelajaran: <span class="font-medium text-gray-700">{{ $class->subject ?? '-' }}</span> &bull; 
                        Tingkat: <span class="font-medium text-gray-700">{{ $class->level ?? '-' }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <x-cressco.button variant="outline" size="sm" leadingIcon="pencil" @click="editModalOpen = true">
                    Edit Kelas
                </x-cressco.button>
                <x-cressco.button variant="{{ $class->status === 'active' ? 'danger' : 'success' }}" size="sm" @click="toggleModalOpen = true">
                    {{ $class->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                </x-cressco.button>
            </div>
        </div>

        <!-- Metric Overview Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="users" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Siswa Terdaftar</div>
                    <div class="text-lg font-bold text-gray-900 mt-0.5">
                        {{ $class->enrollments->where('status', 'active')->count() }}
                        @if($class->capacity)
                            <span class="text-xs font-normal text-gray-500">/ {{ $class->capacity }} Max</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="user-check" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Tutor Pengajar</div>
                    <div class="text-lg font-bold text-gray-900 mt-0.5">
                        {{ $class->tutorAssignments->where('status', 'active')->count() }}
                        <span class="text-xs font-normal text-gray-500">Aktif</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="calendar" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Jadwal Mingguan</div>
                    <div class="text-lg font-bold text-gray-900 mt-0.5">
                        {{ $class->schedules->where('status', 'active')->count() }}
                        <span class="text-xs font-normal text-gray-500">Rutin</span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="building" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Kapasitas Kursi</div>
                    <div class="text-lg font-bold text-gray-900 mt-0.5">
                        @if($class->capacity)
                            @php
                                $enrolled = $class->enrollments->where('status', 'active')->count();
                                $remaining = max(0, $class->capacity - $enrolled);
                            @endphp
                            {{ $remaining }}
                            <span class="text-xs font-normal text-gray-500">Tersedia</span>
                        @else
                            <span class="text-sm font-semibold text-gray-700">Tidak Terbatas</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="border-b border-gray-200">
            <nav class="flex space-x-6" aria-label="Tabs">
                <button type="button"
                        @click="activeTab = 'schedules'"
                        :class="activeTab === 'schedules' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="calendar" class="w-4 h-4" />
                    <span>Jadwal Rutin Mingguan</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $class->schedules->count() }}</span>
                </button>

                <button type="button"
                        @click="activeTab = 'tutors'"
                        :class="activeTab === 'tutors' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="user-check" class="w-4 h-4" />
                    <span>Penugasan Tutor</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $class->tutorAssignments->count() }}</span>
                </button>

                <button type="button"
                        @click="activeTab = 'students'"
                        :class="activeTab === 'students' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="users" class="w-4 h-4" />
                    <span>Siswa Terdaftar</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $class->enrollments->count() }}</span>
                </button>
            </nav>
        </div>

        <!-- TAB 1: JADWAL RUTIN MINGGUAN -->
        <div x-show="activeTab === 'schedules'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Jadwal Rutin Mingguan</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Atur jadwal pertemuan berkala, jam mengajar, ruangan, dan tutor penanggung jawab sesi.</p>
                </div>
                <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="addScheduleModalOpen = true">
                    Tambah Jadwal Rutin
                </x-cressco.button>
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

            @if($class->schedules->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="calendar" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada jadwal rutin</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Tambahkan jadwal belajar mingguan untuk kelas ini agar sesi belajar dapat tercatat secara otomatis.
                    </p>
                    <div class="mt-4">
                        <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="addScheduleModalOpen = true">
                            Tambah Jadwal Sekarang
                        </x-cressco.button>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Hari & Jam</th>
                                    <th class="py-3.5 px-4">Tutor Pengampu</th>
                                    <th class="py-3.5 px-4">Ruangan</th>
                                    <th class="py-3.5 px-4">Periode Efektif</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($class->schedules as $schedule)
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
                                            <div class="font-medium text-gray-900">{{ $schedule->scheduledTutor?->name ?? 'Belum ditentukan' }}</div>
                                            <div class="text-xs text-gray-500">{{ $schedule->scheduledTutor?->email ?? '-' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($schedule->room)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-gray-100 text-xs font-medium text-gray-700">
                                                    <x-cressco.icon-helper name="building" class="w-3.5 h-3.5 text-gray-500" />
                                                    {{ $schedule->room }}
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400 italic">-</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-600">
                                            <div>Mulai: <span class="font-medium text-gray-800">{{ $schedule->starts_on ? \Carbon\Carbon::parse($schedule->starts_on)->translatedFormat('d M Y') : '-' }}</span></div>
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
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button"
                                                        @click="openEditSchedule({
                                                            id: '{{ $schedule->id }}',
                                                            scheduled_tutor_id: '{{ $schedule->scheduled_tutor_id }}',
                                                            day_of_week: '{{ $schedule->day_of_week }}',
                                                            start_time: '{{ $schedule->start_time }}',
                                                            end_time: '{{ $schedule->end_time }}',
                                                            room: '{{ addslashes($schedule->room ?? '') }}',
                                                            starts_on: '{{ $schedule->starts_on }}',
                                                            ends_on: '{{ $schedule->ends_on ?? '' }}',
                                                            status: '{{ $schedule->status }}'
                                                        })"
                                                        class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                                        title="Edit Jadwal">
                                                    <x-cressco.icon-helper name="pencil" class="w-4 h-4" />
                                                </button>

                                                <form action="{{ route('admin.classes.schedules.toggle-status', [$class, $schedule]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                                            title="{{ $schedule->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                        <x-cressco.icon-helper name="{{ $schedule->status === 'active' ? 'slash' : 'check' }}" class="w-4 h-4" />
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.classes.schedules.destroy', [$class, $schedule]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal rutin ini?')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors shadow-2xs"
                                                            title="Hapus Jadwal">
                                                        <x-cressco.icon-helper name="trash-2" class="w-4 h-4" />
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

        <!-- TAB 2: PENUGASAN TUTOR -->
        <div x-show="activeTab === 'tutors'" class="space-y-4" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Penugasan Tutor Pengajar</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar tutor yang ditugaskan untuk mengajar pada kelas ini.</p>
                </div>
                <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="assignTutorModalOpen = true">
                    Tugaskan Tutor
                </x-cressco.button>
            </div>

            @if($class->tutorAssignments->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="user-check" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada tutor yang ditugaskan</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Tugaskan tutor aktif di cabang {{ $class->branch?->name }} untuk mengajar pada kelas ini.
                    </p>
                    <div class="mt-4">
                        <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="assignTutorModalOpen = true">
                            Tugaskan Tutor Sekarang
                        </x-cressco.button>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Nama Tutor</th>
                                    <th class="py-3.5 px-4">Kontak</th>
                                    <th class="py-3.5 px-4">Periode Penugasan</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($class->tutorAssignments as $assignment)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-primary-50 text-primary-700 font-bold flex items-center justify-center text-xs">
                                                    {{ strtoupper(substr($assignment->tutor?->name ?? 'T', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900">{{ $assignment->tutor?->name ?? 'Tutor Tidak Ditemukan' }}</div>
                                                    <div class="text-xs text-gray-500">Role: {{ ucfirst($assignment->tutor?->role ?? 'tutor') }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs">
                                            <div class="text-gray-900">{{ $assignment->tutor?->email ?? '-' }}</div>
                                            <div class="text-gray-500 mt-0.5">{{ $assignment->tutor?->phone ?? '-' }}</div>
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
                                            <form action="{{ route('admin.classes.tutors.toggle-status', [$class, $assignment]) }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <x-cressco.button variant="{{ $assignment->status === 'active' ? 'outline' : 'primary' }}" size="xs">
                                                    {{ $assignment->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </x-cressco.button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        <!-- TAB 3: SISWA TERDAFTAR (ENROLLMENTS) -->
        <div x-show="activeTab === 'students'" class="space-y-4" style="display: none;">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Siswa Terdaftar (Enrollment)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar siswa yang terdaftar dalam kelas ini.</p>
                </div>
                <a href="{{ route('admin.students.index', ['branch_id' => $class->branch_id]) }}">
                    <x-cressco.button variant="outline" size="sm" leadingIcon="external-link">
                        Lihat Data Siswa
                    </x-cressco.button>
                </a>
            </div>

            @if($class->enrollments->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="users" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada siswa terdaftar</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Siswa yang di-enroll ke dalam kelas ini akan otomatis tampil di sini.
                    </p>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Nama Siswa</th>
                                    <th class="py-3.5 px-4">Nomor Induk</th>
                                    <th class="py-3.5 px-4">Tanggal Bergabung</th>
                                    <th class="py-3.5 px-4">Status Enrollment</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($class->enrollments as $enrollment)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-700 font-bold flex items-center justify-center text-xs">
                                                    {{ strtoupper(substr($enrollment->student?->name ?? 'S', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="font-semibold text-gray-900">{{ $enrollment->student?->name ?? 'Siswa Tidak Ditemukan' }}</div>
                                                    <div class="text-xs text-gray-500">{{ $enrollment->student?->email ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs font-mono text-gray-700">
                                            {{ $enrollment->student?->nis ?? '-' }}
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-600">
                                            {{ $enrollment->enrolled_at ? \Carbon\Carbon::parse($enrollment->enrolled_at)->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($enrollment->status === 'active')
                                                <x-cressco.badge variant="success" size="sm" dot>Aktif</x-cressco.badge>
                                            @elseif($enrollment->status === 'completed')
                                                <x-cressco.badge variant="info" size="sm" dot>Selesai</x-cressco.badge>
                                            @else
                                                <x-cressco.badge variant="neutral" size="sm" dot>{{ ucfirst($enrollment->status) }}</x-cressco.badge>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            @if($enrollment->student)
                                                <a href="{{ route('admin.students.show', $enrollment->student) }}"
                                                   class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center"
                                                   title="Detail Siswa">
                                                    <x-cressco.icon-helper name="eye" class="w-4 h-4" />
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

        <!-- =================== MODALS =================== -->

        <!-- 1. EDIT CLASS MODAL -->
        <div x-show="editModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Edit Data Kelas</h3>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.classes.update', $class) }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Cabang *</label>
                            <select name="branch_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                @foreach($accessibleBranches as $branch)
                                    <option value="{{ $branch->id }}" {{ $class->branch_id === $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nama Kelas *</label>
                            <input type="text" name="name" value="{{ old('name', $class->name) }}" required
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Mata Pelajaran</label>
                                <input type="text" name="subject" value="{{ old('subject', $class->subject) }}"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tingkat / Grade</label>
                                <input type="text" name="level" value="{{ old('level', $class->level) }}"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Kapasitas Maksimal</label>
                                <input type="number" name="capacity" min="1" max="100" value="{{ old('capacity', $class->capacity) }}"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status *</label>
                                <select name="status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="active" {{ $class->status === 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="inactive" {{ $class->status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
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

        <!-- 2. TOGGLE CLASS STATUS MODAL -->
        <div x-show="toggleModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="toggleModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="w-12 h-12 rounded-full {{ $class->status === 'active' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }} flex items-center justify-center mx-auto mb-4">
                        <x-cressco.icon-helper name="{{ $class->status === 'active' ? 'alert-triangle' : 'check' }}" class="w-6 h-6" />
                    </div>

                    <h3 class="text-base font-bold text-gray-900 text-center">
                        {{ $class->status === 'active' ? 'Nonaktifkan Kelas?' : 'Aktifkan Kelas?' }}
                    </h3>

                    <p class="text-xs text-gray-500 text-center mt-2">
                        @if($class->status === 'active')
                            Kelas <strong>{{ $class->name }}</strong> akan dinonaktifkan dari jadwal aktif operasional.
                        @else
                            Kelas <strong>{{ $class->name }}</strong> akan diaktifkan kembali.
                        @endif
                    </p>

                    <form action="{{ route('admin.classes.toggle-status', $class) }}" method="POST" class="mt-6 flex items-center justify-center gap-3">
                        @csrf
                        @method('PATCH')
                        <x-cressco.button variant="outline" size="md" type="button" @click="toggleModalOpen = false">
                            Batal
                        </x-cressco.button>
                        <x-cressco.button variant="{{ $class->status === 'active' ? 'danger' : 'success' }}" size="md" type="submit">
                            {{ $class->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                        </x-cressco.button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. ASSIGN TUTOR MODAL -->
        <div x-show="assignTutorModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="assignTutorModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Tugaskan Tutor Pengajar</h3>
                        <button type="button" @click="assignTutorModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.classes.assign-tutor', $class) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Pilih Tutor *</label>
                            <select name="tutor_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">-- Pilih Tutor Aktif --</option>
                                @foreach($tutors as $tutor)
                                    <option value="{{ $tutor->id }}">{{ $tutor->name }} ({{ $tutor->email }})</option>
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
                            <x-cressco.button variant="outline" size="md" type="button" @click="assignTutorModalOpen = false">
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

        <!-- 4. ADD SCHEDULE MODAL -->
        <div x-show="addScheduleModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="addScheduleModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Tambah Jadwal Rutin Mingguan</h3>
                        <button type="button" @click="addScheduleModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.classes.schedules.store', $class) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tutor Pengampu Sesi *</label>
                            <select name="scheduled_tutor_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">-- Pilih Tutor --</option>
                                @foreach($tutors as $tutor)
                                    <option value="{{ $tutor->id }}">{{ $tutor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Hari *</label>
                                <select name="day_of_week" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="1">Senin</option>
                                    <option value="2">Selasa</option>
                                    <option value="3">Rabu</option>
                                    <option value="4">Kamis</option>
                                    <option value="5">Jumat</option>
                                    <option value="6">Sabtu</option>
                                    <option value="7">Minggu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Jam Mulai *</label>
                                <input type="time" name="start_time" value="14:00" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Jam Selesai *</label>
                                <input type="time" name="end_time" value="15:30" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Ruangan (Opsional)</label>
                            <input type="text" name="room" placeholder="Contoh: Lab Komputer 1 / Ruang 204"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Berlaku Mulai *</label>
                                <input type="date" name="starts_on" value="{{ now()->toDateString() }}" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Berlaku Sampai (Opsional)</label>
                                <input type="date" name="ends_on"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="addScheduleModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Tambahkan Jadwal
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 5. EDIT SCHEDULE MODAL -->
        <div x-show="editScheduleModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="editScheduleModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Edit Jadwal Rutin</h3>
                        <button type="button" @click="editScheduleModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'{{ url('admin/classes/' . $class->id . '/schedules') }}/' + activeSchedule.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Tutor Pengampu Sesi *</label>
                            <select name="scheduled_tutor_id" x-model="activeSchedule.scheduled_tutor_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                @foreach($tutors as $tutor)
                                    <option value="{{ $tutor->id }}">{{ $tutor->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Hari *</label>
                                <select name="day_of_week" x-model="activeSchedule.day_of_week" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="1">Senin</option>
                                    <option value="2">Selasa</option>
                                    <option value="3">Rabu</option>
                                    <option value="4">Kamis</option>
                                    <option value="5">Jumat</option>
                                    <option value="6">Sabtu</option>
                                    <option value="7">Minggu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Jam Mulai *</label>
                                <input type="time" name="start_time" x-model="activeSchedule.start_time" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Jam Selesai *</label>
                                <input type="time" name="end_time" x-model="activeSchedule.end_time" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Ruangan (Opsional)</label>
                            <input type="text" name="room" x-model="activeSchedule.room"
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Berlaku Mulai *</label>
                                <input type="date" name="starts_on" x-model="activeSchedule.starts_on" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Berlaku Sampai</label>
                                <input type="date" name="ends_on" x-model="activeSchedule.ends_on"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status *</label>
                            <select name="status" x-model="activeSchedule.status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="editScheduleModalOpen = false">
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

    </div>
</x-admin-layout>
