<x-admin-layout :tenant="$tenant" title="Admin Dashboard">
    <x-slot:breadcrumbSub>Dashboard</x-slot:breadcrumbSub>

    <div class="space-y-6 max-w-7xl mx-auto">
        
        <!-- Header & Branch Filter Selector -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-gray-200/70">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Operasional Cabang</h1>
                <p class="text-xs text-gray-500 mt-1">
                    Ringkasan aktivitas akademik, jadwal sesi hari ini, dan penanganan siswa di {{ $tenant->name ?? 'Prime Academy' }}.
                </p>
            </div>

            <!-- Branch Scope Filter Selector -->
            @if ($accessibleBranches->count() > 1)
                <div class="flex items-center gap-2">
                    <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2" id="branchFilterForm">
                        <label for="branch_id_select" class="text-xs font-semibold text-gray-600 shrink-0">Cabang:</label>
                        <select name="branch_id"
                                id="branch_id_select"
                                onchange="document.getElementById('branchFilterForm').submit()"
                                class="text-xs font-medium text-gray-800 bg-white border border-gray-200 rounded-xl px-3 py-2 shadow-2xs focus:border-terracotta-500 focus:ring-1 focus:ring-terracotta-500 cursor-pointer">
                            <option value="all" {{ ! $selectedBranchId ? 'selected' : '' }}>Semua Cabang Saya ({{ $accessibleBranches->count() }})</option>
                            @foreach ($accessibleBranches as $branch)
                                <option value="{{ $branch->id }}" {{ $selectedBranchId === $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @elseif ($accessibleBranches->count() === 1)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-800">
                    <x-cressco.icon-helper name="building" class="w-4 h-4 text-terracotta-500" />
                    <span>Cabang: {{ $accessibleBranches->first()->name }}</span>
                </div>
            @endif
        </div>

        <!-- 5 Operational Stat Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <x-cressco.project-card
                title="Siswa Aktif"
                icon="academic"
                iconColor="text-emerald-500"
                value="{{ $activeStudentsCount }}"
                trend="{{ $totalStudentsCount }} Total"
                trendType="positive"
                subtitle="Siswa aktif di cabang akses Anda"
            />

            <x-cressco.project-card
                title="Kelas Aktif"
                icon="folder"
                iconColor="text-blue-500"
                value="{{ $activeClassesCount }}"
                trend="Berjalan"
                trendType="neutral"
                subtitle="Total rombel & kelas aktif"
            />

            <x-cressco.project-card
                title="Sesi Hari Ini"
                icon="clock"
                iconColor="text-amber-500"
                value="{{ $todaySessionsCount }}"
                trend="{{ $todayCompletedSessionsCount }} Selesai"
                trendType="neutral"
                subtitle="Jadwal mengajar terjadwal hari ini"
            />

            <x-cressco.project-card
                title="Kehadiran Siswa"
                icon="check-circle"
                iconColor="text-teal-500"
                value="{{ $attendanceRate }}%"
                trend="Bulan Ini"
                trendType="positive"
                subtitle="Rasio presensi siswa hadir"
            />

            <x-cressco.project-card
                title="Perlu Tindak Lanjut"
                icon="credit-card"
                iconColor="text-rose-500"
                value="{{ $pendingPaymentsCount }}"
                trend="Rp {{ number_format($pendingPaymentsAmount, 0, ',', '.') }}"
                trendType="negative"
                subtitle="Tagihan pending / terlambat"
            />
        </div>

        <!-- 2-Column Operational Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Left Column: Today's Schedule & Sessions + Recent Activities -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Today's Teaching Sessions Table -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-terracotta-50 text-terracotta-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="calendar" class="w-4 h-4" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-gray-900">Jadwal & Sesi Mengajar Hari Ini</h2>
                                <p class="text-[11px] text-gray-500">{{ now()->translatedFormat('l, d F Y') }}</p>
                            </div>
                        </div>

                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-gray-100 text-gray-700">
                            {{ $todaySessions->count() }} Sesi
                        </span>
                    </div>

                    @if ($todaySessions->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($todaySessions as $session)
                                <div class="p-4 hover:bg-gray-50/70 transition flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex flex-col items-center justify-center shrink-0 border border-gray-200/60 text-center">
                                            <span class="text-[10px] font-bold text-gray-700 leading-tight">
                                                {{ $session->start_time ? substr($session->start_time, 0, 5) : '-' }}
                                            </span>
                                            <span class="text-[9px] text-gray-400 leading-tight">WIB</span>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <h3 class="text-xs font-bold text-gray-900 truncate">
                                                    {{ $session->classModel?->name ?? 'Kelas' }}
                                                </h3>
                                                <span class="text-[10px] text-gray-400 font-medium">({{ $session->branch?->name ?? 'Cabang' }})</span>
                                            </div>
                                            <div class="flex items-center gap-2 text-[11px] text-gray-500 mt-0.5">
                                                <span>Tutor: <strong class="text-gray-700">{{ $session->actualTutor?->name ?? ($session->scheduledTutor?->name ?? 'Belum ditentukan') }}</strong></span>
                                                @if ($session->room)
                                                    <span>•</span>
                                                    <span>Ruang: {{ $session->room }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2 self-start sm:self-center">
                                        @if ($session->status === 'completed')
                                            <x-cressco.badge variant="success" dot>Selesai</x-cressco.badge>
                                        @elseif ($session->status === 'in_progress')
                                            <x-cressco.badge variant="warning" dot>Berlangsung</x-cressco.badge>
                                        @elseif ($session->status === 'cancelled')
                                            <x-cressco.badge variant="danger" dot>Dibatalkan</x-cressco.badge>
                                        @else
                                            <x-cressco.badge variant="info" dot>Terjadwal</x-cressco.badge>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center">
                            <div class="w-12 h-12 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-3">
                                <x-cressco.icon-helper name="calendar" class="w-6 h-6" />
                            </div>
                            <h3 class="text-xs font-bold text-gray-800">Tidak ada sesi mengajar hari ini</h3>
                            <p class="text-[11px] text-gray-500 mt-0.5">Semua jadwal kelas hari ini kosong atau telah diselesaikan.</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Operational Activities -->
                <div class="bg-white rounded-2xl border border-gray-200/80 shadow-xs overflow-hidden">
                    <div class="p-5 border-b border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                                <x-cressco.icon-helper name="activity" class="w-4 h-4" />
                            </div>
                            <h2 class="text-sm font-bold text-gray-900">Aktivitas Operasional Terbaru</h2>
                        </div>
                    </div>

                    @if ($recentActivities->isNotEmpty())
                        <div class="divide-y divide-gray-100">
                            @foreach ($recentActivities as $act)
                                <div class="p-4 flex items-center justify-between gap-3 hover:bg-gray-50/50 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-8 h-8 rounded-full bg-gray-100 text-gray-700 flex items-center justify-center shrink-0 font-bold text-xs border border-gray-200/60">
                                            {{ strtoupper(substr($act['user'], 0, 2)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-bold text-gray-900 truncate">
                                                {{ $act['user'] }}
                                                <span class="font-normal text-gray-500">— {{ $act['action'] }}</span>
                                            </p>
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $act['detail'] }}</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] text-gray-400 shrink-0">{{ $act['time'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-8 text-center text-xs text-gray-500">
                            Belum ada riwayat aktivitas operasional terbaru.
                        </div>
                    @endif
                </div>

            </div>

            <!-- Right Column: Action Items / Follow-up + Quick Actions -->
            <div class="space-y-6">
                
                <!-- Quick Actions Card -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-3">
                    <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2.5">Aksi Cepat</h2>
                    
                    <div class="space-y-2">
                        <a href="{{ route('admin.students.index') }}" class="w-full flex items-center justify-between p-3 rounded-xl border border-gray-200/80 hover:border-terracotta-300 hover:bg-terracotta-50/40 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-terracotta-100 text-terracotta-800 flex items-center justify-center shrink-0 font-bold">
                                    <x-cressco.icon-helper name="academic" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900 group-hover:text-terracotta-700">Daftarkan Siswa Baru</div>
                                    <div class="text-[10px] text-gray-500">Tambah data siswa & orang tua</div>
                                </div>
                            </div>
                            <x-cressco.icon-helper name="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-terracotta-600" />
                        </a>

                        <a href="{{ route('admin.students.index') }}" class="w-full flex items-center justify-between p-3 rounded-xl border border-gray-200/80 hover:border-blue-300 hover:bg-blue-50/40 transition group">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-800 flex items-center justify-center shrink-0 font-bold">
                                    <x-cressco.icon-helper name="users" class="w-4 h-4" />
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-gray-900 group-hover:text-blue-700">Kelola Data Siswa</div>
                                    <div class="text-[10px] text-gray-500">Daftar siswa, status & detail</div>
                                </div>
                            </div>
                            <x-cressco.icon-helper name="chevron-right" class="w-4 h-4 text-gray-400 group-hover:text-blue-600" />
                        </a>
                    </div>
                </div>

                <!-- Action Required: Tagihan Perlu Ditindaklanjuti -->
                <div class="bg-white rounded-2xl border border-gray-200/80 p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></div>
                            <h2 class="text-sm font-bold text-gray-900">Perlu Tindak Lanjut</h2>
                        </div>
                        <span class="text-[11px] font-bold text-rose-600">{{ $actionRequiredPayments->count() }} Tagihan</span>
                    </div>

                    @if ($actionRequiredPayments->isNotEmpty())
                        <div class="space-y-2.5">
                            @foreach ($actionRequiredPayments as $pay)
                                <div class="p-3 rounded-xl bg-gray-50/80 border border-gray-200/60 flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <div class="text-xs font-bold text-gray-900 truncate">{{ $pay->student?->name ?? 'Siswa' }}</div>
                                        <div class="text-[11px] text-gray-500">Rp {{ number_format($pay->amount, 0, ',', '.') }} • {{ $pay->branch?->name ?? 'Cabang' }}</div>
                                    </div>
                                    <x-cressco.badge :variant="$pay->status === 'menunggu_verifikasi' ? 'warning' : 'danger'">
                                        {{ $pay->status === 'menunggu_verifikasi' ? 'Verifikasi' : 'Terlambat' }}
                                    </x-cressco.badge>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 text-center text-xs text-gray-500">
                            Tidak ada pembayaran yang membutuhkan tindak lanjut saat ini.
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>
</x-admin-layout>
