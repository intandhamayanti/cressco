<x-owner-layout :tenant="$tenant" title="Management Cabang">
    <x-slot:breadcrumbSub>Management Cabang</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             toggleModalOpen: false,
             editBranch: { id: '', name: '', code: '', address: '', phone: '', status: 'active' },
             toggleBranch: { id: '', name: '', status: '' },
             openCreate() {
                 this.toggleModalOpen = false;
                 this.editModalOpen = false;
                 this.createModalOpen = true;
             },
             openEdit(branch) {
                 this.toggleModalOpen = false;
                 this.createModalOpen = false;
                 this.editBranch = {
                     id: branch.id,
                     name: branch.name,
                     code: branch.code || '',
                     address: branch.address || '',
                     phone: branch.phone || '',
                     status: branch.status || 'active'
                 };
                 this.editModalOpen = true;
             },
             openToggle(branch) {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleBranch = {
                     id: branch.id,
                     name: branch.name,
                     status: branch.status
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
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Management Cabang</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola seluruh cabang operasional {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button variant="primary" size="md" leadingIcon="plus" @click="openCreate()">
                    Tambah Cabang
                </x-cressco.button>
            </div>
        </div>

        <!-- Summary Metric Badges / Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-cressco.project-card
                title="Total Cabang"
                icon="building"
                iconColor="text-gray-500"
                value="{{ $totalBranchesCount }}"
                trend="Unit Cabang"
                trendType="neutral"
                subtitle="Seluruh cabang terdaftar di sistem"
            />

            <x-cressco.project-card
                title="Cabang Aktif"
                icon="check-circle"
                iconColor="text-emerald-500"
                value="{{ $activeBranchesCount }}"
                trend="Beroperasi"
                trendType="positive"
                subtitle="Cabang operasional reguler"
            />

            <x-cressco.project-card
                title="Cabang Nonaktif"
                icon="clock"
                iconColor="text-gray-400"
                value="{{ $inactiveBranchesCount }}"
                trend="Nonaktif"
                trendType="neutral"
                subtitle="Cabang ditutup sementara atau arsip"
            />
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.branches.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama cabang, kode, kota, atau nomor telepon..."
                           class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-gray-200 rounded-xl placeholder-gray-400 text-gray-800 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                    />
                </div>

                <!-- Status Filter Dropdown -->
                <div class="relative w-full sm:w-44">
                    <select name="status"
                            onchange="this.form.submit()"
                            class="w-full pl-3 pr-8 py-2 text-xs font-medium bg-white border border-gray-200 rounded-xl text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs appearance-none cursor-pointer">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Status Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Status Nonaktif</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- Submit Filter Button -->
                <x-cressco.button type="submit" variant="secondary" size="sm" class="w-full sm:w-auto">
                    <span>Filter</span>
                </x-cressco.button>

                @if ($search || ($status && $status !== 'all'))
                    <a href="{{ route('owner.branches.index') }}" class="text-xs text-gray-500 hover:text-terracotta-600 px-2 py-1 font-medium">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Branch List Table -->
        <div class="w-full bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs font-sans">
                    
                    <!-- Table Header -->
                    <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Nama Cabang</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Kota / Alamat</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600 text-center">Siswa Aktif</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600 text-center">Kelas</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Admin Bertugas</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Status</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($branches as $b)
                            <tr class="hover:bg-gray-50/70 transition group">
                                
                                <!-- Name & Code -->
                                <td class="py-3.5 px-4">
                                    <a href="{{ route('owner.branches.show', $b) }}" class="font-bold text-gray-900 group-hover:text-terracotta-600 transition flex items-center gap-2">
                                        <span>{{ $b->name }}</span>
                                    </a>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">Kode: {{ $b->code ?? '-' }}</div>
                                </td>

                                <!-- City & Address -->
                                <td class="py-3.5 px-4 max-w-xs truncate">
                                    <div class="font-medium text-gray-800 truncate">{{ $b->address ?: 'Belum ada alamat' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ $b->phone ?: 'No Telp: -' }}</div>
                                </td>

                                <!-- Active Students -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                                        {{ $b->students_count }} siswa
                                    </span>
                                </td>

                                <!-- Active Classes -->
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                                        {{ $b->classes_count }} kelas
                                    </span>
                                </td>

                                <!-- Admin Assigned -->
                                <td class="py-3.5 px-4">
                                    @if ($b->users->isNotEmpty())
                                        <div class="flex items-center gap-1.5">
                                            <div class="w-6 h-6 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-[10px]">
                                                {{ strtoupper(substr($b->users->first()->name, 0, 1)) }}
                                            </div>
                                            <span class="font-medium text-gray-800 truncate max-w-[120px]">{{ $b->users->first()->name }}</span>
                                            @if ($b->users->count() > 1)
                                                <span class="text-[10px] font-bold text-gray-400">+{{ $b->users->count() - 1 }}</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-gray-400 italic text-[11px]">Belum ditugaskan</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <x-cressco.badge :variant="$b->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $b->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- View Detail -->
                                        <a href="{{ route('owner.branches.show', $b) }}"
                                           title="Lihat Detail Cabang"
                                           class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                                            <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                        </a>

                                        <!-- Edit Modal Trigger -->
                                        <button type="button"
                                                @click.stop="openEdit({{ json_encode($b) }})"
                                                title="Edit Cabang"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-terracotta-600 hover:bg-gray-100 transition">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <!-- Status Toggle Button -->
                                        <button type="button"
                                                @click.stop="openToggle({{ json_encode($b) }})"
                                                title="{{ $b->status === 'active' ? 'Nonaktifkan Cabang' : 'Aktifkan Cabang' }}"
                                                class="p-1.5 rounded-lg {{ $b->status === 'active' ? 'text-gray-400 hover:text-amber-600' : 'text-gray-400 hover:text-emerald-600' }} hover:bg-gray-100 transition">
                                            @if ($b->status === 'active')
                                                <x-cressco.icon-helper name="clock" class="w-4 h-4" />
                                            @else
                                                <x-cressco.icon-helper name="check" class="w-4 h-4" />
                                            @endif
                                        </button>

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-400">
                                    <div class="w-10 h-10 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 flex items-center justify-center mx-auto mb-2">
                                        <x-cressco.icon-helper name="building" class="w-5 h-5" />
                                    </div>
                                    <p class="font-bold text-gray-600 text-sm">Tidak ada cabang ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba ubah kata kunci pencarian atau tambahkan cabang baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= CREATE BRANCH MODAL ================= -->
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

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center border border-terracotta-200/60">
                            <x-cressco.icon-helper name="building" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Tambah Cabang Baru</h3>
                            <p class="text-xs text-gray-500">Daftarkan cabang bimbel baru dalam naungan {{ $tenant->name ?? 'Prime Academy' }}.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.branches.store') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Branch Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Nama cabang (misal: Prime Academy - Yogyakarta)"
                               class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                        />
                    </div>

                    <!-- Code & Phone Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kode Cabang</label>
                            <input type="text"
                                   name="code"
                                   placeholder="Contoh: YOG-01 (Opsional)"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs uppercase"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text"
                                   name="phone"
                                   placeholder="Contoh: 0274-551234"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap / Kota</label>
                        <textarea name="address"
                                  rows="2"
                                  placeholder="Contoh: Jl. Kaliurang Km 5.5, Yogyakarta"
                                  class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"></textarea>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Status Operasional</label>
                        <select name="status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                            <option value="active" selected>Aktif (Siap Menerima Siswa & Jadwal)</option>
                            <option value="inactive">Nonaktif (Persiapan / Ditutup)</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Simpan Cabang
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ================= EDIT BRANCH MODAL ================= -->
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

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60">
                            <x-cressco.icon-helper name="edit" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Edit Data Cabang</h3>
                            <p class="text-xs text-gray-500">Perbarui informasi cabang operasional.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'/owner/branches/' + editBranch.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <!-- Branch Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               x-model="editBranch.name"
                               required
                               class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                        />
                    </div>

                    <!-- Code & Phone Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kode Cabang</label>
                            <input type="text"
                                   name="code"
                                   x-model="editBranch.code"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs uppercase"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text"
                                   name="phone"
                                   x-model="editBranch.phone"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>
                    </div>

                    <!-- Address -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap / Kota</label>
                        <textarea name="address"
                                  rows="2"
                                  x-model="editBranch.address"
                                  class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"></textarea>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Status Operasional</label>
                        <select name="status" x-model="editBranch.status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                            <option value="active">Aktif (Siap Menerima Siswa & Jadwal)</option>
                            <option value="inactive">Nonaktif (Persiapan / Ditutup)</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Perbarui Cabang
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
                         :class="toggleBranch.status === 'active' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                        <x-cressco.icon-helper name="building" class="w-6 h-6" />
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        <span x-text="toggleBranch.status === 'active' ? 'Nonaktifkan Cabang' : 'Aktifkan Cabang'"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                        Apakah Anda yakin ingin <span x-text="toggleBranch.status === 'active' ? 'menonaktifkan' : 'mengaktifkan'"></span> cabang <strong class="text-gray-900" x-text="toggleBranch.name"></strong>? Data historis kelas dan siswa tetap tersimpan aman.
                    </p>
                </div>

                <form method="POST" :action="'/owner/branches/' + toggleBranch.id + '/toggle-status'" class="pt-2 flex items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="closeAll()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                            :class="toggleBranch.status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-sm">
                        <span x-text="toggleBranch.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-owner-layout>
