<x-admin-layout :tenant="$tenant" title="Profil & Pengaturan Akun">
    <x-slot:breadcrumbSub>Profil Saya</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-5xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Profil & Pengaturan Akun</h1>
                <p class="text-xs text-gray-500 mt-1">Kelola data profil personal, kata sandi, dan lihat cakupan hak akses cabang operasional Anda.</p>
            </div>
            <div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="px-4 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold transition flex items-center gap-1.5 shadow-2xs cursor-pointer">
                        <x-cressco.icon-helper name="log-out" class="w-4 h-4" />
                        <span>Keluar Akun (Logout)</span>
                    </button>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
                <x-cressco.icon-helper name="check-circle" class="w-4 h-4 text-emerald-600 shrink-0" />
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- Left Column: User Card & Branch Access Badge -->
            <div class="space-y-6">
                <!-- User Profile Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs text-center space-y-4">
                    <div class="w-20 h-20 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-2xl mx-auto border-2 border-terracotta-200 shadow-2xs">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900">{{ $user->name }}</h2>
                        <p class="text-xs text-gray-500">{{ $user->email }}</p>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200/60 mt-2">
                            Admin Operasional
                        </span>
                    </div>

                    <div class="pt-4 border-t border-gray-100 text-left text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-400">Bimbel (Tenant):</span>
                            <span class="font-bold text-gray-900">{{ $tenant->name ?? 'Prime Academy' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Status Akun:</span>
                            <span class="font-bold text-emerald-600 uppercase text-[10px]">{{ $user->status }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Bergabung Sejak:</span>
                            <span class="font-semibold text-gray-700">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Assigned Branches Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-3">
                    <div class="flex items-center gap-2 pb-2 border-b border-gray-100">
                        <x-cressco.icon-helper name="branch" class="w-4 h-4 text-gray-500" />
                        <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Akses Cabang Operasional</h3>
                    </div>
                    <p class="text-[11px] text-gray-500 leading-relaxed">
                        Anda memiliki otorisasi operasional untuk mengelola data pada cabang-cabang berikut:
                    </p>
                    <div class="space-y-1.5 pt-1">
                        @forelse ($branches as $branch)
                            <div class="p-2.5 rounded-xl bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                                <span class="font-semibold text-gray-800">{{ $branch->name }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">{{ $branch->code }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic">Belum ada cabang yang ditugaskan.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Right Column: Profile Edit & Change Password Forms -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- Edit Profile Form -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <x-cressco.icon-helper name="user" class="w-4 h-4 text-gray-500" />
                        <h3 class="text-sm font-bold text-gray-900">Perbarui Informasi Profil</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.profile.update') }}" class="space-y-4 text-xs">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $user->name) }}"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition @error('name') border-rose-500 @enderror"
                            />
                            @error('name')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Alamat Email <span class="text-rose-500">*</span></label>
                            <input
                                type="email"
                                name="email"
                                value="{{ old('email', $user->email) }}"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition @error('email') border-rose-500 @enderror"
                            />
                            @error('email')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end pt-2">
                            <x-cressco.button variant="primary" type="submit">
                                <span>Simpan Perubahan Profil</span>
                            </x-cressco.button>
                        </div>
                    </form>
                </div>

                <!-- Change Password Form -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center gap-2 pb-3 border-b border-gray-100">
                        <x-cressco.icon-helper name="lock" class="w-4 h-4 text-gray-500" />
                        <h3 class="text-sm font-bold text-gray-900">Ubah Kata Sandi (Password)</h3>
                    </div>

                    <form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4 text-xs">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Kata Sandi Saat Ini <span class="text-rose-500">*</span></label>
                            <input
                                type="password"
                                name="current_password"
                                required
                                class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition @error('current_password') border-rose-500 @enderror"
                            />
                            @error('current_password')
                                <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Kata Sandi Baru <span class="text-rose-500">*</span></label>
                                <input
                                    type="password"
                                    name="password"
                                    required
                                    placeholder="Minimal 8 karakter"
                                    class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition @error('password') border-rose-500 @enderror"
                                />
                                @error('password')
                                    <p class="text-rose-500 text-[11px] mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block font-semibold text-gray-700 mb-1">Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span></label>
                                <input
                                    type="password"
                                    name="password_confirmation"
                                    required
                                    placeholder="Ulangi kata sandi baru"
                                    class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition"
                                />
                            </div>
                        </div>

                        <div class="flex justify-end pt-2">
                            <x-cressco.button variant="primary" type="submit">
                                <span>Perbarui Kata Sandi</span>
                            </x-cressco.button>
                        </div>
                    </form>
                </div>

            </div>

        </div>

    </div>
</x-admin-layout>
