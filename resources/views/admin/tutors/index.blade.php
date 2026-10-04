<x-admin-layout :tenant="$tenant" title="Management Tutor & Pengajar">
    <x-slot:breadcrumbSub>Management Tutor</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             createModalOpen: false,
             editModalOpen: false,
             toggleModalOpen: false,
             editTutor: { id: '', name: '', email: '', phone: '', status: 'active' },
             toggleTutor: { id: '', name: '', status: '' },
             openCreate() {
                 this.toggleModalOpen = false;
                 this.editModalOpen = false;
                 this.createModalOpen = true;
             },
             openEdit(tutor) {
                 this.toggleModalOpen = false;
                 this.createModalOpen = false;
                 this.editTutor = {
                     id: tutor.id,
                     name: tutor.name,
                     email: tutor.email,
                     phone: tutor.phone || '',
                     status: tutor.status || 'active'
                 };
                 this.editModalOpen = true;
             },
             openToggle(tutor) {
                 this.createModalOpen = false;
                 this.editModalOpen = false;
                 this.toggleTutor = {
                     id: tutor.id,
                     name: tutor.name,
                     status: tutor.status
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
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Management Tutor & Pengajar</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Kelola data profil tutor, penugasan kelas belajar, dan jadwal operasional di cabang Anda.
                </p>
            </div>

            <div class="flex items-center gap-3">
                <x-cressco.button variant="primary" size="md" leadingIcon="plus" @click="openCreate()">
                    Tambah Tutor Baru
                </x-cressco.button>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="user-check" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Total Tutor</div>
                    <div class="text-xl font-bold text-gray-900 mt-0.5">{{ $totalTutorsCount }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="check-circle" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Tutor Aktif</div>
                    <div class="text-xl font-bold text-emerald-700 mt-0.5">{{ $activeTutorsCount }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="layers" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Penugasan Kelas</div>
                    <div class="text-xl font-bold text-purple-700 mt-0.5">{{ $totalClassAssignmentsCount }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <x-cressco.icon-helper name="calendar" class="w-5 h-5" />
                </div>
                <div>
                    <div class="text-xs text-gray-500 font-medium">Sesi Selesai</div>
                    <div class="text-xl font-bold text-amber-700 mt-0.5">{{ $totalCompletedSessionsCount }}</div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
            <form action="{{ route('admin.tutors.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cari Tutor</label>
                    <div class="relative">
                        <x-cressco.icon-helper name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama, email, atau nomor HP..."
                               class="w-full text-sm pl-9 pr-3 py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cabang</label>
                    <select name="branch_id" class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">Semua Cabang</option>
                        @foreach($accessibleBranches as $b)
                            <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Status</label>
                    <select name="status" class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                </div>

                <div class="sm:col-span-2 lg:col-span-4 flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
                    <a href="{{ route('admin.tutors.index') }}"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Reset Filter
                    </a>
                    <x-cressco.button variant="primary" size="sm" type="submit">
                        Terapkan Filter
                    </x-cressco.button>
                </div>
            </form>
        </div>

        <!-- Tutors Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-gray-200/75 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Daftar Tutor</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Menampilkan {{ $tutors->count() }} tutor terdaftar di cabang Anda</p>
                </div>
            </div>

            @if($tutors->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="user-check" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Tidak ada tutor ditemukan</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Belum ada data tutor yang sesuai dengan filter pencarian atau belum ada tutor yang ditugaskan di cabang Anda.
                    </p>
                    <div class="mt-4">
                        <x-cressco.button variant="primary" size="sm" leadingIcon="plus" @click="openCreate()">
                            Tambah Tutor Baru
                        </x-cressco.button>
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Tutor</th>
                                <th class="py-3.5 px-4">Kontak</th>
                                <th class="py-3.5 px-4">Kelas Diampu</th>
                                <th class="py-3.5 px-4">Jadwal Mingguan</th>
                                <th class="py-3.5 px-4">Sesi Selesai</th>
                                <th class="py-3.5 px-4">Status</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/70">
                            @foreach($tutors as $tutor)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full bg-primary-100 text-primary-700 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($tutor->name, 0, 2)) }}
                                            </div>
                                            <div>
                                                <a href="{{ route('admin.tutors.show', $tutor) }}" class="font-semibold text-gray-900 hover:text-primary-600 transition-colors">
                                                    {{ $tutor->name }}
                                                </a>
                                                <div class="text-[11px] text-gray-400">Bergabung: {{ $tutor->created_at ? $tutor->created_at->translatedFormat('d M Y') : '-' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-xs">
                                        <div class="text-gray-900 font-medium">{{ $tutor->email }}</div>
                                        <div class="text-gray-500 mt-0.5">{{ $tutor->phone ?? '-' }}</div>
                                    </td>

                                    <td class="py-3.5 px-4">
                                        <div class="flex flex-wrap gap-1 max-w-xs">
                                            @forelse($tutor->tutorAssignments->where('status', 'active') as $assignment)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200/60">
                                                    {{ $assignment->class?->name ?? 'Kelas' }}
                                                </span>
                                            @empty
                                                <span class="text-xs text-gray-400 italic">Belum ada kelas</span>
                                            @endforelse
                                        </div>
                                    </td>

                                    <td class="py-3.5 px-4 text-xs font-semibold text-gray-900">
                                        {{ $tutor->scheduledSchedules->where('status', 'active')->count() }} Sesi Rutin
                                    </td>

                                    <td class="py-3.5 px-4 text-xs font-semibold text-gray-900">
                                        {{ $tutor->actualTeachingSessions->count() }} Sesi
                                    </td>

                                    <td class="py-3.5 px-4">
                                        @if($tutor->status === 'active')
                                            <x-cressco.badge variant="success" size="sm" dot>Aktif</x-cressco.badge>
                                        @else
                                            <x-cressco.badge variant="neutral" size="sm" dot>Nonaktif</x-cressco.badge>
                                        @endif
                                    </td>

                                    <td class="py-3.5 px-4 text-right">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.tutors.show', $tutor) }}"
                                               class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                               title="Detail Tutor">
                                                <x-cressco.icon-helper name="eye" class="w-4 h-4" />
                                            </a>

                                            <button type="button"
                                                    @click="openEdit({
                                                        id: '{{ $tutor->id }}',
                                                        name: '{{ addslashes($tutor->name) }}',
                                                        email: '{{ addslashes($tutor->email) }}',
                                                        phone: '{{ addslashes($tutor->phone ?? '') }}',
                                                        status: '{{ $tutor->status }}'
                                                    })"
                                                    class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                                    title="Edit Profil">
                                                <x-cressco.icon-helper name="pencil" class="w-4 h-4" />
                                            </button>

                                            <button type="button"
                                                    @click="openToggle({
                                                        id: '{{ $tutor->id }}',
                                                        name: '{{ addslashes($tutor->name) }}',
                                                        status: '{{ $tutor->status }}'
                                                    })"
                                                    class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs"
                                                    title="{{ $tutor->status === 'active' ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                <x-cressco.icon-helper name="{{ $tutor->status === 'active' ? 'slash' : 'check' }}" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <!-- =================== MODALS =================== -->

        <!-- 1. CREATE TUTOR MODAL -->
        <div x-show="createModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="createModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Tambah / Daftarkan Tutor Baru</h3>
                        <button type="button" @click="createModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form action="{{ route('admin.tutors.store') }}" method="POST" class="mt-4 space-y-4">
                        @csrf

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap Tutor *</label>
                            <input type="text" name="name" required placeholder="Contoh: Budi Santoso, S.Pd."
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Email *</label>
                                <input type="email" name="email" required placeholder="tutor@email.com"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nomor WhatsApp / HP</label>
                                <input type="text" name="phone" placeholder="081234567890"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Password Awal</label>
                                <input type="password" name="password" placeholder="Default: Password123!"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status *</label>
                                <select name="status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="active" selected>Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Cabang (Opsional)</label>
                                <select name="branch_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">-- Pilih Cabang --</option>
                                    @foreach($accessibleBranches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Penugasan Kelas Awal (Opsional)</label>
                                <select name="class_id" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->branch?->name }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="createModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Daftarkan Tutor
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 2. EDIT TUTOR MODAL -->
        <div x-show="editModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Edit Profil Tutor</h3>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'{{ url('admin/tutors') }}/' + editTutor.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nama Lengkap Tutor *</label>
                            <input type="text" name="name" x-model="editTutor.name" required
                                   class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Email *</label>
                                <input type="email" name="email" x-model="editTutor.email" required
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Nomor HP</label>
                                <input type="text" name="phone" x-model="editTutor.phone"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Ubah Password</label>
                                <input type="password" name="password" placeholder="Biarkan kosong jika tidak diubah"
                                       class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status *</label>
                                <select name="status" x-model="editTutor.status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="editModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Perubahan
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- 3. TOGGLE TUTOR STATUS MODAL -->
        <div x-show="toggleModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="toggleModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mx-auto mb-4"
                         :class="toggleTutor.status === 'active' ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600'">
                        <x-cressco.icon-helper name="alert-triangle" class="w-6 h-6" x-show="toggleTutor.status === 'active'" />
                        <x-cressco.icon-helper name="check" class="w-6 h-6" x-show="toggleTutor.status !== 'active'" />
                    </div>

                    <h3 class="text-base font-bold text-gray-900 text-center"
                        x-text="toggleTutor.status === 'active' ? 'Nonaktifkan Tutor?' : 'Aktifkan Tutor?'">
                    </h3>

                    <p class="text-xs text-gray-500 text-center mt-2">
                        Tutor <strong x-text="toggleTutor.name"></strong> akan <span x-text="toggleTutor.status === 'active' ? 'dinonaktifkan dari jadwal pengajaran aktif' : 'diaktifkan kembali'"></span>.
                    </p>

                    <form :action="'{{ url('admin/tutors') }}/' + toggleTutor.id + '/toggle-status'" method="POST" class="mt-6 flex items-center justify-center gap-3">
                        @csrf
                        @method('PATCH')
                        <x-cressco.button variant="outline" size="md" type="button" @click="toggleModalOpen = false">
                            Batal
                        </x-cressco.button>
                        <x-cressco.button ::variant="toggleTutor.status === 'active' ? 'danger' : 'success'" size="md" type="submit">
                            <span x-text="toggleTutor.status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan'"></span>
                        </x-cressco.button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
