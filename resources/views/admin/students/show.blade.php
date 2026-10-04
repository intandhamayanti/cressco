<x-admin-layout :tenant="$tenant" :title="$student->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('admin.students.index') }}" class="hover:text-terracotta-600 transition">Management Siswa</a>
        <span class="mx-1 text-gray-300">/</span>
        <span class="text-gray-900 font-bold">{{ $student->name }}</span>
    </x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             editModalOpen: false,
             toggleModalOpen: false,
             editStudent: {
                 id: '{{ $student->id }}',
                 branch_id: '{{ $student->branch_id }}',
                 name: '{{ $student->name }}',
                 date_of_birth: '{{ $student->date_of_birth ? $student->date_of_birth->format('Y-m-d') : '' }}',
                 gender: '{{ $student->gender ?: 'Laki-laki' }}',
                 phone: '{{ $student->phone ?: '' }}',
                 address: '{{ $student->address ?: '' }}',
                 parent_name: '{{ $student->parent_name ?: '' }}',
                 parent_phone: '{{ $student->parent_phone ?: '' }}',
                 notes: '{{ $student->notes ?: '' }}',
                 joined_at: '{{ $student->joined_at ? $student->joined_at->format('Y-m-d') : '' }}',
                 status: '{{ $student->status ?: 'active' }}'
             },
             closeAll() {
                 this.editModalOpen = false;
                 this.toggleModalOpen = false;
             }
         }">
        
        <!-- Header & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-2xl bg-terracotta-100 text-terracotta-800 flex items-center justify-center font-bold text-base shadow-2xs border border-terracotta-200/60">
                    {{ strtoupper(substr($student->name, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ $student->name }}</h1>
                        <x-cressco.badge :variant="$student->status === 'active' ? 'success' : 'gray'" dot>
                            {{ $student->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-cressco.badge>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Cabang: <span class="font-semibold text-gray-800">{{ $student->branch?->name ?? 'Pusat' }}</span> • Terdaftar sejak {{ $student->joined_at ? $student->joined_at->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('admin.students.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                    ← Kembali
                </a>

                <x-cressco.button variant="secondary" size="md" leadingIcon="edit" @click="editModalOpen = true">
                    Edit Siswa
                </x-cressco.button>

                <x-cressco.button variant="{{ $student->status === 'active' ? 'secondary' : 'primary' }}" size="md" @click="toggleModalOpen = true">
                    @if ($student->status === 'active')
                        <span class="text-amber-700">Nonaktifkan Siswa</span>
                    @else
                        <span>Aktifkan Siswa</span>
                    @endif
                </x-cressco.button>
            </div>
        </div>

        <!-- 2 Columns: Biodata & Academics/Payment -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Column 1: Student Profile & Contacts -->
            <div class="space-y-6">
                
                <!-- Biodata Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Biodata Siswa</h2>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nama Lengkap</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $student->name }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Jenis Kelamin</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $student->gender ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Tanggal Lahir</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $student->date_of_birth ? $student->date_of_birth->translatedFormat('d F Y') : '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">No Telepon / WhatsApp</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $student->phone ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Cabang Penempatan</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $student->branch?->name ?? '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Alamat Domisili</span>
                            <p class="font-medium text-gray-800 mt-0.5 leading-relaxed">{{ $student->address ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Catatan Khusus</span>
                            <p class="font-medium text-gray-800 mt-0.5 leading-relaxed">{{ $student->notes ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Parent / Guardian Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Data Orang Tua / Wali</h2>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nama Orang Tua / Wali</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $student->parent_name ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">No WhatsApp Orang Tua</span>
                            <div class="flex items-center gap-2 mt-0.5">
                                <p class="font-bold text-gray-900">{{ $student->parent_phone ?: '-' }}</p>
                                @if ($student->parent_phone)
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $student->parent_phone) }}"
                                       target="_blank"
                                       class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md">
                                        Chat WA
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Column 2: Enrollments, Attendance & Payment Records -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Class Enrollments -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Kelas & Enrollment</h2>
                            <p class="text-xs text-gray-500">Daftar kelas bimbingan belajar yang diikuti siswa.</p>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700">
                            {{ $student->enrollments->count() }} Pendaftaran
                        </span>
                    </div>

                    @if ($student->enrollments->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($student->enrollments as $enr)
                                <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div>
                                        <h3 class="text-xs font-bold text-gray-900">{{ $enr->classModel?->name ?? 'Kelas' }}</h3>
                                        <p class="text-[11px] text-gray-500 mt-0.5">
                                            Mata Pelajaran: {{ $enr->classModel?->subject ?? '-' }} • Level: {{ $enr->classModel?->level ?? '-' }}
                                        </p>
                                        <span class="text-[10px] text-gray-400">
                                            Periode: {{ $enr->started_at ? $enr->started_at->translatedFormat('d M Y') : '-' }} s/d {{ $enr->ended_at ? $enr->ended_at->translatedFormat('d M Y') : 'Sekarang' }}
                                        </span>
                                    </div>
                                    <div>
                                        <x-cressco.badge :variant="$enr->status === 'active' ? 'success' : ($enr->status === 'completed' ? 'info' : 'gray')" dot>
                                            {{ ucfirst($enr->status) }}
                                        </x-cressco.badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-400 italic">
                            Siswa ini belum terdaftar di kelas manapun.
                        </div>
                    @endif
                </div>

                <!-- Recent Attendance History -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Presensi Terkini</h2>
                            <p class="text-xs text-gray-500">10 sesi mengajar terakhir yang dihadiri siswa.</p>
                        </div>
                    </div>

                    @if ($student->attendances->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="pb-2">Tanggal / Sesi</th>
                                        <th class="pb-2">Kelas</th>
                                        <th class="pb-2">Status Kehadiran</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($student->attendances as $att)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="py-2.5 font-medium text-gray-800">
                                                {{ $att->recorded_at ? $att->recorded_at->translatedFormat('d M Y, H:i') : '-' }}
                                            </td>
                                            <td class="py-2.5 text-gray-600">
                                                {{ $att->teachingSession?->classModel?->name ?? 'Sesi Belajar' }}
                                            </td>
                                            <td class="py-2.5">
                                                <x-cressco.badge :variant="$att->status === 'present' ? 'success' : ($att->status === 'late' ? 'warning' : 'danger')" dot>
                                                    {{ ucfirst($att->status) }}
                                                </x-cressco.badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-400 italic">
                            Belum ada riwayat presensi yang tercatat untuk siswa ini.
                        </div>
                    @endif
                </div>

                <!-- Recent Payment History -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Pembayaran</h2>
                            <p class="text-xs text-gray-500">10 transaksi pembayaran terakhir siswa.</p>
                        </div>
                    </div>

                    @if ($student->payments->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="text-[11px] font-bold text-gray-400 uppercase tracking-wider border-b border-gray-100">
                                        <th class="pb-2">Invoice / Deskripsi</th>
                                        <th class="pb-2">Nominal</th>
                                        <th class="pb-2">Tanggal</th>
                                        <th class="pb-2">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($student->payments as $pay)
                                        <tr class="hover:bg-gray-50/50">
                                            <td class="py-2.5 font-medium text-gray-800">
                                                {{ $pay->invoice_number ?? 'Invoice' }}
                                            </td>
                                            <td class="py-2.5 font-bold text-gray-900">
                                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 text-gray-500 text-[11px]">
                                                {{ $pay->paid_at ? $pay->paid_at->translatedFormat('d M Y') : ($pay->created_at ? $pay->created_at->translatedFormat('d M Y') : '-') }}
                                            </td>
                                            <td class="py-2.5">
                                                <x-cressco.badge :variant="$pay->status === 'lunas' ? 'success' : ($pay->status === 'menunggu_verifikasi' ? 'warning' : 'danger')" dot>
                                                    {{ strtoupper($pay->status) }}
                                                </x-cressco.badge>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-6 text-center text-xs text-gray-400 italic">
                            Belum ada riwayat pembayaran yang tercatat untuk siswa ini.
                        </div>
                    @endif
                </div>

            </div>

        </div>

        <!-- Edit Modal (Scoped to Admin's accessible branches) -->
        <div x-show="editModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     @click="editModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-2xl p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="edit" class="w-5 h-5" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900">Edit Data Siswa</h3>
                                <p class="text-xs text-gray-500">Perbarui profil siswa dan informasi kontak wali.</p>
                            </div>
                        </div>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg">
                            <x-cressco.icon-helper name="close" class="w-5 h-5" />
                        </button>
                    </div>

                    <form method="POST" action="{{ route('admin.students.update', $student) }}" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Name -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap Siswa <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="editStudent.name" required
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Branch -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Cabang Penempatan <span class="text-red-500">*</span></label>
                                <select name="branch_id" x-model="editStudent.branch_id" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    @foreach ($accessibleBranches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Gender -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                                <select name="gender" x-model="editStudent.gender"
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>

                            <!-- DOB -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                                <input type="date" name="date_of_birth" x-model="editStudent.date_of_birth"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Student Phone -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WhatsApp Siswa</label>
                                <input type="text" name="phone" x-model="editStudent.phone"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Parent Name -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua / Wali</label>
                                <input type="text" name="parent_name" x-model="editStudent.parent_name"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Parent Phone -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor HP / WhatsApp Orang Tua</label>
                                <input type="text" name="parent_phone" x-model="editStudent.parent_phone"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Joined At -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                                <input type="date" name="joined_at" x-model="editStudent.joined_at"
                                       class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Status Belajar</label>
                                <select name="status" x-model="editStudent.status" required
                                        class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 bg-white focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Nonaktif</option>
                                </select>
                            </div>

                            <!-- Address -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Domisili</label>
                                <textarea name="address" x-model="editStudent.address" rows="2"
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>

                            <!-- Notes -->
                            <div class="sm:col-span-2">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Catatan</label>
                                <textarea name="notes" x-model="editStudent.notes" rows="2"
                                          class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500"></textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" @click="editModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <x-cressco.button type="submit" variant="primary" size="md">
                                Simpan Perubahan
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Toggle Status Confirmation Modal -->
        <div x-show="toggleModalOpen"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             role="dialog"
             aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="toggleModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity"
                     @click="toggleModalOpen = false"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="toggleModalOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-white rounded-2xl shadow-2xl transform transition-all">
                    
                    <div class="flex items-center gap-3 pb-3 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold"
                             :class="'{{ $student->status }}' === 'active' ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600'">
                            <x-cressco.icon-helper name="alert-circle" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900">
                                {{ $student->status === 'active' ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa' }}
                            </h3>
                            <p class="text-xs text-gray-500">Konfirmasi status belajar siswa.</p>
                        </div>
                    </div>

                    <div class="py-4 text-xs text-gray-600">
                        Apakah Anda yakin ingin <strong>{{ $student->status === 'active' ? 'menonaktifkan' : 'mengaktifkan' }}</strong> siswa <strong class="text-gray-900">{{ $student->name }}</strong>?
                    </div>

                    <form method="POST" action="{{ route('admin.students.toggle-status', $student) }}">
                        @csrf
                        @method('PATCH')

                        <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                            <button type="button" @click="toggleModalOpen = false"
                                    class="px-4 py-2 text-xs font-semibold text-gray-600 hover:text-gray-900 transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 text-xs font-bold rounded-xl shadow-2xs transition cursor-pointer {{ $student->status === 'active' ? 'bg-amber-600 hover:bg-amber-700 text-white' : 'bg-emerald-600 hover:bg-emerald-700 text-white' }}">
                                {{ $student->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
