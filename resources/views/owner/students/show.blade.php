<x-owner-layout :tenant="$tenant" :title="$student->name">
    <x-slot:breadcrumbSub>
        <a href="{{ route('owner.students.index') }}" class="hover:text-terracotta-600 transition">Management Siswa</a>
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
                <a href="{{ route('owner.students.index') }}" class="px-3.5 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
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
                            <span class="text-gray-400 block text-[11px]">Alamat Rumah</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $student->address ?: 'Belum diisi' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Parent & Notes Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Kontak Orang Tua & Catatan</h2>

                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nama Orang Tua / Wali</span>
                            <p class="font-bold text-gray-900 mt-0.5">{{ $student->parent_name ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">No Telepon Orang Tua</span>
                            <p class="font-medium text-gray-800 mt-0.5">{{ $student->parent_phone ?: '-' }}</p>
                        </div>

                        <div>
                            <span class="text-gray-400 block text-[11px]">Catatan / Target Akademik</span>
                            <p class="font-medium text-gray-700 mt-0.5 leading-relaxed bg-gray-50 p-2.5 rounded-xl border border-gray-100">
                                {{ $student->notes ?: 'Belum ada catatan khusus.' }}
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Column 2 & 3: Classes, Attendances, Payments -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Enrolled Classes -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Kelas Bimbingan Belajar</h2>
                            <p class="text-[11px] text-gray-500">Kelas yang sedang aktif diikuti oleh siswa</p>
                        </div>
                        <span class="text-xs font-bold text-gray-500">{{ $student->enrollments->count() }} Kelas</span>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse ($student->enrollments as $e)
                            <div class="py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 font-bold">
                                        <x-cressco.icon-helper name="academic" class="w-4 h-4" />
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $e->classModel?->name ?? 'Kelas Bimbel' }}</div>
                                        <div class="text-[11px] text-gray-400">
                                            Mata Pelajaran: {{ $e->classModel?->subject ?? '-' }} • Tingkat: {{ $e->classModel?->level ?? '-' }} • Mulai: {{ $e->started_at ? $e->started_at->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </div>
                                </div>
                                <x-cressco.badge :variant="$e->status === 'active' ? 'success' : 'gray'" dot size="sm">
                                    {{ $e->status === 'active' ? 'Aktif' : 'Selesai' }}
                                </x-cressco.badge>
                            </div>
                        @empty
                            <div class="py-6 text-center text-gray-400 text-xs italic">
                                Siswa belum terdaftar dalam kelas bimbingan manapun.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Attendance History -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Kehadiran Sesi Terakhir</h2>
                            <p class="text-[11px] text-gray-500">Catatan presensi pada sesi pembelajaran</p>
                        </div>
                        <span class="text-xs font-bold text-gray-500">{{ $student->attendances->count() }} Sesi</span>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse ($student->attendances as $att)
                            <div class="py-2.5 flex items-center justify-between">
                                <div class="flex items-center gap-2.5">
                                    <div class="text-gray-400 font-mono text-[11px]">{{ $att->recorded_at ? $att->recorded_at->translatedFormat('d M Y, H:i') : '-' }}</div>
                                    <div class="font-medium text-gray-800">{{ $att->teachingSession?->material ?: 'Sesi Bimbingan' }}</div>
                                </div>
                                <x-cressco.badge :variant="$att->status === 'hadir' ? 'success' : ($att->status === 'izin' ? 'warning' : 'error')" size="sm">
                                    {{ ucfirst($att->status) }}
                                </x-cressco.badge>
                            </div>
                        @empty
                            <div class="py-4 text-center text-gray-400 text-xs italic">
                                Belum ada catatan absensi tercatat.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Recent Payments History -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-6 shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <div>
                            <h2 class="text-sm font-bold text-gray-900">Riwayat Pembayaran Siswa</h2>
                            <p class="text-[11px] text-gray-500">Tagihan dan pembayaran SPP / kursus</p>
                        </div>
                        <span class="text-xs font-bold text-gray-500">{{ $student->payments->count() }} Tagihan</span>
                    </div>

                    <div class="divide-y divide-gray-100 text-xs">
                        @forelse ($student->payments as $p)
                            <div class="py-2.5 flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-gray-900">Rp {{ number_format($p->amount, 0, ',', '.') }}</div>
                                    <div class="text-[11px] text-gray-400">Periode: {{ $p->period }} • Jatuh Tempo: {{ $p->due_date ? $p->due_date->translatedFormat('d M Y') : '-' }}</div>
                                </div>
                                <x-cressco.badge :variant="$p->status === 'lunas' ? 'success' : ($p->status === 'terlambat' ? 'error' : 'warning')" size="sm">
                                    {{ ucfirst(str_replace('_', ' ', $p->status)) }}
                                </x-cressco.badge>
                            </div>
                        @empty
                            <div class="py-4 text-center text-gray-400 text-xs italic">
                                Belum ada catatan tagihan atau pembayaran.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

        </div>

        <!-- ================= EDIT STUDENT MODAL ================= -->
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

            <div class="relative bg-white rounded-3xl p-6 sm:p-8 max-w-xl w-full shadow-2xl border border-gray-100 space-y-5 z-10"
                 @click.stop>
                
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center border border-amber-200/60">
                            <x-cressco.icon-helper name="edit" class="w-5 h-5" />
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-gray-900">Edit Data Siswa</h3>
                            <p class="text-xs text-gray-500">Perbarui profil dan kontak siswa.</p>
                        </div>
                    </div>
                    <button type="button" @click="closeAll()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <x-cressco.icon-helper name="close" class="w-5 h-5" />
                    </button>
                </div>

                <form method="POST" action="{{ route('owner.students.update', $student) }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Siswa <span class="text-red-500">*</span></label>
                            <input type="text"
                                   name="name"
                                   x-model="editStudent.name"
                                   required
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Cabang Bimbel <span class="text-red-500">*</span></label>
                            <select name="branch_id"
                                    x-model="editStudent.branch_id"
                                    required
                                    class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 shadow-2xs">
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date"
                                   name="date_of_birth"
                                   x-model="editStudent.date_of_birth"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="gender" x-model="editStudent.gender" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 shadow-2xs">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No Telepon Siswa</label>
                            <input type="text"
                                   name="phone"
                                   x-model="editStudent.phone"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Rumah</label>
                            <input type="text"
                                   name="address"
                                   x-model="editStudent.address"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Orang Tua</label>
                            <input type="text"
                                   name="parent_name"
                                   x-model="editStudent.parent_name"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">No Telepon Orang Tua</label>
                            <input type="text"
                                   name="parent_phone"
                                   x-model="editStudent.parent_phone"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"
                            />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Tanggal Bergabung</label>
                            <input type="date"
                                   name="joined_at"
                                   x-model="editStudent.joined_at"
                                   class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"
                            />
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Status Keaktifan <span class="text-red-500">*</span></label>
                            <select name="status" x-model="editStudent.status" class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-800 shadow-2xs">
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes"
                                  rows="2"
                                  x-model="editStudent.notes"
                                  class="w-full px-3.5 py-2 text-xs bg-white border border-gray-200 rounded-xl text-gray-900 shadow-2xs"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="closeAll()" class="px-4 py-2 rounded-xl border border-gray-200 bg-white hover:bg-gray-50 text-xs font-bold text-gray-700 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-terracotta-500 hover:bg-terracotta-600 text-xs font-bold text-white transition shadow-sm">
                            Perbarui Siswa
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
                    {{ $student->status === 'active' ? 'Nonaktifkan Siswa' : 'Aktifkan Siswa' }}
                </h3>
                <p class="text-xs text-gray-500 leading-relaxed">
                    Apakah Anda yakin ingin {{ $student->status === 'active' ? 'menonaktifkan' : 'mengaktifkan' }} siswa <strong>{{ $student->name }}</strong>?
                </p>
                <form method="POST" action="{{ route('owner.students.toggle-status', $student) }}" class="flex items-center gap-3 pt-2">
                    @csrf
                    @method('PATCH')
                    <button type="button" @click="closeAll()" class="flex-1 px-4 py-2 rounded-xl border border-gray-200 bg-white text-xs font-bold text-gray-700">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-xl text-xs font-bold text-white {{ $student->status === 'active' ? 'bg-amber-600 hover:bg-amber-700' : 'bg-emerald-600 hover:bg-emerald-700' }}">
                        {{ $student->status === 'active' ? 'Ya, Nonaktifkan' : 'Ya, Aktifkan' }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-owner-layout>
