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
             batchEnrollModalOpen: false,
             quickAddStudentModalOpen: false,
             studentSearchQuery: '',
             selectedStudentIds: [],
             availableStudents: {{ json_encode($availableStudents->map(fn($s) => ['id' => $s->id, 'name' => $s->name, 'phone' => $s->phone ?? '', 'parent_name' => $s->parent_name ?? ''])->values()) }},
             get filteredAvailableStudents() {
                 if (!this.studentSearchQuery.trim()) {
                     return this.availableStudents;
                 }
                 const q = this.studentSearchQuery.toLowerCase();
                 return this.availableStudents.filter(s => s.name.toLowerCase().includes(q) || (s.phone && s.phone.includes(q)) || (s.parent_name && s.parent_name.toLowerCase().includes(q)));
             },
             toggleSelectAll(checked) {
                 if (checked) {
                     this.selectedStudentIds = this.filteredAvailableStudents.map(s => s.id);
                 } else {
                     this.selectedStudentIds = [];
                 }
             },
             activeSchedule: { id: '', scheduled_tutor_id: '', day_of_week: '1', start_time: '14:00', end_time: '15:30', room: '', starts_on: '{{ now()->toDateString() }}', ends_on: '', status: 'active' },
             replaceSessionModalOpen: false,
             activeSessionForReplacement: { id: '', date: '', time: '', scheduled_tutor: '', actual_tutor: '' },
             openReplaceSession(s) {
                 this.activeSessionForReplacement = {
                     id: s.id,
                     date: s.date,
                     time: s.time,
                     scheduled_tutor: s.scheduled_tutor,
                     actual_tutor: s.actual_tutor
                 };
                 this.replaceSessionModalOpen = true;
             },
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

                <button type="button"
                        @click="activeTab = 'sessions'"
                        :class="activeTab === 'sessions' ? 'border-primary-600 text-primary-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 font-medium'"
                        class="py-3 px-1 border-b-2 text-sm flex items-center gap-2 transition-colors">
                    <x-cressco.icon-helper name="book-open" class="w-4 h-4" />
                    <span>Sesi Mengajar</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-600 font-medium">{{ $class->teachingSessions->count() }}</span>
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
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Siswa Terdaftar (Enrollment)</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Kelola siswa yang terdaftar dalam kelas ini langsung dari Detail Kelas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <x-cressco.button variant="primary" size="sm" leadingIcon="user-plus" @click="batchEnrollModalOpen = true; selectedStudentIds = []; studentSearchQuery = '';">
                        Tambah Siswa ke Kelas
                    </x-cressco.button>
                    <x-cressco.button variant="outline" size="sm" leadingIcon="plus" @click="quickAddStudentModalOpen = true;">
                        + Siswa Baru
                    </x-cressco.button>
                </div>
            </div>

            @if($class->enrollments->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="users" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada siswa terdaftar pada kelas ini</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-md mx-auto">
                        Anda dapat memasukkan siswa yang sudah terdaftar di cabang {{ $class->branch?->name }} sekaligus (batch enroll), atau mendaftarkan siswa baru langsung dari sini.
                    </p>
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <x-cressco.button variant="primary" size="sm" leadingIcon="user-plus" @click="batchEnrollModalOpen = true; selectedStudentIds = []; studentSearchQuery = '';">
                            Tambah Siswa Terdaftar
                        </x-cressco.button>
                        <x-cressco.button variant="outline" size="sm" leadingIcon="plus" @click="quickAddStudentModalOpen = true;">
                            Daftarkan Siswa Baru
                        </x-cressco.button>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Nama Siswa</th>
                                    <th class="py-3.5 px-4">Kontak & Orang Tua</th>
                                    <th class="py-3.5 px-4">Tanggal Mulai</th>
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
                                                    <a href="{{ $enrollment->student ? route('admin.students.show', $enrollment->student) : '#' }}" class="font-semibold text-gray-900 hover:text-primary-600 transition">
                                                        {{ $enrollment->student?->name ?? 'Siswa Tidak Ditemukan' }}
                                                    </a>
                                                    <div class="text-xs text-gray-500">{{ $enrollment->student?->gender ?? '-' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs">
                                            <div class="text-gray-900 font-medium">{{ $enrollment->student?->phone ?: '-' }}</div>
                                            <div class="text-gray-500 mt-0.5">Ortu: {{ $enrollment->student?->parent_name ?: '-' }} {{ $enrollment->student?->parent_phone ? "({$enrollment->student->parent_phone})" : '' }}</div>
                                        </td>
                                        <td class="py-3.5 px-4 text-xs text-gray-600">
                                            {{ $enrollment->started_at ? \Carbon\Carbon::parse($enrollment->started_at)->translatedFormat('d M Y') : '-' }}
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
                                            <div class="inline-flex items-center gap-1.5">
                                                @if($enrollment->student)
                                                    <a href="{{ route('admin.students.show', $enrollment->student) }}"
                                                       class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center"
                                                       title="Detail Siswa">
                                                        <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                                    </a>
                                                @endif

                                                <form action="{{ route('admin.classes.enrollments.toggle', [$class, $enrollment]) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs cursor-pointer"
                                                            title="{{ $enrollment->status === 'active' ? 'Nonaktifkan dari Kelas' : 'Aktifkan Kembali' }}">
                                                        <x-cressco.icon-helper name="{{ $enrollment->status === 'active' ? 'slash' : 'check' }}" class="w-4 h-4" />
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.classes.enrollments.destroy', [$class, $enrollment]) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Keluarkan siswa ini dari kelas?')"
                                                      class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="p-1.5 rounded-lg border border-red-200 bg-white text-red-600 hover:bg-red-50 hover:text-red-700 transition-colors shadow-2xs cursor-pointer"
                                                            title="Keluarkan dari Kelas">
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

        <!-- TAB 4: SESI MENGAJAR -->
        <div x-show="activeTab === 'sessions'" class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Sesi Mengajar Kelas</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Daftar sesi belajar yang dihasilkan dari jadwal rutin, status pelaksanaan, tutor aktual/pengganti, dan presensi.</p>
                </div>
            </div>

            @if($class->teachingSessions->isEmpty())
                <div class="bg-white rounded-xl border border-gray-200 p-8 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="calendar" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Belum ada sesi mengajar</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Sesi mengajar dihasilkan secara otomatis dari jadwal rutin mingguan kelas ini.
                    </p>
                </div>
            @else
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <tr>
                                    <th class="py-3.5 px-4">Tanggal & Waktu</th>
                                    <th class="py-3.5 px-4">Ruangan & Topik</th>
                                    <th class="py-3.5 px-4">Tutor Terjadwal</th>
                                    <th class="py-3.5 px-4">Tutor Aktual (Pengajar)</th>
                                    <th class="py-3.5 px-4">Status</th>
                                    <th class="py-3.5 px-4 text-center">Presensi</th>
                                    <th class="py-3.5 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200/70">
                                @foreach($class->teachingSessions as $session)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="py-3.5 px-4">
                                            <div class="font-semibold text-gray-900">
                                                {{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') }}
                                            </div>
                                            <div class="text-xs text-gray-500 mt-0.5">
                                                {{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB
                                            </div>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <div class="font-medium text-gray-800">{{ $session->room ?? '-' }}</div>
                                            @if($session->material || $session->notes)
                                                <div class="text-xs text-gray-500 truncate max-w-xs mt-0.5">
                                                    {{ $session->material ?? $session->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            <span class="font-medium text-gray-800">{{ $session->scheduledTutor?->name ?? '-' }}</span>
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($session->actual_tutor_id && $session->scheduled_tutor_id && $session->actual_tutor_id !== $session->scheduled_tutor_id)
                                                <div>
                                                    <span class="font-bold text-amber-800">{{ $session->actualTutor?->name ?? '-' }}</span>
                                                    <span class="block text-[10px] text-amber-600 font-semibold">(Tutor Pengganti)</span>
                                                </div>
                                            @elseif(is_null($session->actual_tutor_id))
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold bg-red-100 text-red-800">
                                                    Tutor Berhalangan
                                                </span>
                                            @else
                                                <span class="text-gray-700 font-medium">{{ $session->actualTutor?->name ?? '-' }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4">
                                            @if($session->status === 'completed')
                                                <x-cressco.badge variant="success" size="sm" dot>Selesai</x-cressco.badge>
                                            @elseif($session->status === 'cancelled')
                                                <x-cressco.badge variant="danger" size="sm" dot>Dibatalkan</x-cressco.badge>
                                            @else
                                                <x-cressco.badge variant="info" size="sm" dot>Terjadwal</x-cressco.badge>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-center">
                                            @php
                                                $attCount = $session->studentAttendances->count();
                                                $presentCount = $session->studentAttendances->filter(fn($a) => in_array($a->status, ['hadir', 'present']))->count();
                                            @endphp
                                            @if($attCount > 0)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-gray-100 text-gray-800">
                                                    {{ $presentCount }}/{{ $attCount }} Hadir
                                                </span>
                                            @else
                                                <span class="text-xs text-gray-400">Belum diisi</span>
                                            @endif
                                        </td>
                                        <td class="py-3.5 px-4 text-right">
                                            <div class="inline-flex items-center gap-1.5">
                                                <button type="button"
                                                        @click="openReplaceSession({
                                                            id: '{{ $session->id }}',
                                                            date: '{{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') }}',
                                                            time: '{{ substr($session->start_time, 0, 5) }} - {{ substr($session->end_time, 0, 5) }} WIB',
                                                            scheduled_tutor: '{{ addslashes($session->scheduledTutor?->name ?? '-') }}',
                                                            actual_tutor: '{{ addslashes($session->actualTutor?->name ?? '-') }}'
                                                        })"
                                                        class="p-1.5 rounded-lg border border-amber-200 bg-amber-50 text-amber-800 hover:bg-amber-100 transition-colors shadow-2xs inline-flex items-center justify-center font-medium text-xs gap-1 cursor-pointer"
                                                        title="Atur Tutor Pengganti">
                                                    <x-cressco.icon-helper name="user-check" class="w-4 h-4" />
                                                    <span class="hidden sm:inline">Ganti Tutor</span>
                                                </button>

                                                <a href="{{ route('admin.attendances.sessions.show', $session) }}"
                                                   class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center font-medium text-xs gap-1"
                                                   title="Detail Sesi / Presensi">
                                                    <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                                    <span class="hidden sm:inline">Detail</span>
                                                </a>
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

        <!-- 6. BATCH ENROLL STUDENTS MODAL -->
        <div x-show="batchEnrollModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="batchEnrollModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary-50 text-primary-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="users" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Tambah Siswa Terdaftar ke Kelas</h3>
                                <p class="text-xs text-gray-500">Pilih satu atau beberapa siswa di cabang {{ $class->branch?->name }} untuk dimasukkan ke kelas {{ $class->name }}.</p>
                            </div>
                        </div>
                        <button type="button" @click="batchEnrollModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.classes.enroll-students', $class) }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <!-- Search Box & Select All -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider">Cari & Pilih Siswa</label>
                                <span class="text-xs font-bold text-primary-600" x-text="selectedStudentIds.length + ' siswa dipilih'"></span>
                            </div>

                            <div class="relative">
                                <x-cressco.icon-helper name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                                <input type="text"
                                       x-model="studentSearchQuery"
                                       placeholder="Ketik nama atau nomor HP siswa..."
                                       class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-primary-500">
                            </div>

                            <div class="flex items-center justify-between pt-1">
                                <label class="inline-flex items-center gap-2 text-xs text-gray-600 cursor-pointer">
                                    <input type="checkbox"
                                           @change="toggleSelectAll($event.target.checked)"
                                           class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                    <span>Pilih Semua yang Ditampilkan</span>
                                </label>
                                <button type="button" @click="selectedStudentIds = []" class="text-[11px] text-gray-400 hover:text-gray-600">
                                    Reset Pilihan
                                </button>
                            </div>
                        </div>

                        <!-- Available Students List -->
                        <div class="border border-gray-200 rounded-xl overflow-hidden max-h-60 overflow-y-auto divide-y divide-gray-100 bg-gray-50/50">
                            <template x-for="stu in filteredAvailableStudents" :key="stu.id">
                                <label class="flex items-center justify-between p-3 hover:bg-white cursor-pointer transition-colors">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox"
                                               name="student_ids[]"
                                               :value="stu.id"
                                               x-model="selectedStudentIds"
                                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        <div>
                                            <div class="font-bold text-xs text-gray-900" x-text="stu.name"></div>
                                            <div class="text-[11px] text-gray-500" x-text="stu.phone || 'Tanpa no. HP'"></div>
                                        </div>
                                    </div>
                                    <span class="text-[11px] text-gray-400" x-text="stu.parent_name ? 'Ortu: ' + stu.parent_name : ''"></span>
                                </label>
                            </template>

                            <div x-show="filteredAvailableStudents.length === 0" class="p-6 text-center text-xs text-gray-400">
                                <span x-show="availableStudents.length === 0">Semua siswa di cabang ini sudah terdaftar pada kelas ini.</span>
                                <span x-show="availableStudents.length > 0">Tidak ada siswa yang sesuai dengan pencarian Anda.</span>
                            </div>
                        </div>

                        <!-- Start Date -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1">Tanggal Mulai Efektif</label>
                            <input type="date" name="started_at" value="{{ date('Y-m-d') }}"
                                   class="w-full text-xs px-3 py-2 rounded-xl border border-gray-200 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="batchEnrollModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit" ::disabled="selectedStudentIds.length === 0">
                                Tambahkan ke Kelas
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 7. QUICK REGISTER NEW STUDENT MODAL -->
        <div x-show="quickAddStudentModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="quickAddStudentModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-xl rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="user-plus" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Daftarkan Siswa Baru ke Kelas Ini</h3>
                                <p class="text-xs text-gray-500">Siswa baru akan otomatis ditempatkan di cabang {{ $class->branch?->name }} dan di-enroll ke kelas {{ $class->name }}.</p>
                            </div>
                        </div>
                        <button type="button" @click="quickAddStudentModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.classes.store-student', $class) }}" method="POST" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="branch_id" value="{{ $class->branch_id }}">
                        <input type="hidden" name="status" value="active">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Anisa Rahma"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="date_of_birth"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WA Siswa</label>
                                <input type="text" name="phone" placeholder="081234567890"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua / Wali</label>
                                <input type="text" name="parent_name" placeholder="Nama ayah/ibu"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">No HP Orang Tua</label>
                                <input type="text" name="parent_phone" placeholder="081234567890"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                                <input type="date" name="joined_at" value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Khusus (Opsional)</label>
                                <textarea name="notes" rows="2" placeholder="Catatan minat bakat, target belajar, dll..."
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="quickAddStudentModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan & Daftarkan ke Kelas
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 8. ATUR TUTOR PENGGANTI SESI MODAL -->
        <div x-show="replaceSessionModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="replaceSessionModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="user-check" class="w-4 h-4" />
                            </div>
                            <h3 class="text-base font-bold text-gray-900">Atur Tutor Pengganti untuk Sesi</h3>
                        </div>
                        <button type="button" @click="replaceSessionModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'{{ url('admin/attendances/sessions') }}/' + activeSessionForReplacement.id + '/replace-tutor'" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div class="p-3 bg-gray-50 rounded-xl border border-gray-200 text-xs space-y-1.5">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Kelas:</span>
                                <span class="font-bold text-gray-900">{{ $class->name }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tanggal & Waktu:</span>
                                <span class="font-semibold text-gray-900" x-text="activeSessionForReplacement.date + ' (' + activeSessionForReplacement.time + ')'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tutor Terjadwal:</span>
                                <span class="font-semibold text-gray-900" x-text="activeSessionForReplacement.scheduled_tutor"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tutor Aktual Saat Ini:</span>
                                <span class="font-bold text-amber-700" x-text="activeSessionForReplacement.actual_tutor"></span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                                Pilih Tutor Pengganti *
                            </label>
                            <select name="replacement_tutor_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="">-- Pilih Tutor Pengganti --</option>
                                @foreach($tutors as $tut)
                                    <option value="{{ $tut->id }}">
                                        {{ $tut->name }} ({{ ucfirst($tut->role) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">
                                Alasan Penggantian *
                            </label>
                            <textarea name="reason" rows="3" placeholder="Contoh: Tutor utama sedang sakit demam / berhalangan hadir..."
                                      class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required></textarea>
                        </div>

                        <div class="p-3 bg-blue-50/70 rounded-xl border border-blue-200 text-[11px] text-blue-900 flex items-start gap-2">
                            <x-cressco.icon-helper name="info" class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                            <span>
                                <strong>Catatan:</strong> Pergantian tutor hanya berlaku untuk sesi tanggal ini saja. Tutor utama pada jadwal kelas tidak akan berubah, dan honor sesi ini akan dihitung untuk tutor pengganti yang mengajar.
                            </span>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="replaceSessionModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Tutor Pengganti
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
