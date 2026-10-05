<x-owner-layout :tenant="$tenant" title="Monitoring Absensi & Kehadiran">
    <x-slot:breadcrumbSub>Absensi Siswa</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Monitoring & Rekap Presensi Bimbel</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Oversight kehadiran siswa dan pelaksanaan sesi belajar di seluruh cabang bimbel.
                </p>
            </div>
        </div>

        <!-- Metric Summary Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs text-gray-500 font-medium">Rata-rata Kehadiran</div>
                <div class="text-2xl font-bold text-gray-900 mt-1">
                    {{ $attendanceRate }}%
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Tingkat kehadiran siswa</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs text-emerald-600 font-medium">Hadir</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">
                    {{ $presentCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Siswa hadir di sesi</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs text-blue-600 font-medium">Izin</div>
                <div class="text-2xl font-bold text-blue-700 mt-1">
                    {{ $permissionCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Izin terkonfirmasi</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
                <div class="text-xs text-amber-600 font-medium">Sakit</div>
                <div class="text-2xl font-bold text-amber-700 mt-1">
                    {{ $sickCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Keterangan sakit</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs col-span-2 sm:col-span-1">
                <div class="text-xs text-rose-600 font-medium">Alpa</div>
                <div class="text-2xl font-bold text-rose-700 mt-1">
                    {{ $alpaCount }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">Tanpa keterangan</div>
            </div>
        </div>

        <!-- Filter & Search Section -->
        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs">
            <form action="{{ route('owner.attendances.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                <!-- Search Student -->
                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cari Nama Siswa</label>
                    <div class="relative">
                        <x-cressco.icon-helper name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama siswa..."
                               class="w-full text-xs pl-9 pr-3 py-2 rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                    </div>
                </div>

                <!-- Branch Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Cabang</label>
                    <select name="branch_id" class="w-full text-xs py-2 px-3 rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                        <option value="all">Semua Cabang</option>
                        @foreach($branches as $b)
                            <option value="{{ $b->id }}" {{ $branchId === $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Class Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Kelas</label>
                    <select name="class_id" class="w-full text-xs py-2 px-3 rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                        <option value="all">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c->id }}" {{ $classId === $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Status Kehadiran</label>
                    <select name="status" class="w-full text-xs py-2 px-3 rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
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
                           class="w-full text-xs py-2 px-3 rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                </div>

                <div class="lg:col-span-2">
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-1">Tanggal Selesai</label>
                    <input type="date" name="date_to" value="{{ $dateTo }}"
                           class="w-full text-xs py-2 px-3 rounded-xl border border-gray-200 focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 transition">
                </div>

                <!-- Action Buttons -->
                <div class="lg:col-span-4 flex items-end justify-end gap-2 pt-1">
                    <a href="{{ route('owner.attendances.index') }}"
                       class="px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs font-semibold text-gray-600 hover:bg-gray-50 transition shadow-2xs">
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
                    <span class="text-xs text-gray-500">Sesi pengajaran yang baru berlangsung</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($recentSessions as $session)
                        <div class="bg-white p-4 rounded-2xl border border-gray-200/80 shadow-xs hover:border-terracotta-300 transition-colors flex flex-col justify-between">
                            <div>
                                <div class="flex items-start justify-between gap-2">
                                    <h3 class="font-semibold text-gray-900 text-sm leading-snug">{{ $session->classModel?->name ?? 'Kelas' }}</h3>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 {{ $session->status === 'completed' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($session->status) }}
                                    </span>
                                </div>
                                <div class="text-xs text-gray-500 mt-2 flex items-center gap-1.5">
                                    <x-cressco.icon-helper name="calendar" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                    <span>{{ \Carbon\Carbon::parse($session->session_date)->translatedFormat('l, d M Y') }} • {{ substr($session->start_time, 0, 5) }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                    <x-cressco.icon-helper name="user-check" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                    <span class="truncate">Tutor: {{ $session->actualTutor?->name ?? $session->scheduledTutor?->name ?? '-' }}</span>
                                </div>
                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1.5">
                                    <x-cressco.icon-helper name="users" class="w-3.5 h-3.5 text-gray-400 shrink-0" />
                                    <span>{{ $session->student_attendances_count }} Presensi Tercatat</span>
                                </div>
                            </div>

                            <div class="pt-3 mt-3 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-[11px] text-gray-400 font-medium">{{ $session->branch?->name }}</span>
                                <a href="{{ route('owner.attendances.sessions.show', $session) }}"
                                   class="text-xs font-bold text-terracotta-600 hover:text-terracotta-700 inline-flex items-center gap-1">
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
        <div class="bg-white rounded-2xl border border-gray-200/80 overflow-hidden shadow-xs">
            <div class="p-4 border-b border-gray-200/75 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-gray-900">Catatan Presensi Siswa</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Menampilkan seluruh rekaman presensi siswa di bimbel</p>
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
                    <table class="w-full text-left text-xs text-gray-600">
                        <thead class="bg-gray-50/75 border-b border-gray-200 text-[11px] font-bold text-gray-700 uppercase tracking-wider">
                            <tr>
                                <th class="py-3.5 px-4">Siswa</th>
                                <th class="py-3.5 px-4">Kelas & Sesi</th>
                                <th class="py-3.5 px-4">Cabang</th>
                                <th class="py-3.5 px-4">Waktu Presensi</th>
                                <th class="py-3.5 px-4">Status Kehadiran</th>
                                <th class="py-3.5 px-4">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/70">
                            @foreach($attendances as $att)
                                <tr class="hover:bg-gray-50/50 transition-colors">
                                    <!-- Student Info -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-gray-900">{{ $att->student?->name ?? 'Siswa Dihapus' }}</div>
                                        <div class="text-[11px] text-gray-500">{{ $att->student?->phone ?? '-' }}</div>
                                    </td>

                                    <!-- Class & Session -->
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-gray-900">{{ $att->teachingSession?->classModel?->name ?? '-' }}</div>
                                        <div class="text-[11px] text-gray-500">
                                            {{ $att->teachingSession?->session_date ? \Carbon\Carbon::parse($att->teachingSession->session_date)->translatedFormat('d M Y') : '-' }}
                                        </div>
                                    </td>

                                    <!-- Branch -->
                                    <td class="py-3.5 px-4 font-medium text-gray-700">
                                        {{ $att->branch?->name ?? '-' }}
                                    </td>

                                    <!-- Recorded At -->
                                    <td class="py-3.5 px-4 text-gray-600">
                                        <div>{{ $att->recorded_at ? \Carbon\Carbon::parse($att->recorded_at)->translatedFormat('d M Y H:i') : '-' }}</div>
                                        <div class="text-[10px] text-gray-400">Oleh: {{ $att->recordedBy?->name ?? 'Tutor' }}</div>
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
                                    <td class="py-3.5 px-4 text-gray-600 max-w-xs truncate">
                                        {{ $att->note ?? '-' }}
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

    </div>
</x-owner-layout>
