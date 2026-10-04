<x-admin-layout :tenant="$tenant" title="Presensi & Kehadiran Siswa">
    <x-slot:breadcrumbSub>Presensi Siswa</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto"
         x-data="{
             editModalOpen: false,
             activeAttendance: { id: '', student_name: '', class_name: '', status: 'hadir', note: '' },
             openEdit(att) {
                 this.activeAttendance = {
                     id: att.id,
                     student_name: att.student_name,
                     class_name: att.class_name,
                     status: att.status,
                     note: att.note || ''
                 };
                 this.editModalOpen = true;
             }
         }">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Presensi & Kehadiran Siswa</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Pantau catatan kehadiran per kelas, verifikasi data presensi sesi belajar, dan lakukan koreksi status jika diperlukan.
                </p>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-gray-500 font-medium">Rasio Kehadiran</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $attendanceRate }}%
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Tingkat kehadiran siswa</div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-emerald-600 font-medium">Hadir</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">
                    {{ $presentCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Siswa hadir sesi</div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-blue-600 font-medium">Izin</div>
                <div class="text-2xl font-bold text-blue-700 mt-1">
                    {{ $permissionCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Izin terkonfirmasi</div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
                <div class="text-xs text-amber-600 font-medium">Sakit</div>
                <div class="text-2xl font-bold text-amber-700 mt-1">
                    {{ $sickCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Keterangan sakit</div>
            </div>

            <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs col-span-2 sm:col-span-1">
                <div class="text-xs text-red-600 font-medium">Alpa / Tanpa Keterangan</div>
                <div class="text-2xl font-bold text-red-700 mt-1">
                    {{ $alpaCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Perlu tindak lanjut</div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-xs">
            <form action="{{ route('admin.attendances.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search Student -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cari Nama Siswa</label>
                    <div class="relative">
                        <x-cressco.icon-helper name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama siswa..."
                               class="w-full text-sm pl-9 pr-3 py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                    </div>
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cabang</label>
                    <select name="branch_id" class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">Semua Cabang</option>
                        @foreach($accessibleBranches as $b)
                            <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Kelas</label>
                    <select name="class_id" class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <option value="all">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Status Kehadiran</label>
                    <select name="status" class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="hadir" {{ in_array($status, ['hadir', 'present']) ? 'selected' : '' }}>Hadir</option>
                        <option value="izin" {{ in_array($status, ['izin', 'excused']) ? 'selected' : '' }}>Izin</option>
                        <option value="sakit" {{ $status === 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="alpa" {{ in_array($status, ['alpa', 'absent']) ? 'selected' : '' }}>Alpa</option>
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Tanggal Mulai</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                           class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full text-sm py-1.5 rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500">
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-4 flex items-end justify-end gap-2 pt-1">
                    <a href="{{ route('admin.attendances.index') }}"
                       class="px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                        Reset Filter
                    </a>
                    <x-cressco.button variant="primary" size="sm" type="submit">
                        Terapkan Filter
                    </x-cressco.button>
                </div>
            </form>
        </div>

        <!-- Recent Teaching Sessions Section -->
        @if($recentSessions->isNotEmpty())
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-gray-900 uppercase tracking-wider">Sesi Mengajar Terbaru</h2>
                    <span class="text-xs text-gray-500">5 sesi pengajaran terakhir di cabang Anda</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($recentSessions as $session)
                        <div class="bg-white p-3.5 rounded-xl border border-gray-200 shadow-xs hover:border-primary-300 transition-colors flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-semibold text-gray-900 text-sm">{{ $session->classModel?->name ?? 'Kelas' }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                    <x-cressco.icon-helper name="calendar" class="w-3.5 h-3.5 text-gray-400" />
                                    <span>{{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                    <x-cressco.icon-helper name="user-check" class="w-3.5 h-3.5 text-gray-400" />
                                    <span>Tutor: {{ $session->actualTutor?->name ?? $session->scheduledTutor?->name ?? '-' }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                    <x-cressco.icon-helper name="users" class="w-3.5 h-3.5 text-gray-400" />
                                    <span>{{ $session->student_attendances_count }} Presensi Siswa</span>
                                </div>
                            </div>

                            <div class="pt-3 mt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400">{{ $session->branch?->name }}</span>
                                <a href="{{ route('admin.attendances.sessions.show', $session) }}"
                                   class="text-xs font-semibold text-primary-600 hover:text-primary-800 inline-flex items-center gap-1">
                                    <span>Detail Sesi</span>
                                    <x-cressco.icon-helper name="arrow-right" class="w-3.5 h-3.5" />
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Attendances Table -->
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-gray-200/75 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-semibold text-gray-900">Catatan Presensi Siswa</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Total {{ $attendances->total() }} catatan presensi ditemukan</p>
                </div>
            </div>

            @if($attendances->isEmpty())
                <div class="p-12 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                        <x-cressco.icon-helper name="calendar" class="w-6 h-6" />
                    </div>
                    <h3 class="text-sm font-semibold text-gray-900">Tidak ada data presensi</h3>
                    <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto">
                        Belum ada catatan presensi siswa pada rentang filter yang dipilih.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-600">
                        <thead class="bg-gray-50/75 border-b border-gray-200 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Siswa</th>
                                <th class="py-3.5 px-4">Kelas & Sesi</th>
                                <th class="py-3.5 px-4">Cabang</th>
                                <th class="py-3.5 px-4">Waktu Presensi</th>
                                <th class="py-3.5 px-4">Status Kehadiran</th>
                                <th class="py-3.5 px-4">Catatan</th>
                                <th class="py-3.5 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/70">
                            @foreach($attendances as $att)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <!-- Student Info -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900">{{ $att->student?->name ?? 'Siswa Dihapus' }}</div>
                                        <div class="text-xs text-gray-500 font-mono">{{ $att->student?->nis ?? '-' }}</div>
                                    </td>

                                    <!-- Class & Session -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-gray-900">{{ $att->teachingSession?->classModel?->name ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $att->teachingSession?->session_date ? \Carbon\Carbon::parse($att->teachingSession->session_date)->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </td>

                                    <!-- Branch -->
                                    <td class="py-3.5 px-4 text-xs font-medium text-gray-700">
                                        {{ $att->branch?->name ?? '-' }}
                                    </td>

                                    <!-- Recorded At -->
                                    <td class="py-3.5 px-4 text-xs text-gray-600">
                                        <div>{{ $att->recorded_at ? \Carbon\Carbon::parse($att->recorded_at)->translatedFormat('d M Y H:i') : '-' }}</div>
                                        <div class="text-[11px] text-gray-400">Oleh: {{ $att->recordedBy?->name ?? 'Sistem' }}</div>
                                    </td>

                                    <!-- Status Badge -->
                                    <td class="py-3.5 px-4">
                                        @if(in_array($att->status, ['hadir', 'present']))
                                            <x-cressco.badge variant="success" size="sm" dot>Hadir</x-cressco.badge>
                                        @elseif(in_array($att->status, ['izin', 'excused']))
                                            <x-cressco.badge variant="info" size="sm" dot>Izin</x-cressco.badge>
                                        @elseif($att->status === 'sakit')
                                            <x-cressco.badge variant="warning" size="sm" dot>Sakit</x-cressco.badge>
                                        @elseif(in_array($att->status, ['alpa', 'absent']))
                                            <x-cressco.badge variant="danger" size="sm" dot>Alpa</x-cressco.badge>
                                        @else
                                            <x-cressco.badge variant="neutral" size="sm" dot>{{ ucfirst($att->status) }}</x-cressco.badge>
                                        @endif
                                    </td>

                                    <!-- Note -->
                                    <td class="py-3.5 px-4 text-xs text-gray-600 max-w-xs truncate">
                                        {{ $att->note ?? '-' }}
                                    </td>

                                    <!-- Action -->
                                    <td class="py-3.5 px-4 text-right">
                                        <button type="button"
                                                @click="openEdit({
                                                    id: '{{ $att->id }}',
                                                    student_name: '{{ addslashes($att->student?->name ?? '') }}',
                                                    class_name: '{{ addslashes($att->teachingSession?->classModel?->name ?? '') }}',
                                                    status: '{{ in_array($att->status, ['present', 'hadir']) ? 'hadir' : (in_array($att->status, ['excused', 'izin']) ? 'izin' : (in_array($att->status, ['absent', 'alpa']) ? 'alpa' : $att->status)) }}',
                                                    note: '{{ addslashes($att->note ?? '') }}'
                                                })"
                                                class="p-1.5 rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors shadow-2xs inline-flex items-center justify-center"
                                                title="Koreksi Presensi">
                                            <x-cressco.icon-helper name="pencil" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($attendances->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $attendances->links() }}
                    </div>
                @endif
            @endif
        </div>

        <!-- EDIT / CORRECT ATTENDANCE MODAL -->
        <div x-show="editModalOpen"
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;"
             x-cloak>
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs transition-opacity" @click="editModalOpen = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-md rounded-2xl bg-white p-6 shadow-xl border border-gray-100" @click.stop>
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-lg font-bold text-gray-900">Koreksi Presensi Siswa</h3>
                        <button type="button" @click="editModalOpen = false" class="text-gray-400 hover:text-gray-600">
                            <x-cressco.icon-helper name="x" class="w-5 h-5" />
                        </button>
                    </div>

                    <form :action="'{{ url('admin/attendances') }}/' + activeAttendance.id" method="POST" class="mt-4 space-y-4">
                        @csrf
                        @method('PUT')

                        <div>
                            <div class="text-xs text-gray-500">Nama Siswa</div>
                            <div class="text-sm font-bold text-gray-900 mt-0.5" x-text="activeAttendance.student_name"></div>
                        </div>

                        <div>
                            <div class="text-xs text-gray-500">Kelas</div>
                            <div class="text-sm font-medium text-gray-800 mt-0.5" x-text="activeAttendance.class_name"></div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Status Presensi *</label>
                            <select name="status" x-model="activeAttendance.status" class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500" required>
                                <option value="hadir">Hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                                <option value="alpa">Alpa</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-gray-700 uppercase tracking-wider mb-1.5">Catatan / Alasan Perubahan</label>
                            <textarea name="note" x-model="activeAttendance.note" rows="3" placeholder="Masukkan alasan koreksi presensi atau keterangan siswa..."
                                      class="w-full text-sm rounded-lg border-gray-300 focus:border-primary-500 focus:ring-primary-500"></textarea>
                        </div>

                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <x-cressco.button variant="outline" size="md" type="button" @click="editModalOpen = false">
                                Batal
                            </x-cressco.button>
                            <x-cressco.button variant="primary" size="md" type="submit">
                                Simpan Koreksi
                            </x-cressco.button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-admin-layout>
