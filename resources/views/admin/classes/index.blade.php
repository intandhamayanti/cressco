<x-admin-layout :tenant="$tenant" title="Management Kelas & Jadwal">
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
                icon="folder"
                iconColor="text-gray-500"
                value="{{ $totalClassesCount }}"
                trend="Rombel"
                trendType="neutral"
                subtitle="Semua rombel kelas di cabang akses Anda"
            />

            <x-cressco.project-card
                title="Kelas Aktif"
                icon="check-circle"
                iconColor="text-emerald-500"
                value="{{ $activeClassesCount }}"
                trend="Berjalan"
                trendType="positive"
                subtitle="Kelas dengan jadwal aktif berjalan"
            />

            <x-cressco.project-card
                title="Kapasitas Kursi"
                icon="building"
                iconColor="text-terracotta-500"
                value="{{ $totalCapacityCount }}"
                trend="Kursi"
                trendType="neutral"
                subtitle="Daya tampung maksimal kelas"
            />

            <x-cressco.project-card
                title="Enrollment Siswa"
                icon="users"
                iconColor="text-blue-500"
                value="{{ $totalEnrollmentsCount }}"
                trend="Terisi"
                trendType="neutral"
                subtitle="Total siswa aktif terdaftar di kelas"
            />
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.classes.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nama kelas, mata pelajaran, atau tingkat level..."
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition placeholder-gray-400">
                </div>

                <!-- Branch Filter -->
                @if ($accessibleBranches->count() > 1)
                    <div class="w-full sm:w-56">
                        <select name="branch_id"
                                class="w-full py-2 px-3 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition text-gray-700">
                            <option value="all" {{ ($branchId ?? 'all') === 'all' ? 'selected' : '' }}>Semua Cabang Saya</option>
                            @foreach ($accessibleBranches as $branch)
                                <option value="{{ $branch->id }}" {{ ($branchId ?? '') === $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <!-- Status Filter -->
                <div class="w-full sm:w-44">
                    <select name="status"
                            class="w-full py-2 px-3 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition text-gray-700">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <!-- Submit & Reset Buttons -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <x-cressco.button type="submit" variant="secondary" size="md">
                        Filter
                    </x-cressco.button>

                    @if ($search || ($branchId && $branchId !== 'all') || ($status && $status !== 'all'))
                        <a href="{{ route('admin.classes.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-gray-500 hover:text-gray-900 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Class Data Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/75 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 sm:px-6">Kelas & Pelajaran</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4">Kapasitas & Siswa</th>
                            <th class="py-3 px-4">Tutor Ditugaskan</th>
                            <th class="py-3 px-4">Jadwal Mingguan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse ($classes as $cls)
                            @php
                                $enrolledCount = $cls->enrollments->where('status', 'active')->count();
                                $capacity = $cls->capacity ?: 20;
                                $isFull = $enrolledCount >= $capacity;
                            @endphp
                            <tr class="hover:bg-gray-50/60 transition group">
                                
                                <!-- Class Info -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0 border border-blue-200/60">
                                            {{ strtoupper(substr($cls->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.classes.show', $cls) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block leading-tight">
                                                {{ $cls->name }}
                                            </a>
                                            <div class="flex items-center gap-2 text-[11px] text-gray-400 mt-0.5">
                                                <span>{{ $cls->subject ?: 'Umum' }}</span>
                                                @if ($cls->level)
                                                    <span>•</span>
                                                    <span class="font-medium text-gray-600">{{ $cls->level }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Branch -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200/60">
                                        {{ $cls->branch?->name ?? 'Cabang' }}
                                    </span>
                                </td>

                                <!-- Capacity & Enrollment -->
                                <td class="py-3.5 px-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center gap-1.5 font-bold {{ $isFull ? 'text-rose-600' : 'text-gray-800' }}">
                                            <span>{{ $enrolledCount }}</span>
                                            <span class="text-gray-400 font-normal">/ {{ $capacity }} Siswa</span>
                                        </div>
                                        <div class="w-24 bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                            <div class="h-1.5 rounded-full {{ $isFull ? 'bg-rose-500' : 'bg-blue-600' }}" style="width: {{ min(100, round(($enrolledCount / $capacity) * 100)) }}%"></div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Tutors Assigned -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $activeTutors = $cls->tutorAssignments->where('status', 'active');
                                    @endphp
                                    @if ($activeTutors->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($activeTutors->take(2) as $assign)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-teal-50 text-teal-800 border border-teal-200/60">
                                                    {{ $assign->tutor?->name ?? 'Tutor' }}
                                                </span>
                                            @endforeach
                                            @if ($activeTutors->count() > 2)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600">
                                                    +{{ $activeTutors->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum ada tutor</span>
                                    @endif
                                </td>

                                <!-- Weekly Schedules -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $activeSchedules = $cls->schedules->where('status', 'active');
                                        $dayNames = [0 => 'Min', 1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab'];
                                    @endphp
                                    @if ($activeSchedules->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($activeSchedules as $sch)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold bg-gray-100 text-gray-700">
                                                    {{ $dayNames[$sch->day_of_week] ?? 'Hari' }} {{ substr($sch->start_time, 0, 5) }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum ada jadwal</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <x-cressco.badge :variant="$cls->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $cls->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 sm:px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.classes.show', $cls) }}"
                                           title="Detail & Jadwal Kelas"
                                           class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                            <x-cressco.icon-helper name="view" class="w-4 h-4" />
                                        </a>

                                        <button type="button"
                                                @click="openEdit({{ json_encode($cls) }})"
                                                title="Edit Kelas"
                                                class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition cursor-pointer">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <button type="button"
                                                @click="openToggle({{ json_encode($cls) }})"
                                                title="{{ $cls->status === 'active' ? 'Nonaktifkan Kelas' : 'Aktifkan Kelas' }}"
                                                class="p-1.5 {{ $cls->status === 'active' ? 'text-amber-500 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50' }} rounded-lg transition cursor-pointer">
                                            <x-cressco.icon-helper name="{{ $cls->status === 'active' ? 'clock' : 'check-circle' }}" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-500">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                                            <x-cressco.icon-helper name="folder" class="w-6 h-6" />
                                        </div>
                                        <div class="text-sm font-bold text-gray-800">Tidak ada kelas ditemukan</div>
                                        <p class="text-xs text-gray-400">
                                            Silakan sesuaikan filter pencarian atau buat kelas baru untuk cabang yang Anda kelola.
                                        </p>
                                        <x-cressco.button variant="primary" size="sm" @click="openCreate()">
                                            Buat Kelas Sekarang
                                        </x-cressco.button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Class Modal -->
        <div x-show="createModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     @click="createModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="folder" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Buat Kelas Baru</h3>
                                <p class="text-xs text-gray-500">Tambah rombel kelas bimbingan belajar baru.</p>
                            </div>
                        </div>
                        <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.classes.store') }}" class="mt-4 space-y-4">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required placeholder="Contoh: 12 IPA - Matematika UTBK"
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Cabang <span class="text-red-500">*</span></label>
                                <select name="branch_id" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="" disabled selected>Pilih Cabang</option>
                                    @foreach ($accessibleBranches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Mata Pelajaran</label>
                                <input type="text" name="subject" placeholder="Contoh: Matematika"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenjang / Level</label>
                                <input type="text" name="level" placeholder="Contoh: 12 SMA / SMP"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kapasitas Kursi</label>
                                <input type="number" name="capacity" min="1" max="200" value="15"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tutor Pengajar Awal (Opsional)</label>
                            <select name="tutor_id"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                <option value="">Belum Ditentukan</option>
                                @foreach ($tutors as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }} ({{ ucfirst($t->role) }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Awal</label>
                            <select name="status"
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                <option value="active" selected>Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="createModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <x-cressco.button type="submit" variant="primary" size="md">
                                Buat Kelas
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Class Modal -->
        <div x-show="editModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-lg p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="edit" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Edit Data Kelas</h3>
                                <p class="text-xs text-gray-500">Perbarui rincian rombel kelas bimbingan belajar.</p>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" :action="'{{ url('admin/classes') }}/' + editClass.id" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="name" x-model="editClass.name" required
                                   class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Cabang <span class="text-red-500">*</span></label>
                                <select name="branch_id" x-model="editClass.branch_id" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    @foreach ($accessibleBranches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Mata Pelajaran</label>
                                <input type="text" name="subject" x-model="editClass.subject"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenjang / Level</label>
                                <input type="text" name="level" x-model="editClass.level"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Kapasitas Kursi</label>
                                <input type="number" name="capacity" min="1" max="200" x-model="editClass.capacity"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Kelas</label>
                            <select name="status" x-model="editClass.status" required
                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="editModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <x-cressco.button type="submit" variant="primary" size="md">
                                Simpan Perubahan
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Toggle Status Confirmation Modal -->
        <div x-show="toggleModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="toggleModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     @click="toggleModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="toggleModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold"
                             :class="toggleClass.status === 'active' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'">
                            <x-cressco.icon-helper name="alert-circle" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900"
                                x-text="toggleClass.status === 'active' ? 'Nonaktifkan Kelas' : 'Aktifkan Kelas'"></h3>
                            <p class="text-xs text-gray-500">Konfirmasi status operasional kelas.</p>
                        </div>
                    </div>

                    <div class="py-4 text-xs text-gray-600">
                        Apakah Anda yakin ingin <strong x-text="toggleClass.status === 'active' ? 'menonaktifkan' : 'mengaktifkan'"></strong> kelas <strong class="text-gray-900" x-text="toggleClass.name"></strong>?
                    </div>

                    <form method="POST" :action="'{{ url('admin/classes') }}/' + toggleClass.id + '/toggle-status'">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="toggleModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    :class="toggleClass.status === 'active' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                                    class="px-4 py-2 text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer">
                                <span x-text="toggleClass.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
