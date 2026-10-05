<x-owner-layout :tenant="$tenant" title="Pengaturan Bimbel">
    <x-slot:breadcrumbSub>Pengaturan Bimbel</x-slot:breadcrumbSub>

    <div class="space-y-8 max-w-5xl mx-auto"
         x-data="{
             editProfileModalOpen: false,
             changePasswordModalOpen: false
         }">

        <!-- Top Header Section -->
        <div class="pb-4 border-b border-gray-200/70 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pengaturan Bimbel</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola profil lembaga bimbel, standar honor default pengajar, dan preferensi operasional di {{ $tenant->name }}.
                </p>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="{{ route('owner.profile') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-white border border-gray-200 hover:bg-gray-50 text-xs font-bold text-gray-700 transition shadow-2xs">
                    <x-cressco.icon-helper name="user" class="w-4 h-4 text-gray-500" />
                    <span>Profil Saya</span>
                </a>
            </div>
        </div>

        <!-- Section: Overview Organisasi -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-1">
                <span class="text-xs text-gray-500 font-medium">Total Cabang</span>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['branchCount'] ?? 1 }}</div>
                <div class="text-[11px] text-gray-400">Cabang operasional</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-1">
                <span class="text-xs text-gray-500 font-medium">Total Tutor</span>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['tutorCount'] ?? 0 }}</div>
                <div class="text-[11px] text-gray-400">Pengajar terdaftar</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-1">
                <span class="text-xs text-gray-500 font-medium">Total Siswa</span>
                <div class="text-2xl font-bold text-gray-900">{{ $stats['studentCount'] ?? 0 }}</div>
                <div class="text-[11px] text-gray-400">Siswa aktif</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs space-y-1">
                <span class="text-xs text-gray-500 font-medium">Hak Akses</span>
                <div class="text-base font-bold text-gray-900 mt-1">Owner Bimbel</div>
                <div class="text-[11px] text-gray-400">Executive Control</div>
            </div>
        </div>

        <!-- Section 1: Profil & Identitas Lembaga Bimbel -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-6">
            <div class="border-b border-gray-100 pb-4">
                <h2 class="text-base font-bold text-gray-900">Informasi Lembaga Bimbel</h2>
                <p class="text-xs text-gray-500 mt-0.5">Identitas resmi bimbel yang tampil pada portal dan slip pembayaran siswa.</p>
            </div>

            <form method="POST" action="{{ route('owner.settings.tenant') }}" class="space-y-4 text-xs">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Nama Bimbel / Lembaga <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $tenant->name) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Kode / Slug Tenant (Read-Only)</label>
                        <input type="text" value="{{ $tenant->slug }}" readonly disabled class="w-full h-10 px-3.5 rounded-xl border border-gray-200 bg-gray-50 text-xs text-gray-500 cursor-not-allowed font-mono" />
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Email Resmi Lembaga</label>
                        <input type="email" name="email" value="{{ old('email', $tenant->email) }}" placeholder="info@bimbel.com" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Nomor Telepon / WhatsApp Kantor</label>
                        <input type="text" name="phone" value="{{ old('phone', $tenant->phone) }}" placeholder="081234567890" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Alamat Kantor Pusat</label>
                    <input type="text" name="address" value="{{ old('address', $tenant->address) }}" placeholder="Alamat lengkap kantor pusat..." class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                </div>

                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Deskripsi Singkat / Visi Lembaga</label>
                    <textarea name="description" rows="3" placeholder="Deskripsi atau visi lembaga bimbel..." class="w-full p-3 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">{{ old('description', $tenant->description) }}</textarea>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-[11px] text-gray-400">Perubahan identitas berlaku untuk seluruh slip dan portal siswa.</p>
                    <x-cressco.button variant="primary" type="submit">
                        Simpan Informasi Bimbel
                    </x-cressco.button>
                </div>
            </form>
        </div>

        <!-- Section 2: Pengaturan Honor Default Bimbel -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-6">
            <div class="border-b border-gray-100 pb-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Pengaturan Honor Default Bimbel</h2>
                        <p class="text-xs text-gray-500 mt-0.5">
                            Standar acuan honor pengajar yang berlaku otomatis bagi seluruh tutor dengan pilihan "Default Bimbel".
                        </p>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Acuan Standar Aktif
                    </span>
                </div>
            </div>

            @php
                $curMethod = $defaultHonorScheme?->method ?? 'per_session';
                $curRate = (float) ($defaultHonorScheme?->rate ?: ($defaultHonorScheme?->fixed_amount ?: 150000));
                $curEffective = $defaultHonorScheme?->effective_from ? $defaultHonorScheme->effective_from->format('Y-m-d') : '2026-01-01';
            @endphp

            <form method="POST" action="{{ route('owner.settings.honor-default') }}" class="space-y-4 text-xs" x-data="{ honorMethod: '{{ old('method', $curMethod) }}' }">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Metode Honor Standar <span class="text-rose-500">*</span></label>
                        <select name="method" x-model="honorMethod" required class="w-full h-10 px-3 py-2 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition">
                            <option value="per_session">Per Sesi Mengajar</option>
                            <option value="per_student" disabled class="text-gray-400 bg-gray-100">Per Siswa (Coming Soon)</option>
                            <option value="fixed_monthly" disabled class="text-gray-400 bg-gray-100">Bulanan Tetap (Coming Soon)</option>
                            <option value="revenue_share" disabled class="text-gray-400 bg-gray-100">Bagi Hasil / Revenue Share (Coming Soon)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">
                            <span>Tarif Honor Per Sesi (Rp)</span>
                            <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="rate" value="{{ old('rate', $curRate) }}" required min="0" step="5000" placeholder="75000" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Tanggal Mulai Berlaku <span class="text-rose-500">*</span></label>
                        <input type="date" name="effective_from" value="{{ old('effective_from', $curEffective) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>
                </div>

                <div class="p-3.5 bg-gray-50 rounded-xl border border-gray-200/80 text-[11px] text-gray-600 flex items-start gap-2.5">
                    <x-cressco.icon-helper name="info" class="w-4 h-4 text-terracotta-500 shrink-0 mt-0.5" />
                    <p>
                        Pengaturan ini menjadi acuan kompensasi bagi seluruh tutor yang memilih opsi <strong>"Default Bimbel"</strong>. Jika ingin memberikan nominal berbeda pada pengajar tertentu, Anda dapat memilih opsi <strong>"Pengaturan Lain"</strong> langsung di halaman detail tutor terkait.
                    </p>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-[11px] text-gray-400">Perhitungan honor periode berjalan otomatis menggunakan acuan ini.</p>
                    <x-cressco.button variant="primary" type="submit">
                        Simpan Honor Default
                    </x-cressco.button>
                </div>
            </form>
        </div>

        <!-- Section 3: Informasi Pemilik Akun (Personal Information) -->
        <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-6">
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Personal Information</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Informasi akun pemilik dan data kontak otentikasi.</p>
                </div>
                <button type="button" @click="editProfileModalOpen = true" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-gray-200 bg-gray-50 hover:bg-gray-100 text-gray-700 text-xs font-semibold transition cursor-pointer">
                    <x-cressco.icon-helper name="pencil" class="w-3.5 h-3.5 text-gray-500" />
                    <span>Edit Profil</span>
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px]">Nama Lengkap Owner</span>
                    <p class="font-bold text-gray-900 mt-0.5 text-sm">{{ $user->name }}</p>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Email Kontak Owner</span>
                    <p class="font-bold text-gray-900 mt-0.5">{{ $user->email }}</p>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Lembaga Bimbel</span>
                    <p class="font-bold text-gray-900 mt-0.5">{{ $tenant->name }}</p>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Keamanan Kata Sandi</span>
                    <button type="button" @click="changePasswordModalOpen = true" class="mt-1 inline-flex items-center gap-1 text-xs font-bold text-terracotta-600 hover:text-terracotta-700 transition cursor-pointer">
                        <span>Ganti Password Akun</span>
                        <x-cressco.icon-helper name="arrow-right" class="w-3.5 h-3.5" />
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
                    <h3 class="text-base font-bold text-gray-900">Edit Profil Owner</h3>
                    <button type="button" @click="editProfileModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.settings.profile') }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Alamat Email Login <span class="text-rose-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="editProfileModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition cursor-pointer">
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
                    <h3 class="text-base font-bold text-gray-900">Ubah Kata Sandi</h3>
                    <button type="button" @click="changePasswordModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
                        <x-cressco.icon-helper name="close" class="w-4 h-4" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.settings.password') }}" class="space-y-4 text-xs">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Password Saat Ini <span class="text-rose-500">*</span></label>
                        <input type="password" name="current_password" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Password Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password" required minlength="8" class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Konfirmasi Password Baru <span class="text-rose-500">*</span></label>
                        <input type="password" name="password_confirmation" required class="w-full h-10 px-3.5 rounded-xl border border-gray-300 text-xs focus:ring-2 focus:ring-terracotta-500 focus:border-terracotta-500 transition" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="changePasswordModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-50 text-xs font-semibold transition cursor-pointer">
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
