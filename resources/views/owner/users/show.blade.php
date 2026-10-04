<x-owner-layout :tenant="$tenant" :title="$user->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('owner.users.index') }}" class="hover:text-terracotta-600 transition">Management Users</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">{{ $user->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             editModalOpen: false,
             toggleModalOpen: false,
             editUser: {
                 id: '{{ $user->id }}',
                 name: '{{ $user->name }}',
                 email: '{{ $user->email }}',
                 role: '{{ $user->role }}',
                 status: '{{ $user->status }}',
                 branch_ids: {{ json_encode($user->branches->pluck('id')) }}
             },
             closeAll() {
                 this.editModalOpen = false;
                 this.toggleModalOpen = false;
             }
         }">
        
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl {{ $user->isOwner() ? 'bg-terracotta-100 text-terracotta-800 border-terracotta-200/60' : ($user->isAdmin() ? 'bg-blue-100 text-blue-800 border-blue-200/60' : 'bg-purple-100 text-purple-800 border-purple-200/60') }} flex items-center justify-center border font-bold text-base shadow-2xs">
                    {{ strtoupper(substr($user->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $user->name }}</h1>
                        @if ($user->isOwner())
                            <x-cressco.badge variant="terracotta" size="sm">Owner</x-cressco.badge>
                        @elseif ($user->isAdmin())
                            <x-cressco.badge variant="info" size="sm">Admin</x-cressco.badge>
                        @elseif ($user->isTutor())
                            <x-cressco.badge variant="purple" size="sm">Tutor</x-cressco.badge>
                        @endif

                        <x-cressco.badge :variant="$user->status === 'active' ? 'success' : ($user->status === 'invited' ? 'warning' : 'gray')" dot>
                            {{ $user->status === 'active' ? 'Aktif' : ($user->status === 'invited' ? 'Invited' : 'Nonaktif') }}
                        </x-cressco.badge>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $user->email }} • Terdaftar sejak {{ $user->created_at->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('owner.users.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                    ← Kembali
                </a>

                <x-cressco.button variant="secondary" size="md" leadingIcon="edit" @click="editModalOpen = true">
                    Edit User
                </x-cressco.button>

                @if (auth()->id() !== $user->id)
                    <x-cressco.button variant="{{ $user->status === 'active' ? 'secondary' : 'primary' }}" size="md" @click="toggleModalOpen = true">
                        @if ($user->status === 'active')
                            <span class="text-amber-700">Nonaktifkan User</span>
                        @else
                            <span>Aktifkan User</span>
                        @endif
                    </x-cressco.button>
                @endif
            </div>
        </div>

        <!-- 2 Columns: Profile Details & Role Specific Operations -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Column 1: Account Information -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Informasi Akun</h2>

                <div class="space-y-3.5 text-xs">
                    <div>
                        <span class="text-gray-400 block text-[11px]">Nama Lengkap</span>
                        <p class="font-bold text-gray-900 mt-0.5">{{ $user->name }}</p>
                    </div>

                    <div>
                        <span class="text-gray-400 block text-[11px]">Alamat Email</span>
                        <p class="font-medium text-gray-800 mt-0.5">{{ $user->email }}</p>
                    </div>

                    <div>
                        <span class="text-gray-400 block text-[11px]">Role Sistem</span>
                        <p class="font-bold text-gray-800 mt-0.5 capitalize">{{ $user->role }}</p>
                    </div>

                    <div>
                        <span class="text-gray-400 block text-[11px]">Status Akun</span>
                        <div class="mt-1">
                            <x-cressco.badge :variant="$user->status === 'active' ? 'success' : ($user->status === 'invited' ? 'warning' : 'gray')" dot>
                                {{ $user->status === 'active' ? 'Aktif' : ($user->status === 'invited' ? 'Invited' : 'Nonaktif') }}
                            </x-cressco.badge>
                        </div>
                    </div>

                    <div>
                        <span class="text-gray-400 block text-[11px]">Terdaftar Pada</span>
                        <p class="font-medium text-gray-800 mt-0.5">{{ $user->created_at->translatedFormat('d F Y, H:i') }}</p>
                    </div>
                </div>
            </div>

            <!-- Column 2 & 3: Role Scope (Branches for Admin / Classes for Tutor) -->
            <div class="lg:col-span-2 space-y-6">
                
                @if ($user->isAdmin())
                    <!-- Admin Assigned Branches -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Akses Cabang Admin</h2>
                                <p class="text-[11px] text-gray-500">Cabang yang memiliki izin operasional untuk admin ini</p>
                            </div>
                            <span class="text-xs font-bold text-gray-500">{{ $user->branches->count() }} Cabang</span>
                        </div>

                        <div class="divide-y divide-gray-100 text-xs">
                            @forelse ($user->branches as $b)
                                <div class="py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100">
                                            <x-cressco.icon-helper name="building" class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <a href="{{ route('owner.branches.show', $b) }}" class="font-bold text-gray-900 hover:text-terracotta-600 transition">
                                                {{ $b->name }}
                                            </a>
                                            <div class="text-[11px] text-gray-400">Kode: {{ $b->code ?? '-' }} • {{ $b->address ?: 'Alamat belum diatur' }}</div>
                                        </div>
                                    </div>
                                    <x-cressco.badge :variant="$b->status === 'active' ? 'success' : 'gray'" dot size="sm">
                                        {{ $b->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </div>
                            @empty
                                <div class="py-6 text-center text-gray-400 text-xs italic">
                                    Admin ini belum ditugaskan ke cabang manapun. Klik "Edit User" untuk menugaskan cabang.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @elseif ($user->isTutor())
                    <!-- Tutor Assigned Classes -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Penugasan Mengajar Tutor</h2>
                                <p class="text-[11px] text-gray-500">Kelas dan cabang aktif yang diampu oleh tutor ini</p>
                            </div>
                            <span class="text-xs font-bold text-gray-500">{{ $user->tutorAssignments->count() }} Penugasan</span>
                        </div>

                        <div class="divide-y divide-gray-100 text-xs">
                            @forelse ($user->tutorAssignments as $ta)
                                <div class="py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center border border-purple-100">
                                            <x-cressco.icon-helper name="academic" class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900">{{ $ta->classModel?->name ?? 'Kelas Bimbel' }}</div>
                                            <div class="text-[11px] text-gray-400">Cabang: {{ $ta->branch?->name ?? '-' }} • Mata Pelajaran: {{ $ta->classModel?->subject ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <x-cressco.badge :variant="$ta->status === 'active' ? 'success' : 'gray'" dot size="sm">
                                        {{ $ta->status === 'active' ? 'Aktif Mengajar' : 'Selesai' }}
                                    </x-cressco.badge>
                                </div>
                            @empty
                                <div class="py-6 text-center text-gray-400 text-xs italic">
                                    Tutor ini belum memiliki riwayat penugasan kelas.
                                </div>
                            @endforelse
                        </div>
                    </div>
                @else
                    <!-- Owner Overview -->
                    <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                        <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Hak Akses & Otoritas Owner</h2>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Sebagai Pemilik Bimbel (Owner), akun ini memiliki otoritas penuh ke seluruh cabang, kelas, data siswa, laporan keuangan, dan pengaturan dalam naungan <strong>{{ $tenant->name ?? 'Prime Academy' }}</strong>.
                        </p>
                    </div>
                @endif

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

                <form method="POST" action="{{ route('owner.users.update', $user) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text"
                               name="name"
                               x-model="editUser.name"
                               required
                               class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                        />
                    </div>

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

        <!-- ================= TOGGLE STATUS MODAL ================= -->
        <div x-show="toggleModalOpen"
             x-cloak
             x-transition
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="closeAll()"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                <h3 class="text-base font-bold text-gray-900">
                    {{ $user->status === 'active' ? 'Nonaktifkan Pengguna' : 'Aktifkan Pengguna' }}
                </h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Apakah Anda yakin ingin {{ $user->status === 'active' ? 'menonaktifkan' : 'mengaktifkan' }} akun <strong>{{ $user->name }}</strong>?
                </p>
                <form method="POST" action="{{ route('owner.users.toggle-status', $user) }}" class="flex items-center gap-3 pt-2">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="closeAll()" class="flex-1 px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl text-xs font-bold text-white {{ $user->status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $user->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
