<x-owner-layout :tenant="$tenant" :title="$class->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('owner.classes.index') }}" class="hover:text-terracotta-600 transition">Management Kelas & Jadwal</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">{{ $class->name }}</span>
    </x-slot:breadcrumbSub>

    @php
        $activeEnrollments = $class->enrollments->where('status', 'active')->count();
        $capacityRatio = $class->capacity ? min(100, round(($activeEnrollments / $class->capacity) * 100)) : 0;
        $activeTutors = $class->tutorAssignments->where('status', 'active');
        $activeSchedules = $class->schedules->where('status', 'active');
        $daysMap = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
    @endphp

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             editClassModalOpen: false,
             toggleClassModalOpen: false,
             addScheduleModalOpen: false,
             editScheduleModalOpen: false,
             assignTutorModalOpen: false,
             editSchedule: { id: '', scheduled_tutor_id: '', day_of_week: 1, start_time: '', end_time: '', room: '', starts_on: '', ends_on: '', status: 'active' },
             openEditSchedule(sch) {
                 this.editSchedule = {
                     id: sch.id,
                     scheduled_tutor_id: sch.scheduled_tutor_id,
                     day_of_week: sch.day_of_week,
                     start_time: sch.start_time ? sch.start_time.substring(0, 5) : '',
                     end_time: sch.end_time ? sch.end_time.substring(0, 5) : '',
                     room: sch.room || '',
                     starts_on: sch.starts_on ? sch.starts_on.substring(0, 10) : '',
                     ends_on: sch.ends_on ? sch.ends_on.substring(0, 10) : '',
                     status: sch.status || 'active'
                 };
                 this.editScheduleModalOpen = true;
             },
             closeAll() {
                 this.editClassModalOpen = false;
                 this.toggleClassModalOpen = false;
                 this.addScheduleModalOpen = false;
                 this.editScheduleModalOpen = false;
                 this.assignTutorModalOpen = false;
             }
         }">
        
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-base shadow-2xs border border-terracotta-200/60">
                    {{ strtoupper(substr($class->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $class->name }}</h1>
                        <x-cressco.badge :variant="$class->status === 'active' ? 'success' : 'gray'" dot>
                            {{ $class->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-cressco.badge>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Cabang: <span class="font-semibold text-gray-800">{{ $class->branch?->name ?? 'Pusat' }}</span>
                        @if ($class->subject)
                            • Mapel: <span class="font-semibold text-gray-800">{{ $class->subject }}</span>
                        @endif
                        @if ($class->level)
                            • Tingkat: <span class="font-semibold text-gray-800">{{ $class->level }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('owner.classes.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                    ← Kembali
                </a>

                <x-cressco.button variant="secondary" size="md" leadingIcon="edit" @click="editClassModalOpen = true">
                    Edit Kelas
                </x-cressco.button>

                <x-cressco.button variant="{{ $class->status === 'active' ? 'secondary' : 'primary' }}" size="md" @click="toggleClassModalOpen = true">
                    @if ($class->status === 'active')
                        <span class="text-amber-700">Nonaktifkan Kelas</span>
                    @else
                        <span>Aktifkan Kelas</span>
                    @endif
                </x-cressco.button>
            </div>
        </div>

        <!-- 2 Columns: Information & Operational Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Column 1: Class Information & Summary Stats -->
            <div class="space-y-6">
                
                <!-- Information Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Informasi Kelas</h2>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nama Kelas</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $class->name }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Cabang Lokasi</span>
                            <p class="font-semibold text-gray-800 mt-0.5">{{ $class->branch?->name ?? '-' }} ({{ $class->branch?->code ?? '' }})</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Mata Pelajaran</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $class->subject ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Tingkat / Jenjang</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $class->level ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Kapasitas Kursi</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $class->capacity ? $class->capacity . ' Siswa' : 'Tidak Terbatas' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Status Operasional</span>
                            <div class="mt-1">
                                <x-cressco.badge :variant="$class->status === 'active' ? 'success' : 'gray'" dot>
                                    {{ $class->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-cressco.badge>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Okupansi & Kapasitas Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Okupansi Kelas</h3>
                    
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-500 font-medium">Siswa Terdaftar</span>
                        <span class="font-bold text-gray-900">{{ $activeEnrollments }} / {{ $class->capacity ?: '∞' }} Siswa</span>
                    </div>

                    @if ($class->capacity)
                        <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                            <div class="h-full {{ $capacityRatio >= 90 ? 'bg-amber-500' : 'bg-terracotta-500' }} transition-all duration-300"
                                 style="width: {{ $capacityRatio }}%"></div>
                        </div>
                        <p class="text-[11px] text-gray-400 text-right">{{ $capacityRatio }}% Terisi</p>
                    @endif
                </div>

            </div>

            <!-- Column 2 & 3: Recurring Schedules, Tutors & Enrolled Students -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- 1. JADWAL RUTIN (RECURRING SCHEDULES) -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Jadwal Rutin Belajar</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Aturan jadwal mingguan berulang untuk kelas ini.</p>
                        </div>
                        <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="addScheduleModalOpen = true">
                            Tambah Jadwal Rutin
                        </x-cressco.button>
                    </div>

                    <div class="space-y-3">
                        @forelse ($class->schedules as $schedule)
                            <div class="p-4 rounded-xl border border-gray-200/80 bg-gray-50/50 hover:bg-gray-50 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-terracotta-50 text-terracotta-700 flex flex-col items-center justify-center font-bold shrink-0 border border-terracotta-200/60">
                                        <span class="text-[10px] uppercase text-terracotta-500">{{ substr($daysMap[$schedule->day_of_week] ?? 'Hari', 0, 3) }}</span>
                                        <span class="text-xs leading-none mt-0.5">{{ substr($schedule->start_time, 0, 2) }}</span>
                                    </div>

                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900 text-sm">
                                                Setiap {{ $daysMap[$schedule->day_of_week] ?? 'Hari' }}, {{ substr($schedule->start_time, 0, 5) }} - {{ substr($schedule->end_time, 0, 5) }} WIB
                                            </span>
                                            <x-cressco.badge :variant="$schedule->status === 'active' ? 'success' : 'gray'" size="xs" dot>
                                                {{ $schedule->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                            </x-cressco.badge>
                                        </div>

                                        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-gray-500 mt-1 text-[11px]">
                                            @if ($schedule->room)
                                                <span>Ruangan: <strong class="text-gray-700">{{ $schedule->room }}</strong></span>
                                                <span>•</span>
                                            @endif
                                            <span>Tutor: <strong class="text-gray-700">{{ $schedule->scheduledTutor?->name ?? 'Belum ditentukan' }}</strong></span>
                                            <span>•</span>
                                            <span>Berlaku: {{ $schedule->starts_on ? $schedule->starts_on->translatedFormat('d M Y') : '-' }} {{ $schedule->ends_on ? 's/d ' . $schedule->ends_on->translatedFormat('d M Y') : '(Seterusnya)' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 self-end sm:self-center">
                                    <button type="button"
                                            @click="openEditSchedule({{ $schedule->toJson() }})"
                                            class="p-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-100 text-gray-600 transition"
                                            title="Edit Jadwal">
                                        <x-cressco.icon-helper name="edit" class="w-3.5 h-3.5" />
                                    </button>

                                    <form method="POST" action="{{ route('owner.classes.schedules.toggle-status', [$class, $schedule]) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg border border-gray-200 bg-white hover:bg-gray-100 {{ $schedule->status === 'active' ? 'text-amber-600' : 'text-emerald-600' }} transition"
                                                title="{{ $schedule->status === 'active' ? 'Nonaktifkan Jadwal' : 'Aktifkan Jadwal' }}">
                                            <x-cressco.icon-helper name="{{ $schedule->status === 'active' ? 'close' : 'check' }}" class="w-3.5 h-3.5" />
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('owner.classes.schedules.destroy', [$class, $schedule]) }}" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal rutin ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg border border-gray-200 bg-white hover:bg-red-50 text-red-500 transition"
                                                title="Hapus Jadwal">
                                            <x-cressco.icon-helper name="trash" class="w-3.5 h-3.5" />
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-8 text-center text-gray-400 border border-dashed border-gray-200 rounded-xl">
                                <x-cressco.icon-helper name="calendar" class="w-6 h-6 mx-auto mb-2 text-gray-300" />
                                <p class="text-xs font-semibold text-gray-600">Belum ada jadwal rutin</p>
                                <p class="text-[11px] text-gray-400 mt-0.5">Klik tombol "+ Tambah Jadwal Rutin" untuk membuat aturan jadwal mingguan.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 2. TUTOR PENGAJAR (TUTOR ASSIGNMENTS) -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Tutor Pengajar</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar tutor yang ditugaskan untuk mengajar pada kelas ini.</p>
                        </div>
                        <x-cressco.button variant="secondary" size="sm" leadingIcon="plus" @click="assignTutorModalOpen = true">
                            Tugaskan Tutor
                        </x-cressco.button>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse ($class->tutorAssignments as $assignment)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($assignment->tutor?->name ?? 'T', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-gray-900">{{ $assignment->tutor?->name ?? 'Tutor' }}</span>
                                            <x-cressco.badge :variant="$assignment->status === 'active' ? 'success' : 'gray'" size="xs" dot>
                                                {{ $assignment->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                            </x-cressco.badge>
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            {{ $assignment->tutor?->email ?? '' }} • Mulai: {{ $assignment->started_at ? $assignment->started_at->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <form method="POST" action="{{ route('owner.classes.tutors.toggle-status', [$class, $assignment]) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2.5 py-1 rounded-lg border border-gray-200 bg-white hover:bg-gray-50 text-[11px] font-semibold text-gray-600 transition">
                                            {{ $assignment->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-400">
                                <p class="text-xs">Belum ada tutor yang ditugaskan pada kelas ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. SISWA TERDAFTAR (ENROLLMENTS) -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Siswa Terdaftar ({{ $class->enrollments->count() }})</h2>
                            <p class="text-xs text-gray-500 mt-0.5">Daftar siswa aktif dan riwayat enrollment pada kelas ini.</p>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse ($class->enrollments as $enrollment)
                            <div class="py-3 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gray-100 text-gray-700 font-bold text-xs flex items-center justify-center shrink-0">
                                        {{ strtoupper(substr($enrollment->student?->name ?? 'S', 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            @if ($enrollment->student)
                                                <a href="{{ route('owner.students.show', $enrollment->student) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition">
                                                    {{ $enrollment->student->name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-gray-900">Siswa</span>
                                            @endif
                                            <x-cressco.badge :variant="$enrollment->status === 'active' ? 'success' : 'gray'" size="xs" dot>
                                                {{ ucfirst($enrollment->status) }}
                                            </x-cressco.badge>
                                        </div>
                                        <div class="text-[11px] text-gray-400 mt-0.5">
                                            Kontak: {{ $enrollment->student?->phone ?: '-' }} • Wali: {{ $enrollment->student?->parent_name ?: '-' }} • Terdaftar: {{ $enrollment->started_at ? $enrollment->started_at->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </div>
                                </div>

                                @if ($enrollment->student)
                                    <div>
                                        <a href="{{ route('owner.students.show', $enrollment->student) }}" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition inline-block">
                                            <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-400">
                                <p class="text-xs">Belum ada siswa terdaftar pada kelas ini.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT KELAS                          -->
        <!-- ========================================== -->
        <div x-show="editClassModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="editClassModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="editClassModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Informasi Kelas</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $class->name }}</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.classes.update', $class) }}" class="space-y-4 mt-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               value="{{ $class->name }}"
                               required
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Cabang Penempatan <span class="text-red-500">*</span></label>
                            <select name="branch_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}" {{ $class->branch_id === $b->id ? 'selected' : '' }}>
                                        {{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Kapasitas Maksimal Siswa</label>
                            <input type="number"
                                   name="capacity"
                                   value="{{ $class->capacity }}"
                                   min="1"
                                   max="200"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Mata Pelajaran</label>
                            <input type="text"
                                   name="subject"
                                   value="{{ $class->subject }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tingkat / Jenjang</label>
                            <input type="text"
                                   name="level"
                                   value="{{ $class->level }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Kelas</label>
                        <select name="status" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="active" {{ $class->status === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $class->status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Simpan Perubahan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TAMBAH JADWAL RUTIN                 -->
        <!-- ========================================== -->
        <div x-show="addScheduleModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="addScheduleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="addScheduleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tambah Jadwal Rutin Baru</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Jadwal berulang mingguan untuk {{ $class->name }}.</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.classes.schedules.store', $class) }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tutor Pengajar <span class="text-red-500">*</span></label>
                        <select name="scheduled_tutor_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Pilih Tutor...</option>
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }} ({{ ucfirst($tutor->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Hari <span class="text-red-500">*</span></label>
                            <select name="day_of_week" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="0">Minggu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="time"
                                   name="start_time"
                                   required
                                   value="16:00"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="time"
                                   name="end_time"
                                   required
                                   value="17:30"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ruangan / Lab (Opsional)</label>
                        <input type="text"
                               name="room"
                               placeholder="Contoh: Ruang M-1, Lab Komputer, dll."
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date"
                                   name="starts_on"
                                   required
                                   value="{{ now()->toDateString() }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                            <input type="date"
                                   name="ends_on"
                                   placeholder="Kosongkan jika aktif seterusnya"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Jadwal</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="active" selected>Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Simpan Jadwal Rutin
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT JADWAL RUTIN                   -->
        <!-- ========================================== -->
        <div x-show="editScheduleModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="editScheduleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="editScheduleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Jadwal Rutin</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $class->name }}</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/owner/classes/' . $class->id . '/schedules') }}/' + editSchedule.id" class="space-y-4 mt-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Tutor Pengajar <span class="text-red-500">*</span></label>
                        <select name="scheduled_tutor_id" x-model="editSchedule.scheduled_tutor_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }} ({{ ucfirst($tutor->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Hari <span class="text-red-500">*</span></label>
                            <select name="day_of_week" x-model="editSchedule.day_of_week" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="0">Minggu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                            <input type="time"
                                   name="start_time"
                                   x-model="editSchedule.start_time"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                            <input type="time"
                                   name="end_time"
                                   x-model="editSchedule.end_time"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Ruangan / Lab (Opsional)</label>
                        <input type="text"
                               name="room"
                               x-model="editSchedule.room"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-red-500">*</span></label>
                            <input type="date"
                                   name="starts_on"
                                   x-model="editSchedule.starts_on"
                                   required
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Berakhir (Opsional)</label>
                            <input type="date"
                                   name="ends_on"
                                   x-model="editSchedule.ends_on"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Jadwal</label>
                        <select name="status" x-model="editSchedule.status" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Perbarui Jadwal
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TUGASKAN TUTOR                      -->
        <!-- ========================================== -->
        <div x-show="assignTutorModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="assignTutorModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="assignTutorModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-100 z-10">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Tugaskan Tutor ke Kelas</h3>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $class->name }}</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.classes.assign-tutor', $class) }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Pilih Tutor Pengajar <span class="text-red-500">*</span></label>
                        <select name="tutor_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Pilih Tutor...</option>
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }} ({{ ucfirst($tutor->role) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Mulai</label>
                            <input type="date"
                                   name="started_at"
                                   value="{{ now()->toDateString() }}"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tanggal Selesai (Opsional)</label>
                            <input type="date"
                                   name="ended_at"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Penugasan</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="active" selected>Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Tugaskan Tutor
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TOGGLE STATUS KELAS                 -->
        <!-- ========================================== -->
        <div x-show="toggleClassModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="toggleClassModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="toggleClassModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-gray-100 z-10 text-center">
                
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center mx-auto mb-4 border border-amber-100">
                    <x-cressco.icon-helper name="help" class="w-6 h-6" />
                </div>

                <h3 class="text-base font-bold text-gray-900">
                    {{ $class->status === 'active' ? 'Nonaktifkan Kelas?' : 'Aktifkan Kelas?' }}
                </h3>

                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin mengubah status kelas <strong class="text-gray-800">{{ $class->name }}</strong> menjadi <span class="font-bold">{{ $class->status === 'active' ? 'Nonaktif' : 'Aktif' }}</span>?
                </p>

                <form method="POST" action="{{ route('owner.classes.toggle-status', $class) }}" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('PATCH')

                    <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>

                    <button type="submit"
                            class="px-4 py-2 rounded-xl text-white text-xs font-bold transition shadow-xs {{ $class->status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $class->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
