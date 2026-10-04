<x-owner-layout :tenant="$tenant" title="Management Users">
    <x-slot:breadcrumbSub>Management Users</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             toggleModalOpen: false,
             editUser: { id: '', name: '', email: '', role: 'admin', status: 'active', branch_ids: [] },
             toggleUser: { id: '', name: '', status: '' },
             openCreate() {
                 this.toggleModalOpen = false;
                 this.editModalOpen = false;
                 this.createModalOpen = true;
             },
             openEdit(user) {
                 this.toggleModalOpen = false;
                 this.createModalOpen = false;
                 this.editUser = {
                     id: user.id,
                     name: user.name,
                     email: user.email,
                     role: user.role,
                     status: user.status,
                     branch_ids: user.branches ? user.branches.map(b => b.id) : []
                 };
                 this.editModalOpen = true;
             },
             openToggle(user) {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleUser = {
                     id: user.id,
                     name: user.name,
                     status: user.status
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
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Management Users</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola akun pengelola, admin cabang, dan tutor dalam naungan {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button variant="primary" size="md" leadingIcon="plus" @click="openCreate()">
                    Tambah / Invite User
                </x-cressco.button>
            </div>
        </div>

        <!-- Summary Metric Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cressco.project-card
                title="Total Pengguna"
                icon="users"
                iconColor="text-gray-500"
                value="{{ $totalUsersCount }}"
                trend="Semua Role"
                trendType="neutral"
                subtitle="Akun pengelola & operasional"
            />

            <x-cressco.project-card
                title="Admin Cabang"
                icon="building"
                iconColor="text-blue-500"
                value="{{ $adminCount }}"
                trend="Staff Cabang"
                trendType="neutral"
                subtitle="Petugas operasional cabang"
            />

            <x-cressco.project-card
                title="Tutor / Tentor"
                icon="academic"
                iconColor="text-purple-500"
                value="{{ $tutorCount }}"
                trend="Pengajar"
                trendType="neutral"
                subtitle="Tenaga pengajar terdaftar"
            />

            <x-cressco.project-card
                title="Status Aktif"
                icon="check-circle"
                iconColor="text-emerald-500"
                value="{{ $activeCount }}"
                trend="Aktif"
                trendType="positive"
                subtitle="User dengan akses sistem aktif"
            />
        </div>

        <!-- Search & Filter Controls -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs">
            <form method="GET" action="{{ route('owner.users.index') }}" class="flex flex-col sm:flex-row items-center gap-3">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="search" class="w-4 h-4" />
                    </div>
                    <input type="text"
                           name="search"
                           value="{{ $search }}"
                           placeholder="Cari nama pengguna atau email..."
                           class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-gray-200 rounded-xl placeholder-gray-400 text-gray-800 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                    />
                </div>

                <!-- Role Filter -->
                <div class="relative w-full sm:w-36">
                    <select name="role"
                            onchange="this.form.submit()"
                            class="w-full pl-3 pr-8 py-2 text-xs font-medium bg-white border border-gray-200 rounded-xl text-gray-700 focus:outline-hidden focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs appearance-none cursor-pointer">
                        <option value="all" {{ $role === 'all' ? 'selected' : '' }}>Semua Role</option>
                        <option value="owner" {{ $role === 'owner' ? 'selected' : '' }}>Owner</option>
                        <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="tutor" {{ $role === 'tutor' ? 'selected' : '' }}>Tutor</option>
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
                        <option value="invited" {{ $status === 'invited' ? 'selected' : '' }}>Invited</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center pr-2.5 pointer-events-none text-gray-400">
                        <x-cressco.icon-helper name="chevron-down" class="w-3.5 h-3.5" />
                    </div>
                </div>

                <!-- Branch Filter -->
                <div class="relative w-full sm:w-44">
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

                <!-- Filter Button -->
                <x-cressco.button type="submit" variant="secondary" size="sm" class="w-full sm:w-auto">
                    <span>Filter</span>
                </x-cressco.button>

                @if ($search || ($role && $role !== 'all') || ($status && $status !== 'all') || ($branchId && $branchId !== 'all'))
                    <a href="{{ route('owner.users.index') }}" class="text-xs text-gray-500 hover:text-terracotta-600 px-2 py-1 font-medium">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Users Table -->
        <div class="w-full bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs font-sans">
                    
                    <!-- Table Header -->
                    <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Nama & Email</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Role</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Akses Cabang</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Status</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600">Terdaftar Sejak</th>
                            <th class="py-3.5 px-4 font-bold text-gray-600 text-right">Aksi</th>
                        </tr>
                    </thead>

                    <!-- Table Body -->
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($users as $u)
                            <tr class="hover:bg-gray-50/70 transition group">
                                
                                <!-- Name & Avatar -->
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full {{ $u->isOwner() ? 'bg-terracotta-100 text-terracotta-800' : ($u->isAdmin() ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }} flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ strtoupper(substr($u->name, 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('owner.users.show', $u) }}" class="font-bold text-gray-900 group-hover:text-terracotta-600 transition truncate block">
                                                {{ $u->name }}
                                                @if (auth()->id() === $u->id)
                                                    <span class="text-[10px] text-terracotta-600 font-semibold ml-1">(Anda)</span>
                                                @endif
                                            </a>
                                            <div class="text-[11px] text-gray-400 truncate">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Role Badge -->
                                <td class="py-3.5 px-4">
                                    @if ($u->isOwner())
                                        <x-cressco.badge variant="terracotta" size="sm">Owner</x-cressco.badge>
                                    @elseif ($u->isAdmin())
                                        <x-cressco.badge variant="info" size="sm">Admin</x-cressco.badge>
                                    @elseif ($u->isTutor())
                                        <x-cressco.badge variant="purple" size="sm">Tutor</x-cressco.badge>
                                    @else
                                        <x-cressco.badge variant="gray" size="sm">{{ ucfirst($u->role) }}</x-cressco.badge>
                                    @endif
                                </td>

                                <!-- Branch Access -->
                                <td class="py-3.5 px-4">
                                    @if ($u->isOwner())
                                        <span class="text-xs font-semibold text-gray-700">Semua Cabang (Tenant Scope)</span>
                                    @elseif ($u->isAdmin())
                                        @if ($u->branches->isNotEmpty())
                                            <div class="flex flex-wrap gap-1">
                                                @foreach ($u->branches as $ub)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold bg-gray-100 text-gray-700">
                                                        {{ $ub->name }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic text-[11px]">Belum ada cabang</span>
                                        @endif
                                    @elseif ($u->isTutor())
                                        <span class="text-gray-500 text-[11px]">Berdasarkan Penugasan Mengajar</span>
                                    @endif
                                </td>

                                <!-- Status Badge -->
                                <td class="py-3.5 px-4">
                                    <x-cressco.badge :variant="$u->status === 'active' ? 'success' : ($u->status === 'invited' ? 'warning' : 'gray')" dot>
                                        {{ $u->status === 'active' ? 'Aktif' : ($u->status === 'invited' ? 'Invited' : 'Nonaktif') }}
                                    </x-cressco.badge>
                                </td>

                                <!-- Registered At -->
                                <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                                    {{ $u->created_at ? $u->created_at->translatedFormat('d M Y') : '-' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        
                                        <!-- View Detail -->
                                        <a href="{{ route('owner.users.show', $u) }}"
                                           title="Lihat Detail User"
                                           class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 transition">
                                            <x-cressco.icon-helper name="chevron-right" class="w-4 h-4" />
                                        </a>

                                        <!-- Edit User Trigger -->
                                        <button type="button"
                                                @click.stop="openEdit({{ json_encode($u) }})"
                                                title="Edit User"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-terracotta-600 hover:bg-gray-100 transition">
                                            <x-cressco.icon-helper name="edit" class="w-4 h-4" />
                                        </button>

                                        <!-- Status Toggle Button -->
                                        @if (auth()->id() !== $u->id)
                                            <button type="button"
                                                    @click.stop="openToggle({{ json_encode($u) }})"
                                                    title="{{ $u->status === 'active' ? 'Nonaktifkan User' : 'Aktifkan User' }}"
                                                    class="p-1.5 rounded-lg {{ $u->status === 'active' ? 'text-gray-400 hover:text-amber-600' : 'text-gray-400 hover:text-emerald-600' }} hover:bg-gray-100 transition">
                                                @if ($u->status === 'active')
                                                    <x-cressco.icon-helper name="clock" class="w-4 h-4" />
                                                @else
                                                    <x-cressco.icon-helper name="check" class="w-4 h-4" />
                                                @endif
                                            </button>
                                        @endif

                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-gray-400">
                                    <div class="w-10 h-10 rounded-2xl bg-gray-50 border border-gray-200 text-gray-400 flex items-center justify-center mx-auto mb-2">
                                        <x-cressco.icon-helper name="users" class="w-5 h-5" />
                                    </div>
                                    <p class="font-bold text-gray-600 text-sm">Tidak ada pengguna ditemukan</p>
                                    <p class="text-xs text-gray-400 mt-0.5">Coba sesuaikan filter atau tambahkan user baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= CREATE / INVITE USER MODAL ================= -->
        <div x-show="createModalOpen"
             x-cloak
             x-transition:enter="ease-out duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans"
             x-data="{ newRole: 'admin' }">
            
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs transition-opacity" @click="closeAll()"></div>

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center border border-terracotta-200/60">
                            <x-cressco.icon-helper name="users" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Tambah / Invite User Baru</h3>
                            <p class="text-xs text-gray-500">Tambahkan Admin Cabang atau Tutor baru dalam tenant.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.users.store') }}" class="space-y-4">
                    @csrf
                    
                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               required
                               placeholder="Nama lengkap pengguna"
                               class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                        />
                    </div>

                    <!-- Email & Role Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email"
                                   name="email"
                                   required
                                   placeholder="nama@cressco.test"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Role Pengguna <span class="text-red-500">*</span></label>
                            <select name="role"
                                    x-model="newRole"
                                    required
                                    class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="admin">Admin Cabang</option>
                                <option value="tutor">Tutor / Tentor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password & Status Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Password Awal</label>
                            <input type="text"
                                   name="password"
                                   placeholder="Default: Password123!"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Akun</label>
                            <select name="status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="active" selected>Aktif</option>
                                <option value="invited">Invited (Undangan)</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Branch Access for Admin (Shown only when role is admin) -->
                    <div x-show="newRole === 'admin'" class="space-y-2 pt-1">
                        <label class="block text-xs font-bold text-gray-700">Tugaskan Akses Cabang</label>
                        <p class="text-[11px] text-gray-400">Pilih cabang yang dapat dikelola oleh Admin ini:</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-gray-50/80 rounded-xl border border-gray-100 max-h-36 overflow-y-auto">
                            @foreach ($branches as $b)
                                <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer hover:text-gray-900">
                                    <input type="checkbox"
                                           name="branch_ids[]"
                                           value="{{ $b->id }}"
                                           class="rounded text-terracotta-600 focus:ring-terracotta-500 border-gray-300">
                                    <span>{{ $b->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Simpan Pengguna
                        </button>
                    </div>
                </form>

            </div>
        </div>

        <!-- ================= EDIT USER MODAL ================= -->
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
                            <h3 class="text-base font-bold text-gray-900">Edit Data Pengguna</h3>
                            <p class="text-xs text-gray-500">Perbarui profil, role, atau akses cabang.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" :action="'/owner/users/' + editUser.id" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <!-- Full Name -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               x-model="editUser.name"
                               required
                               class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                        />
                    </div>

                    <!-- Email & Role Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                            <input type="email"
                                   name="email"
                                   x-model="editUser.email"
                                   required
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Role Pengguna <span class="text-red-500">*</span></label>
                            <select name="role"
                                    x-model="editUser.role"
                                    required
                                    class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <template x-if="editUser.role === 'owner'">
                                    <option value="owner">Owner</option>
                                </template>
                                <option value="admin">Admin Cabang</option>
                                <option value="tutor">Tutor / Tentor</option>
                            </select>
                        </div>
                    </div>

                    <!-- Password & Status Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Password Baru (Opsional)</label>
                            <input type="password"
                                   name="password"
                                   placeholder="Kosongkan jika tidak diubah"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 placeholder-gray-400 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Akun <span class="text-red-500">*</span></label>
                            <select name="status" x-model="editUser.status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                <option value="active">Aktif</option>
                                <option value="invited">Invited</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Branch Access for Admin -->
                    <div x-show="editUser.role === 'admin'" class="space-y-2 pt-1">
                        <label class="block text-xs font-bold text-gray-700">Tugaskan Akses Cabang</label>
                        <p class="text-[11px] text-gray-400">Pilih cabang yang dapat dikelola oleh Admin ini:</p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 p-3 bg-gray-50/80 rounded-xl border border-gray-100 max-h-36 overflow-y-auto">
                            @foreach ($branches as $b)
                                <label class="flex items-center gap-2 text-xs font-medium text-gray-700 cursor-pointer hover:text-gray-900">
                                    <input type="checkbox"
                                           name="branch_ids[]"
                                           value="{{ $b->id }}"
                                           :checked="editUser.branch_ids && editUser.branch_ids.includes('{{ $b->id }}')"
                                           class="rounded text-terracotta-600 focus:ring-terracotta-500 border-gray-300">
                                    <span>{{ $b->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Perbarui Pengguna
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
                         :class="toggleUser.status === 'active' ? 'bg-amber-50 text-amber-600 border-amber-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'">
                        <x-cressco.icon-helper name="users" class="w-6 h-6" />
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <div>
                    <h3 class="text-base font-bold text-gray-900">
                        <span x-text="toggleUser.status === 'active' ? 'Nonaktifkan Pengguna' : 'Aktifkan Pengguna'"></span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-2 leading-relaxed">
                        Apakah Anda yakin ingin <span x-text="toggleUser.status === 'active' ? 'menonaktifkan' : 'mengaktifkan'"></span> akun <strong class="text-gray-900" x-text="toggleUser.name"></strong>? Pengguna nonaktif tidak dapat masuk ke sistem.
                    </p>
                </div>

                <form method="POST" :action="'/owner/users/' + toggleUser.id + '/toggle-status'" class="pt-2 flex items-center gap-3">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="closeAll()" class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                        Batal
                    </button>
                    <button type="submit"
                            :class="toggleUser.status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700'"
                            class="flex-1 px-4 py-2.5 rounded-xl text-xs font-bold text-white transition shadow-sm">
                        <span x-text="toggleUser.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-owner-layout>
