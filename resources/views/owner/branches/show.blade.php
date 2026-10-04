<x-owner-layout :tenant="$tenant" :title="$branch->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('owner.branches.index') }}" class="hover:text-terracotta-600 transition">Management Cabang</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">{{ $branch->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             editModalOpen: false,
             toggleModalOpen: false,
             editBranch: {{ json_encode($branch) }}
         }">
        
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center border border-terracotta-200/60 font-bold text-base shadow-2xs">
                    <x-cressco.icon-helper name="building" class="w-6 h-6" />
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $branch->name }}</h1>
                        <x-cressco.badge :variant="$branch->status === 'active' ? 'success' : 'gray'" dot>
                            {{ $branch->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-cressco.badge>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Kode Cabang: <span class="font-mono font-semibold text-gray-700">{{ $branch->code ?? '-' }}</span> • Terdaftar sejak {{ $branch->created_at->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('owner.branches.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                    ← Kembali
                </a>

                <x-cressco.button variant="secondary" size="md" leadingIcon="edit" @click="editModalOpen = true">
                    Edit Cabang
                </x-cressco.button>

                <x-cressco.button variant="{{ $branch->status === 'active' ? 'secondary' : 'primary' }}" size="md" @click="toggleModalOpen = true">
                    @if ($branch->status === 'active')
                        <span class="text-amber-700">Nonaktifkan</span>
                    @else
                        <span>Aktifkan Cabang</span>
                    @endif
                </x-cressco.button>
            </div>
        </div>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs space-y-1">
                <span class="text-[11px] font-semibold text-gray-500">Siswa Terdaftar</span>
                <div class="text-2xl font-bold text-gray-900">{{ $branch->students_count }}</div>
                <div class="text-[10px] text-gray-400">Total siswa di cabang ini</div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs space-y-1">
                <span class="text-[11px] font-semibold text-gray-500">Kelas Belajar</span>
                <div class="text-2xl font-bold text-gray-900">{{ $branch->classes_count }}</div>
                <div class="text-[10px] text-gray-400">Kelas aktif beroperasi</div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs space-y-1">
                <span class="text-[11px] font-semibold text-gray-500">Admin Bertugas</span>
                <div class="text-2xl font-bold text-gray-900">{{ $branch->branch_users_count }}</div>
                <div class="text-[10px] text-gray-400">Staff pengelola cabang</div>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200/80 p-4 shadow-xs space-y-1">
                <span class="text-[11px] font-semibold text-gray-500">Penugasan Tutor</span>
                <div class="text-2xl font-bold text-gray-900">{{ $branch->tutor_assignments_count }}</div>
                <div class="text-[10px] text-gray-400">Tutor mengajar</div>
            </div>
        </div>

        <!-- 2 Columns: Branch Info & Admins Assigned -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Column 1: Branch Information Details -->
            <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Informasi Lokasi & Kontak</h2>

                <div class="space-y-3 text-xs">
                    <div>
                        <span class="text-gray-400 block text-[11px]">Alamat Lengkap</span>
                        <p class="font-medium text-gray-800 mt-0.5">{{ $branch->address ?: 'Belum diisi' }}</p>
                    </div>

                    <div>
                        <span class="text-gray-400 block text-[11px]">Nomor Telepon / WhatsApp</span>
                        <p class="font-medium text-gray-800 mt-0.5">{{ $branch->phone ?: '-' }}</p>
                    </div>

                    <div>
                        <span class="text-gray-400 block text-[11px]">Status Operasional</span>
                        <div class="mt-1">
                            <x-cressco.badge :variant="$branch->status === 'active' ? 'success' : 'gray'" dot>
                                {{ $branch->status === 'active' ? 'Aktif Beroperasi' : 'Nonaktif' }}
                            </x-cressco.badge>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2 & 3: Admins Assigned -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Admin Pengelola Cabang</h2>
                        <p class="text-[11px] text-gray-500">Staff admin yang memiliki hak akses operasional pada cabang ini</p>
                    </div>
                    <span class="text-xs font-bold text-gray-500">{{ $branch->users->count() }} Admin</span>
                </div>

                <div class="divide-y divide-gray-100 text-xs">
                    @forelse ($branch->users as $admin)
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-xs shrink-0">
                                    {{ strtoupper(substr($admin->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-gray-900 truncate">{{ $admin->name }}</div>
                                    <div class="text-[11px] text-gray-500 truncate">{{ $admin->email }}</div>
                                </div>
                            </div>
                            <x-cressco.badge variant="gray" size="sm">
                                Admin Cabang
                            </x-cressco.badge>
                        </div>
                    @empty
                        <div class="py-6 text-center text-gray-400 text-xs italic">
                            Belum ada admin yang ditugaskan secara spesifik ke cabang ini.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

        <!-- Active Classes Table -->
        <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
            <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Kelas di Cabang Ini</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Kelas bimbingan belajar yang aktif</p>
                </div>
                <span class="text-xs font-bold text-gray-500">{{ $branch->classes->count() }} Kelas</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs font-sans">
                    <thead class="bg-gray-50/80 border-b border-gray-200 text-gray-500 uppercase tracking-wider font-semibold">
                        <tr>
                            <th class="py-3 px-4 font-bold text-gray-600">Nama Kelas</th>
                            <th class="py-3 px-4 font-bold text-gray-600">Mata Pelajaran</th>
                            <th class="py-3 px-4 font-bold text-gray-600">Tingkat</th>
                            <th class="py-3 px-4 font-bold text-gray-600 text-center">Kapasitas</th>
                            <th class="py-3 px-4 font-bold text-gray-600 text-center">Siswa Terdaftar</th>
                            <th class="py-3 px-4 font-bold text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 text-gray-700">
                        @forelse ($branch->classes as $c)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="py-3 px-4 font-bold text-gray-900">{{ $c->name }}</td>
                                <td class="py-3 px-4 font-medium text-gray-700">{{ $c->subject }}</td>
                                <td class="py-3 px-4 text-gray-600">{{ $c->level }}</td>
                                <td class="py-3 px-4 text-center font-medium">{{ $c->capacity }} kursi</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                        {{ $c->enrollments_count ?? 0 }} siswa
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <x-cressco.badge :variant="$c->status === 'active' ? 'success' : 'gray'" dot>
                                        {{ $c->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </x-cressco.badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-gray-400 italic">
                                    Belum ada kelas yang dibuat untuk cabang ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= EDIT MODAL ================= -->
        <div x-show="editModalOpen"
             x-cloak
             x-transition
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="editModalOpen = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-lg w-full shadow-2xl border border-gray-100 space-y-5 z-10">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-base font-bold text-gray-900">Edit Data Cabang</h3>
                    <button type="button" @click="editModalOpen = false" class="p-1 text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.branches.update', $branch) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Nama Cabang <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $branch->name) }}" required class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kode Cabang</label>
                            <input type="text" name="code" value="{{ old('code', $branch->code) }}" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs uppercase" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" name="phone" value="{{ old('phone', $branch->phone) }}" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap / Kota</label>
                        <textarea name="address" rows="2" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs">{{ old('address', $branch->address) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 shadow-2xs">
                            <option value="active" {{ $branch->status === 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ $branch->status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700">Batal</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 text-xs font-bold text-white shadow-sm hover:bg-terracotta-600">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ================= TOGGLE STATUS MODAL ================= -->
        <div x-show="toggleModalOpen"
             x-cloak
             x-transition
             class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 flex items-center justify-center font-sans">
            <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-xs" @click="toggleModalOpen = false"></div>
            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full shadow-2xl border border-gray-100 space-y-5 z-10">
                <h3 class="text-base font-bold text-gray-900">
                    {{ $branch->status === 'active' ? 'Nonaktifkan Cabang' : 'Aktifkan Cabang' }}
                </h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Apakah Anda yakin ingin {{ $branch->status === 'active' ? 'menonaktifkan' : 'mengaktifkan' }} cabang <strong>{{ $branch->name }}</strong>?
                </p>
                <form method="POST" action="{{ route('owner.branches.toggle-status', $branch) }}" class="flex items-center gap-3 pt-2">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="toggleModalOpen = false" class="flex-1 px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl text-xs font-bold text-white {{ $branch->status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $branch->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
