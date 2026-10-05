<x-owner-layout :tenant="$tenant" title="Profil Saya">
    <x-slot:breadcrumbSub>Profil Saya</x-slot:breadcrumbSub>

    <div class="space-y-8 max-w-5xl mx-auto"
         x-data="{
             editProfileModalOpen: false,
             changePasswordModalOpen: false
         }">

        <!-- Top Header Section -->
        <div class="pb-4 border-b border-gray-200/70">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Profil Saya</h1>
            <p class="text-xs text-gray-500 mt-1">
                Kelola informasi akun pribadi, detail kontak, dan keamanan kata sandi Anda.
            </p>
        </div>

        <!-- Section: Personal Information -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-gray-900">Personal Information</h2>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                
                <!-- Left: Profile Photo / Avatar Card -->
                <div class="lg:col-span-5 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs flex flex-col items-center justify-center text-center relative overflow-hidden">
                    <div class="w-28 h-28 rounded-2xl bg-gradient-to-br from-terracotta-100 to-terracotta-200 text-terracotta-700 flex items-center justify-center font-bold text-3xl shadow-inner border border-terracotta-200 relative mb-4">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                        <div class="absolute bottom-1 right-1 w-4 h-4 rounded-full bg-emerald-500 border-2 border-white"></div>
                    </div>

                    <h3 class="text-base font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>

                    <div class="mt-3 flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200">
                            Owner / Founder
                        </span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                        </span>
                    </div>

                    <p class="text-[11px] text-gray-400 mt-4 leading-relaxed">
                        Akun pengelola utama untuk organisasi <span class="font-semibold text-gray-700">{{ $tenant->name }}</span>.
                    </p>
                </div>

                <!-- Middle/Right: Personal Information Card -->
                <div class="lg:col-span-7 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                            <h3 class="text-sm font-bold text-gray-900">Detail Informasi Akun</h3>
                            <button type="button" @click="editProfileModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold transition">
                                <x-cressco.icon-helper name="pencil" class="w-3.5 h-3.5 text-gray-500" />
                                <span>Edit Profil</span>
                            </button>
                        </div>

                        <div class="space-y-4 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[11px]">Nama Lengkap</span>
                                <p class="font-bold text-gray-900 mt-0.5 text-sm">{{ $user->name }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Alamat Email</span>
                                <p class="font-bold text-gray-900 mt-0.5">{{ $user->email }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Peran (Role)</span>
                                <p class="font-bold text-gray-900 mt-0.5">{{ ucfirst($user->role) }} Portal (Executive Control)</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Lembaga Bimbel</span>
                                <p class="font-medium text-gray-800 mt-0.5">{{ $tenant->name }} ({{ $stats['branchCount'] }} Cabang, {{ $stats['tutorCount'] }} Tutor, {{ $stats['studentCount'] }} Siswa)</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section: Keamanan Akun (Password) -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-gray-900">Keamanan Akun</h2>

            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs divide-y divide-gray-100">
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Kata Sandi (Password)</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Ganti kata sandi secara berkala untuk menjaga keamanan akun Anda.</p>
                    </div>
                    <button type="button" @click="changePasswordModalOpen = true" class="px-4 py-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold transition shrink-0">
                        Ubah Password
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal Edit Profil Owner -->
        <div x-show="editProfileModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div @click.outside="editProfileModalOpen = false"
                 class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Edit Profil Akun</h3>
                    <button type="button" @click="editProfileModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.profile.update') }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="editProfileModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            Batal
                        </button>
                        <x-cressco.button variant="primary" type="submit">
                            Simpan Perubahan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Change Password -->
        <div x-show="changePasswordModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div @click.outside="changePasswordModalOpen = false"
                 class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Ubah Kata Sandi (Password)</h3>
                    <button type="button" @click="changePasswordModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.profile.password') }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Password Saat Ini <span class="text-rose-500">*</span></label>
                        <input type="password" name="current_password" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Password Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        <span class="text-[10px] text-gray-400 mt-1 block">Minimal 8 karakter kombinasi huruf dan angka.</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="changePasswordModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            Batal
                        </button>
                        <x-cressco.button variant="primary" type="submit">
                            Ubah Password
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
