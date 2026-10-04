<x-admin-layout :tenant="$tenant" title="Management Siswa">
    <x-slot:breadcrumbSub>Management Siswa</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             toggleModalOpen: false,
             editStudent: { id: '', branch_id: '', name: '', date_of_birth: '', gender: 'Laki-laki', phone: '', address: '', parent_name: '', parent_phone: '', notes: '', joined_at: '', status: 'active' },
             toggleStudent: { id: '', name: '', status: '' },
             openCreate() {
                 this.toggleModalOpen = false;
                 this.editModalOpen = false;
                 this.createModalOpen = true;
             },
             openEdit(student) {
                 this.toggleModalOpen = false;
                 this.createModalOpen = false;
                 this.editStudent = {
                     id: student.id,
                     branch_id: student.branch_id,
                     name: student.name,
                     date_of_birth: student.date_of_birth ? student.date_of_birth.substring(0, 10) : '',
                     gender: student.gender || 'Laki-laki',
                     phone: student.phone || '',
                     address: student.address || '',
                     parent_name: student.parent_name || '',
                     parent_phone: student.parent_phone || '',
                     notes: student.notes || '',
                     joined_at: student.joined_at ? student.joined_at.substring(0, 10) : '',
                     status: student.status || 'active'
                 };
                 this.editModalOpen = true;
             },
             openToggle(student) {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleStudent = {
                     id: student.id,
                     name: student.name,
                     status: student.status
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
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Management Siswa</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola pendaftaran siswa, profil, dan penempatan cabang di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button variant="primary" size="md" leadingIcon="plus" @click="openCreate()">
                    Daftarkan Siswa Baru
                </x-cressco.button>
            </div>
        </div>

        <!-- Summary Metric Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Siswa"
                icon="academic"
                iconColor="text-gray-500"
                value="{{ $totalStudentsCount }}"
                trend="Siswa"
                trendType="neutral"
                subtitle="Siswa terdaftar di cabang akses Anda"
            />

            <x-cressco.project-card
                title="Siswa Aktif"
                icon="check-circle"
                iconColor="text-emerald-500"
                value="{{ $activeStudentsCount }}"
                trend="Aktif Belajar"
                trendType="positive"
                subtitle="Siswa dengan status belajar aktif"
            />

            <x-cressco.project-card
                title="Siswa Nonaktif"
                icon="clock"
                iconColor="text-gray-400"
                value="{{ $inactiveStudentsCount }}"
                trend="Alumni / Cuti"
                trendType="neutral"
                subtitle="Siswa nonaktif atau telah selesai"
            />

            <x-cressco.project-card
                title="Cabang Akses"
                icon="building"
                iconColor="text-blue-500"
                value="{{ $accessibleBranches->count() }}"
                trend="Cabang"
                trendType="neutral"
                subtitle="Lokasi cabang yang Anda kelola"
            />
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search ?? '' }}"
                           placeholder="Cari nama siswa, nomor HP, nama orang tua, atau alamat..."
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition placeholder-gray-400">
                </div>

                <!-- Branch Filter (Only accessible branches) -->
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
                        <a href="{{ route('admin.students.index') }}" class="px-3 py-2 rounded-xl text-xs font-semibold text-gray-500 hover:text-gray-900 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Student Data Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 bg-gray-50/75 text-[11px] font-bold text-gray-500 uppercase tracking-wider">
                            <th class="py-3 px-4 sm:px-6">Siswa</th>
                            <th class="py-3 px-4">Kontak & Orang Tua</th>
                            <th class="py-3 px-4">Cabang</th>
                            <th class="py-3 px-4">Kelas Diikuti</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 sm:px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-xs">
                        @forelse ($students as $stu)
                            <tr class="hover:bg-gray-50/60 transition group">
                                
                                <!-- Student Name & Avatar -->
                                <td class="py-3.5 px-4 sm:px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0 border border-terracotta-200/60">
                                            {{ strtoupper(substr($stu->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.students.show', $stu) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition block leading-tight">
                                                {{ $stu->name }}
                                            </a>
                                            <span class="text-[11px] text-gray-400 mt-0.5 block">
                                                Terdaftar: {{ $stu->joined_at ? $stu->joined_at->translatedFormat('d M Y') : '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contacts & Parent -->
                                <td class="py-3.5 px-4 text-gray-600">
                                    <div class="space-y-0.5">
                                        <div class="font-medium text-gray-800">{{ $stu->phone ?: '-' }}</div>
                                        <div class="text-[11px] text-gray-400">
                                            Ortu: <strong class="text-gray-600">{{ $stu->parent_name ?: '-' }}</strong> {{ $stu->parent_phone ? "({$stu->parent_phone})" : '' }}
                                        </div>
                                    </div>
                                </td>

                                <!-- Branch -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200/60">
                                        {{ $stu->branch?->name ?? 'Cabang' }}
                                    </span>
                                </td>

                                <!-- Enrolled Classes -->
                                <td class="py-3.5 px-4">
                                    @php
                                        $activeEnrollments = $stu->enrollments->where('status', 'active');
                                    @endphp
                                    @if ($activeEnrollments->isNotEmpty())
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($activeEnrollments->take(2) as $enr)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200/60">
                                                    {{ $enr->classModel?->name ?? 'Kelas' }}
                                                </span>
                                            @endforeach
                                            @if ($activeEnrollments->count() > 2)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-gray-100 text-gray-600">
                                                    +{{ $activeEnrollments->count() - 2 }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum ada kelas</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <x-cressco.badge :variant="$stu->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $stu->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 sm:px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('admin.students.show', $stu) }}"
                                           title="Detail Siswa"
                                           class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition">
                                            <x-cressco.icon-helper name="view" class="w-4 h-4" />
                                        </a>

                                        <button type="button"
                                                @click="openEdit({{ json_encode($stu) }})"
                                                title="Edit Data Siswa"
                                                class="p-1.5 text-gray-400 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition cursor-pointer">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <button type="button"
                                                @click="openToggle({{ json_encode($stu) }})"
                                                title="{{ $stu->status === 'active' ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa' }}"
                                                class="p-1.5 {{ $stu->status === 'active' ? 'text-amber-500 hover:text-amber-700 hover:bg-amber-50' : 'text-emerald-600 hover:text-emerald-800 hover:bg-emerald-50' }} rounded-lg transition cursor-pointer">
                                            <x-cressco.icon-helper name="{{ $stu->status === 'active' ? 'clock' : 'check-circle' }}" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-500">
                                    <div class="max-w-sm mx-auto space-y-3">
                                        <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto">
                                            <x-cressco.icon-helper name="academic" class="w-6 h-6" />
                                        </div>
                                        <div class="text-sm font-bold text-gray-800">Tidak ada data siswa ditemukan</div>
                                        <p class="text-xs text-gray-400">
                                            Silakan sesuaikan filter pencarian atau daftarkan siswa baru untuk cabang Anda.
                                        </p>
                                        <x-cressco.button variant="primary" size="sm" @click="openCreate()">
                                            Daftarkan Siswa Sekarang
                                        </x-cressco.button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Create Student Modal -->
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
                     class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="academic" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Daftarkan Siswa Baru</h3>
                                <p class="text-xs text-gray-500">Lengkapi formulir pendaftaran siswa baru pada cabang yang Anda kelola.</p>
                            </div>
                        </div>
                        <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.students.store') }}" class="mt-4 space-y-4">
                        @csrf
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required placeholder="Contoh: Budi Santoso"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Branch (Scoped to Admin's accessible branches) -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Cabang Penempatan <span class="text-red-500">*</span></label>
                                <select name="branch_id" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="" disabled selected>Pilih Cabang</option>
                                    @foreach ($accessibleBranches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>

                            <!-- DOB -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="date_of_birth"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Student Phone -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WhatsApp Siswa</label>
                                <input type="text" name="phone" placeholder="081234567890"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Parent Name -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua / Wali</label>
                                <input type="text" name="parent_name" placeholder="Nama ayah/ibu"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Parent Phone -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WhatsApp Orang Tua</label>
                                <input type="text" name="parent_phone" placeholder="081234567890"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Joined At -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                                <input type="date" name="joined_at" value="{{ date('Y-m-d') }}"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Status Awal</label>
                                <select name="status"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="active" selected>Aktif Belajar</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Domisili</label>
                                <textarea name="address" rows="2" placeholder="Alamat lengkap tempat tinggal siswa..."
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan Akademik / Khusus</label>
                                <textarea name="notes" rows="2" placeholder="Catatan minat bakat, riwayat belajar, dll..."
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="createModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <x-cressco.button type="submit" variant="primary" size="md">
                                Daftarkan Siswa
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Student Modal -->
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
                     class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="edit" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Edit Data Siswa</h3>
                                <p class="text-xs text-gray-500">Perbarui informasi profil siswa dan data orang tua.</p>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" :action="'{{ url('admin/students') }}/' + editStudent.id" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="editStudent.name" required
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Branch -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Cabang Penempatan <span class="text-red-500">*</span></label>
                                <select name="branch_id" x-model="editStudent.branch_id" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    @foreach ($accessibleBranches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" x-model="editStudent.gender"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>

                            <!-- DOB -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="date_of_birth" x-model="editStudent.date_of_birth"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Student Phone -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WhatsApp Siswa</label>
                                <input type="text" name="phone" x-model="editStudent.phone"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Parent Name -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua / Wali</label>
                                <input type="text" name="parent_name" x-model="editStudent.parent_name"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Parent Phone -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WhatsApp Orang Tua</label>
                                <input type="text" name="parent_phone" x-model="editStudent.parent_phone"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Joined At -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                                <input type="date" name="joined_at" x-model="editStudent.joined_at"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Status Belajar</label>
                                <select name="status" x-model="editStudent.status" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Domisili</label>
                                <textarea name="address" x-model="editStudent.address" rows="2"
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan</label>
                                <textarea name="notes" x-model="editStudent.notes" rows="2"
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>
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
                             :class="toggleStudent.status === 'active' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'">
                            <x-cressco.icon-helper name="alert-circle" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900"
                                x-text="toggleStudent.status === 'active' ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa'"></h3>
                            <p class="text-xs text-gray-500">Konfirmasi perubahan status keaktifan siswa.</p>
                        </div>
                    </div>

                    <div class="py-4 text-xs text-gray-600">
                        Apakah Anda yakin ingin <strong x-text="toggleStudent.status === 'active' ? 'menonaktifkan' : 'mengaktifkan'"></strong> siswa <strong class="text-gray-900" x-text="toggleStudent.name"></strong>?
                    </div>

                    <form method="POST" :action="'{{ url('admin/students') }}/' + toggleStudent.id + '/toggle-status'">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="toggleModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    :class="toggleStudent.status === 'active' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white'"
                                    class="px-4 py-2 text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer">
                                <span x-text="toggleStudent.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
