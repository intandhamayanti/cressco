<x-owner-layout :tenant="$tenant" title="Pengaturan & Profil Owner">
    <x-slot:breadcrumbSub>Setting</x-slot:breadcrumbSub>

    <div class="space-y-8 max-w-7xl mx-auto"
         x-data="{
             editProfileModalOpen: false,
             changePasswordModalOpen: false,
             editTenantModalOpen: false
         }">

        <!-- Top Header Section -->
        <div class="pb-4 border-b border-gray-200/70">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Setting</h1>
            <p class="text-xs text-gray-500 mt-1">
                Customize your profile, personal information, password, and general settings.
            </p>
        </div>

        <!-- Section: Personal Information -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-gray-900">Personal Information</h2>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 items-stretch">
                
                <!-- Left: Profile Photo / Avatar Card -->
                <div class="lg:col-span-4 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs flex flex-col items-center justify-center text-center relative overflow-hidden">
                    <div class="w-32 h-32 rounded-2xl bg-gradient-to-br from-terracotta-100 to-terracotta-200 text-terracotta-700 flex items-center justify-center font-bold text-4xl shadow-inner border border-terracotta-200 relative mb-4">
                        {{ strtoupper(substr($user->name, 0, 2)) }}
                        <div class="absolute bottom-1 right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-white"></div>
                    </div>

                    <h3 class="text-base font-bold text-gray-900">{{ $user->name }}</h3>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $user->email }}</p>

                    <div class="mt-3 flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-terracotta-50 text-terracotta-700 border border-terracotta-200">
                            Owner / Founder
                        </span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                        </span>
                    </div>

                    <p class="text-[11px] text-gray-400 mt-4 leading-relaxed">
                        Akun pengelola utama untuk organisasi <span class="font-semibold text-gray-700">{{ $tenant->name }}</span>.
                    </p>
                </div>

                <!-- Middle: Personal Information Card -->
                <div class="lg:col-span-4 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                            <h3 class="text-sm font-bold text-gray-900">Personal Information</h3>
                            <button type="button" @click="editProfileModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold transition">
                                <x-cressco.icon-helper name="pencil" class="w-3.5 h-3.5 text-gray-500" />
                                <span>Edit</span>
                            </button>
                        </div>

                        <div class="space-y-3.5 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[11px]">Full Name</span>
                                <p class="font-bold text-gray-900 mt-0.5">{{ $user->name }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Role & Akses</span>
                                <p class="font-bold text-gray-900 mt-0.5">{{ ucfirst($user->role) }} Portal</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Address</span>
                                <p class="font-medium text-gray-800 mt-0.5">{{ $tenant->address ?: 'Belum ditentukan' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Email Address</span>
                                <p class="font-bold text-gray-900 mt-0.5">{{ $user->email }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Phone Number</span>
                                <p class="font-medium text-gray-800 mt-0.5">{{ $tenant->phone ?: 'Belum ditentukan' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Job Information Card -->
                <div class="lg:col-span-4 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                            <h3 class="text-sm font-bold text-gray-900">Job Information</h3>
                            <button type="button" @click="editTenantModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold transition">
                                <x-cressco.icon-helper name="pencil" class="w-3.5 h-3.5 text-gray-500" />
                                <span>Edit</span>
                            </button>
                        </div>

                        <div class="space-y-3.5 text-xs">
                            <div>
                                <span class="text-gray-400 block text-[11px]">Role</span>
                                <p class="font-bold text-gray-900 mt-0.5">Owner & Founder</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Workspace / Bimbel</span>
                                <p class="font-bold text-gray-900 mt-0.5">{{ $tenant->name }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Start Date / Terdaftar</span>
                                <p class="font-medium text-gray-800 mt-0.5">{{ $tenant->created_at ? $tenant->created_at->format('d F Y') : '-' }}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Status</span>
                                <div class="mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Available
                                    </span>
                                </div>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[11px]">Employee Type / Scope</span>
                                <p class="font-medium text-gray-800 mt-0.5">Full Scope ({{ $stats['branchCount'] }} Cabang, {{ $stats['tutorCount'] }} Tutor, {{ $stats['studentCount'] }} Siswa)</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section: General Setting -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-gray-900">General Setting</h2>

            <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs divide-y divide-gray-100">
                
                <!-- Password Row -->
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Password</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Set a unique password to protect your account.</p>
                    </div>
                    <button type="button" @click="changePasswordModalOpen = true" class="px-4 py-2 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-bold transition shrink-0">
                        Change Password
                    </button>
                </div>

                <!-- Two-Factor Authentication (2FA) Row -->
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Two-Factor Authentication (2FA)</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Make your account extra secure. Along with your password, you'll need to enter a code.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-400">Nonaktif</span>
                    </div>
                </div>

                <!-- Notifications Row -->
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Notifications</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Manage your alerts, reminders, and daily report updates.</p>
                    </div>
                    <div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                            Aktif
                        </span>
                    </div>
                </div>

                <!-- Language Row -->
                <div class="p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h4 class="text-sm font-bold text-gray-900">Language</h4>
                        <p class="text-xs text-gray-500 mt-0.5">Set the default language for your workspace experience.</p>
                    </div>
                    <div>
                        <select class="h-9 px-3 rounded-xl border border-gray-300 text-xs font-medium focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">
                            <option value="id" selected>Bahasa Indonesia</option>
                            <option value="en">English</option>
                        </select>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section: Pengaturan Profil Bimbel / Organization -->
        <div class="space-y-4">
            <h2 class="text-base font-bold text-gray-900">Informasi & Profil Lembaga Bimbel</h2>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs">
                <form method="POST" action="{{ route('owner.settings.tenant') }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Nama Bimbel / Tenant <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Identifier Slug (Read-Only)</label>
                            <input type="text" value="{{ $tenant->slug }}" readonly disabled class="w-full h-10 px-3.5 rounded-xl border border-gray-200 bg-gray-50 text-xs text-gray-500 cursor-not-allowed font-mono" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Email Resmi Bimbel</label>
                            <input type="email" name="email" value="{{ old('email', $tenant->email) }}" placeholder="info@bimbel.com" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Nomor Telepon / WhatsApp Kantor</label>
                            <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" placeholder="081234567890" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Alamat Kantor Pusat</label>
                        <input type="text" name="address" value="{{ old('address', $tenant->address) }}" placeholder="Alamat lengkap kantor pusat bimbel..." class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Deskripsi Singkat Lembaga Bimbel</label>
                        <textarea name="description" rows="3" placeholder="Deskripsi atau visi misi lembaga bimbel..." class="w-full p-3 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500">{{ old('description', $tenant->description) }}</textarea>
                    </div>

                    <div class="flex items-center justify-end pt-3 border-t border-gray-100">
                        <x-cressco.button variant="primary" type="submit">
                            Simpan Perubahan Bimbel
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Profil Owner -->
        <div x-show="editProfileModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div @click.outside="editProfileModalOpen = false"
                 class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Edit Profil Owner</h3>
                    <button type="button" @click="editProfileModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.settings.profile') }}" class="space-y-4 text-xs">
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
                            Simpan Profil
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

                <form method="POST" action="{{ route('owner.settings.password') }}" class="space-y-4 text-xs">
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

        <!-- Modal Edit Bimbel Information (Quick Edit) -->
        <div x-show="editTenantModalOpen"
             x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/50 backdrop-blur-xs">
            <div @click.outside="editTenantModalOpen = false"
                 class="w-full max-w-lg rounded-2xl bg-white shadow-2xl border border-gray-100 p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Edit Informasi Bimbel</h3>
                    <button type="button" @click="editTenantModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.settings.tenant') }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Nama Bimbel <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Email Resmi</label>
                            <input type="email" name="email" value="{{ old('email', $tenant->email) }}" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Telepon Kantor</label>
                            <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Alamat Kantor</label>
                        <input type="text" name="address" value="{{ old('address', $tenant->address) }}" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="editTenantModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition">
                            Batal
                        </button>
                        <x-cressco.button variant="primary" type="submit">
                            Simpan
                        </x-cressco.button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
