<x-owner-layout :tenant="$tenant" title="Management Kelas & Jadwal">
    <x-slot:breadcrumbSub>Management Kelas & Jadwal</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             toggleModalOpen: false,
             editClass: { id: '', branch_id: '', name: '', subject: '', level: '', capacity: '', status: 'active' },
             toggleClass: { id: '', name: '', status: '' },
             openCreate() {
                 this.toggleModalOpen = false;
                 this.editModalOpen = false;
                 this.createModalOpen = true;
             },
             openEdit(cls) {
                 this.toggleModalOpen = false;
                 this.createModalOpen = false;
                 this.editClass = {
                     id: cls.id,
                     branch_id: cls.branch_id,
                     name: cls.name,
                     subject: cls.subject || '',
                     level: cls.level || '',
                     capacity: cls.capacity || '',
                     status: cls.status || 'active'
                 };
                 this.editModalOpen = true;
             },
             openToggle(cls) {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleClass = {
                     id: cls.id,
                     name: cls.name,
                     status: cls.status
                 };
                 this.toggleModalOpen = true;
             },
             closeAll() {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleModalOpen = false;
             }
         }">

        <!-- Page Header & Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Management Kelas & Jadwal</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola data kelas belajar, penugasan tutor, kapasitas siswa, dan jadwal rutin di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button variant="primary" size="md" leadingIcon="plus" @click="openCreate()">
                    Buat Kelas Baru
                </x-cressco.button>
            </div>
        </div>

        <!-- Summary Metric Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Kelas"
                icon="book-open"
                iconColor="text-gray-500"
                value="{{ $totalClassesCount }}"
                trend="Rombel"
                trendType="neutral"
                subtitle="Semua rombel kelas terdaftar"
            />

            <x-cressco.project-card
                title="Kelas Aktif"
                icon="check"
                iconColor="text-emerald-500"
                value="{{ $activeClassesCount }}"
                trend="Berjalan"
                trendType="positive"
                subtitle="Kelas dengan jadwal aktif berjalan"
            />

            <x-cressco.project-card
                title="Kapasitas Kursi"
                icon="folder"
                iconColor="text-terracotta-500"
                value="{{ $totalCapacityCount }}"
                trend="Kursi"
                trendType="terracotta"
                subtitle="Daya tampung maksimal kelas"
            />

            <x-cressco.project-card
                title="Enrollment Siswa"
                icon="users"
                iconColor="text-blue-500"
                value="{{ $totalEnrollmentsCount }}"
                trend="Terisi"
                trendType="neutral"
                subtitle="Siswa terdaftar dalam kelas"
            />
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.classes.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                
                <!-- Search Input -->
                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nama kelas, mata pelajaran, tingkat..."
                           class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-gray-200 bg-white placeholder-gray-400 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                </div>

                <!-- Branch Filter -->
                <div class="sm:col-span-3">
                    <select name="branch_id"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                        <option value="all" {{ ($branchId ?? 'all') === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ ($branchId ?? '') === $b->id ? 'selected' : '' }}>
                                Cabang {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-2">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <!-- Actions / Reset -->
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 px-3 py-2 rounded-xl bg-gray-900 hover:bg-gray-800 text-white font-medium text-xs transition">
                        Cari
                    </button>
                    @if ($search || ($branchId && $branchId !== 'all') || ($status && $status !== 'all'))
                        <a href="{{ route('owner.classes.index') }}" class="px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-600 font-medium text-xs transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Classes Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200/70 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4">Nama Kelas & Jenjang</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4">Tutor Pengajar</th>
                            <th class="py-3 px-4">Jadwal Rutin</th>
                            <th class="py-3 px-4">Kapasitas / Siswa</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 font-medium text-gray-700">
                        @forelse ($classes as $cls)
                            @php
                                $activeEnrollments = $cls->enrollments->where('status', 'active')->count();
                                $capacityRatio = $cls->capacity ? min(100, round(($activeEnrollments / $cls->capacity) * 100)) : 0;
                                $activeTutors = $cls->tutorAssignments->where('status', 'active');
                                $activeSchedules = $cls->schedules->where('status', 'active');
                                $daysMap = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                            @endphp
                            <tr class="hover:bg-gray-50/70 transition">
                                
                                <!-- Nama Kelas & Jenjang -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-terracotta-50 text-terracotta-600 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-100">
                                            {{ strtoupper(substr($cls->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('owner.classes.show', $cls) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block">
                                                {{ $cls->name }}
                                            </a>
                                            <div class="flex items-center gap-1.5 mt-0.5 text-[11px] text-gray-500">
                                                @if ($cls->subject)
                                                    <span class="font-semibold text-gray-700">{{ $cls->subject }}</span>
                                                @endif
                                                @if ($cls->subject && $cls->level)
                                                    <span>•</span>
                                                @endif
                                                @if ($cls->level)
                                                    <span class="text-gray-500">{{ $cls->level }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Cabang -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <x-cressco.icon-helper name="building" class="w-3.5 h-3.5 text-gray-400" />
                                        <span class="font-semibold text-gray-800">{{ $cls->branch?->name ?? '-' }}</span>
                                    </div>
                                </td>

                                <!-- Tutor Pengajar -->
                                <td class="py-3.5 px-4">
                                    @if ($activeTutors->count() > 0)
                                        <div class="space-y-1">
                                            @foreach ($activeTutors->take(2) as $assignment)
                                                <div class="flex items-center gap-1.5">
                                                    <div class="w-4 h-4 rounded-full bg-terracotta-100 text-terracotta-700 font-bold text-[9px] flex items-center justify-center shrink-0">
                                                        {{ strtoupper(substr($assignment->tutor?->name ?? 'T', 0, 1)) }}
                                                    </div>
                                                    <span class="text-xs text-gray-800 font-medium truncate max-w-[150px]">{{ $assignment->tutor?->name ?? 'Tutor' }}</span>
                                                </div>
                                            @endforeach
                                            @if ($activeTutors->count() > 2)
                                                <span class="text-[10px] text-gray-400 font-semibold">+{{ $activeTutors->count() - 2 }} tutor lainnya</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Belum ada tutor</span>
                                    @endif
                                </td>

                                <!-- Jadwal Rutin -->
                                <td class="py-3.5 px-4">
                                    @if ($activeSchedules->count() > 0)
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @foreach ($activeSchedules->take(2) as $sch)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-100 text-gray-700 text-[10px] font-medium border border-gray-200">
                                                    {{ $daysMap[$sch->day_of_week] ?? 'Hari' }} {{ substr($sch->start_time, 0, 5) }}-{{ substr($sch->end_time, 0, 5) }}
                                                </span>
                                            @endforeach
                                            @if ($activeSchedules->count() > 2)
                                                <span class="text-[10px] text-gray-400 font-semibold self-center">+{{ $activeSchedules->count() - 2 }} jadwal</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-[11px] text-gray-400 italic">Belum diatur</span>
                                    @endif
                                </td>

                                <!-- Kapasitas & Siswa -->
                                <td class="py-3.5 px-4">
                                    <div>
                                        <div class="flex items-center justify-between text-[11px] mb-1">
                                            <span class="font-bold text-gray-900">{{ $activeEnrollments }} Siswa</span>
                                            <span class="text-gray-400">/ {{ $cls->capacity ?: '∞' }} Kapasitas</span>
                                        </div>
                                        @if ($cls->capacity)
                                            <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div class="h-full {{ $capacityRatio >= 90 ? 'bg-amber-500' : 'bg-terracotta-500' }}" style="width: {{ $capacityRatio }}%"></div>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <!-- Status -->
                                <td class="py-3.5 px-4">
                                    <x-cressco.badge :variant="$cls->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $cls->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Aksi -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('owner.classes.show', $cls) }}"
                                           class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                                           title="Lihat Detail Kelas">
                                            <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                        </a>

                                        <button type="button"
                                                @click="openEdit({{ $cls->toJson() }})"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition"
                                                title="Edit Kelas">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <button type="button"
                                                @click="openToggle({{ $cls->toJson() }})"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-amber-600 hover:bg-amber-50 transition"
                                                title="{{ $cls->status === 'active' ? 'Nonaktifkan Kelas' : 'Aktifkan Kelas' }}">
                                            <x-cressco.icon-helper name="{{ $cls->status === 'active' ? 'close' : 'check' }}" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="w-12 h-12 rounded-2xl bg-gray-50 border border-gray-100 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                        <x-cressco.icon-helper name="book-open" class="w-6 h-6" />
                                    </div>
                                    <p class="font-medium text-gray-600 text-sm">Belum ada kelas yang ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-1">Silakan sesuaikan filter pencarian atau buat kelas baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: CREATE KELAS BARU                   -->
        <!-- ========================================== -->
        <div x-show="createModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="createModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="createModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Buat Kelas Baru</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Lengkapi informasi kelas bimbel di {{ $tenant->name ?? 'Prime Academy' }}.</p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.classes.store') }}" class="space-y-4 mt-4 text-xs">
                    @csrf

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Contoh: 12 IPA - Matematika Intensif"
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Cabang Penempatan <span class="text-red-500">*</span></label>
                            <select name="branch_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                <option value="">Pilih Cabang...</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Kapasitas Maksimal Siswa</label>
                            <input type="number"
                                   name="capacity"
                                   min="1"
                                   max="200"
                                   placeholder="Contoh: 15"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Mata Pelajaran</label>
                            <input type="text"
                                   name="subject"
                                   placeholder="Contoh: Matematika, Fisika, dll."
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tingkat / Jenjang</label>
                            <input type="text"
                                   name="level"
                                   placeholder="Contoh: 12 SMA, 9 SMP, dll."
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Pilih Tutor Pengajar (Opsional)</label>
                        <select name="tutor_id" class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="">Belum Ditugaskan (Bisa diatur nanti)</option>
                            @foreach ($tutors as $tutor)
                                <option value="{{ $tutor->id }}">{{ $tutor->name }} ({{ ucfirst($tutor->role) }})</option>
                            @endforeach
                        </select>
                        <p class="text-[11px] text-gray-400 mt-1">Anda juga dapat menambahkan lebih dari satu tutor melalui halaman detail kelas.</p>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Kelas</label>
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
                            Simpan Kelas
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: EDIT KELAS                          -->
        <!-- ========================================== -->
        <div x-show="editModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="editModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="relative bg-white rounded-2xl shadow-xl max-w-xl w-full p-6 border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Edit Kelas</h3>
                        <p class="text-xs text-gray-500 mt-0.5" x-text="editClass.name"></p>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600 transition">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'{{ url('/owner/classes') }}/' + editClass.id" class="space-y-4 mt-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               x-model="editClass.name"
                               required
                               class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Cabang Penempatan <span class="text-red-500">*</span></label>
                            <select name="branch_id" x-model="editClass.branch_id" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Kapasitas Maksimal Siswa</label>
                            <input type="number"
                                   name="capacity"
                                   x-model="editClass.capacity"
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
                                   x-model="editClass.subject"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>

                        <div>
                            <label class="block font-bold text-gray-700 mb-1">Tingkat / Jenjang</label>
                            <input type="text"
                                   name="level"
                                   x-model="editClass.level"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-gray-700 mb-1">Status Kelas</label>
                        <select name="status" x-model="editClass.status" required class="w-full px-3 py-2 rounded-xl border border-gray-200 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-gray-700 font-bold transition">
                            Batal
                        </button>
                        <x-cressco.button type="submit" variant="primary" size="md">
                            Perbarui Kelas
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- MODAL: TOGGLE STATUS KELAS                 -->
        <!-- ========================================== -->
        <div x-show="toggleModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 overflow-y-auto">
            
            <div x-show="toggleModalOpen"
                 x-transition:enter="ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs"
                 @click="closeAll()"></div>

            <div x-show="toggleModalOpen"
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
                    <span x-text="toggleClass.status === 'active' ? 'Nonaktifkan Kelas?' : 'Aktifkan Kelas?'"></span>
                </h3>

                <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                    Apakah Anda yakin ingin mengubah status operasional kelas <strong class="text-gray-800" x-text="toggleClass.name"></strong>?
                </p>

                <form method="POST" :action="'{{ url('/owner/classes') }}/' + toggleClass.id + '/toggle-status'" class="mt-6 flex items-center justify-center gap-3">
                    @csrf
                    @method('PATCH')

                    <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>

                    <button type="submit"
                            :class="toggleClass.status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="px-4 py-2 rounded-xl text-white text-xs font-bold transition shadow-xs">
                        <span x-text="toggleClass.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
