<x-owner-layout :tenant="$tenant" title="Management Siswa">
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
                    Kelola data siswa, registrasi baru, dan penempatan cabang di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button
                    as="a"
                    :href="route('owner.imports.create', 'students')"
                    variant="outline"
                    size="md"
                    leadingIcon="upload"
                >
                    Import Siswa
                </x-cressco.button>

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
                subtitle="Seluruh siswa terdaftar di sistem"
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
                title="Siswa Nonaktif / Lulus"
                icon="clock"
                iconColor="text-gray-400"
                value="{{ $inactiveStudentsCount }}"
                trend="Alumni / Cuti"
                trendType="neutral"
                subtitle="Siswa nonaktif atau telah lulus"
            />

            <x-cressco.project-card
                title="Sebaran Cabang"
                icon="building"
                iconColor="text-blue-500"
                value="{{ $branches->count() }}"
                trend="Cabang"
                trendType="neutral"
                subtitle="Total lokasi cabang aktif operasional"
            />
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.students.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama siswa, no telp, orang tua, atau alamat..."
                           class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-gray-200 rounded-xl placeholder-gray-400 text-gray-800 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                    />
                </div>

                <!-- Branch Filter -->
                <div class="relative w-full sm:w-48">
                    <select name="branch_id"
                            onchange="this.form.submit()"
                            class="w-full pl-3 pr-8 py-2 text-xs font-medium bg-white border border-gray-200 rounded-xl text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs appearance-none cursor-pointer">
                        <option value="all" {{ $branchId === 'all' ? 'selected' : '' }}>Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>
                                {{ $b->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="relative w-full sm:w-36">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full pl-3 pr-8 py-2 text-xs font-medium bg-white border border-gray-200 rounded-xl text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs appearance-none cursor-pointer">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- Submit Filter Button -->
                <x-cressco.button type="submit" variant="secondary" size="sm" class="w-full sm:w-auto">
                    <span>Filter</span>
                </x-cressco.button>

                @if ($search || ($branchId && $branchId !== 'all') || ($status && $status !== 'all'))
                    <a href="{{ route('owner.students.index') }}" class="text-xs text-gray-500 hover:text-terracotta-600 px-2 py-1 font-medium">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Student List Table -->
        <div class="w-full bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs font-sans">
                    
                    <!-- Table Header -->
                    <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Nama Siswa</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Cabang</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Kontak & Orang Tua</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Kelas Terdaftar</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Bergabung</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Status</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($students as $s)
                            <tr class="hover:bg-gray-50/70 transition group">
                                
                                <!-- Name & Gender -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($s->name, 0, 2)) }}
                                        </div>
                                        <div>
                                            <a href="{{ route('owner.students.show', $s) }}" class="font-bold text-gray-900 group-hover:text-terracotta-600 transition truncate block">
                                                {{ $s->name }}
                                            </a>
                                            <div class="text-[11px] text-gray-400">
                                                {{ $s->gender ?: 'Siswa' }} • {{ $s->date_of_birth ? $s->date_of_birth->translatedFormat('d M Y') : '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Branch -->
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-gray-100 text-gray-800">
                                        {{ $s->branch?->name ?? 'Cabang Pusat' }}
                                    </span>
                                </td>

                                <!-- Contact & Parent -->
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-800">{{ $s->phone ?: 'No Telp: -' }}</div>
                                    @if ($s->parent_name)
                                        <div class="text-[11px] text-gray-400">Ortu: {{ $s->parent_name }} ({{ $s->parent_phone ?: '-' }})</div>
                                    @endif
                                </td>

                                <!-- Enrolled Classes (Concise Count) -->
                                <td class="py-3.5 px-4">
                                    @if ($s->enrollments->isNotEmpty())
                                        <div>
                                            <span class="font-bold text-gray-900 text-xs">{{ $s->enrollments->count() }} Kelas</span>
                                            <span class="text-[11px] text-gray-400 block mt-0.5">Terdaftar aktif</span>
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum terdaftar</span>
                                    @endif
                                </td>

                                <!-- Joined Date -->
                                <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                                    {{ $s->joined_at ? $s->joined_at->translatedFormat('d M Y') : '-' }}
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <x-cressco.badge :variant="$s->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $s->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View Detail -->
                                        <a href="{{ route('owner.students.show', $s) }}"
                                           class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-xl border border-gray-200/90 bg-white hover:bg-gray-50 text-gray-700 font-bold text-xs transition shadow-2xs">
                                            <span>Detail</span>
                                            <x-cressco.icon-helper name="chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                                        </a>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button"
                                                @click.stop="openEdit({{ json_encode($s) }})"
                                                title="Edit Data Siswa"
                                                class="p-1.5 rounded-xl border border-transparent hover:border-gray-200 text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <!-- Status Toggle Button -->
                                        <button type="button"
                                                @click.stop="openToggle({{ json_encode($s) }})"
                                                title="{{ $s->status === 'active' ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa' }}"
                                                class="p-1.5 rounded-xl border border-transparent hover:border-amber-200 text-gray-400 hover:text-amber-600 hover:bg-amber-50 transition">
                                            <x-cressco.icon-helper name="{{ $s->status === 'active' ? 'close' : 'check' }}" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="w-10 h-10 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 flex items-center justify-center mx-auto mb-2">
                                        <x-cressco.icon-helper name="academic" class="w-5 h-5" />
                                    </div>
                                    <p class="font-bold text-gray-600 text-sm">Tidak ada siswa ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan filter pencarian atau daftarkan siswa baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= CREATE STUDENT MODAL ================= -->
        <div x-show="createModalOpen"
             x-cloak
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans">
            
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="closeAll()"></div>

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center border border-terracotta-200/60">
                            <x-cressco.icon-helper name="academic" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Daftarkan Siswa Baru</h3>
                            <p class="text-xs text-gray-500">Registrasi siswa bimbingan belajar baru.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.students.store') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Name & Branch Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Siswa <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="name"
                                   required
                                   placeholder="Nama lengkap siswa"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cabang Bimbel <span class="text-red-500">*</span></label>
                            <select name="branch_id"
                                    required
                                    class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="">Pilih Cabang</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date of Birth & Gender Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date"
                                   name="date_of_birth"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Phone & Address Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No Telepon / WhatsApp Siswa</label>
                            <input type="text"
                                   name="phone"
                                   placeholder="08123456789"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Tempat Tinggal</label>
                            <input type="text"
                                   name="address"
                                   placeholder="Alamat rumah / kota"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Parent Name & Parent Phone Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua / Wali</label>
                            <input type="text"
                                   name="parent_name"
                                   placeholder="Nama orang tua"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No Telepon Orang Tua</label>
                            <input type="text"
                                   name="parent_phone"
                                   placeholder="081299887766"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Joined Date & Status Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                            <input type="date"
                                   name="joined_at"
                                   value="{{ now()->toDateString() }}"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Keaktifan</label>
                            <select name="status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="active" selected>Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan / Target Belajar</label>
                        <textarea name="notes"
                                  rows="2"
                                  placeholder="Contoh: Target lolos SNBT Kedokteran, butuh penguatan Fisika"
                                  class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Simpan Siswa
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ================= EDIT STUDENT MODAL ================= -->
        <div x-show="editModalOpen"
             x-cloak
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans">
            
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="closeAll()"></div>

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60">
                            <x-cressco.icon-helper name="edit" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Edit Data Siswa</h3>
                            <p class="text-xs text-gray-500">Perbarui informasi profil dan kontak siswa.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'/owner/students/' + editStudent.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <!-- Name & Branch Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Siswa <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="name"
                                   x-model="editStudent.name"
                                   required
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cabang Bimbel <span class="text-red-500">*</span></label>
                            <select name="branch_id"
                                    x-model="editStudent.branch_id"
                                    required
                                    class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date of Birth & Gender Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date"
                                   name="date_of_birth"
                                   x-model="editStudent.date_of_birth"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender" x-model="editStudent.gender" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Phone & Address Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No Telepon Siswa</label>
                            <input type="text"
                                   name="phone"
                                   x-model="editStudent.phone"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Tempat Tinggal</label>
                            <input type="text"
                                   name="address"
                                   x-model="editStudent.address"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Parent Name & Parent Phone Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua</label>
                            <input type="text"
                                   name="parent_name"
                                   x-model="editStudent.parent_name"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No Telepon Orang Tua</label>
                            <input type="text"
                                   name="parent_phone"
                                   x-model="editStudent.parent_phone"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Joined Date & Status Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                            <input type="date"
                                   name="joined_at"
                                   x-model="editStudent.joined_at"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Keaktifan <span class="text-red-500">*</span></label>
                            <select name="status" x-model="editStudent.status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes"
                                  rows="2"
                                  x-model="editStudent.notes"
                                  class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Perbarui Siswa
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ================= TOGGLE STATUS CONFIRMATION MODAL ================= -->
        <div x-show="toggleModalOpen"
             x-cloak
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans">
            
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="closeAll()"></div>

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-start justify-between">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center border shadow-2xs"
                         :class="toggleStudent.status === 'active' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                        <x-cressco.icon-helper name="academic" class="w-6 h-6" />
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        <span x-text="toggleStudent.status === 'active' ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa'"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                        Apakah Anda yakin ingin <span x-text="toggleStudent.status === 'active' ? 'menonaktifkan' : 'mengaktifkan'"></span> siswa <strong class="text-gray-900" x-text="toggleStudent.name"></strong>? Data kelas dan pembayaran tetap tersimpan aman.
                    </p>
                </div>

                <form method="POST" :action="'/owner/students/' + toggleStudent.id + '/toggle-status'" class="pt-2 flex items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="closeAll()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                            :class="toggleStudent.status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-sm">
                        <span x-text="toggleStudent.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-owner-layout>
